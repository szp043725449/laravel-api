<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/4
 * Time: 上午11:06
 */

namespace Integration\Api\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Integration\Api\Configure\Configure;
use Integration\Api\Services\Authentication;
use Integration\Api\Services\Message;
use Psy\Exception\TypeErrorException;
use Cache;

class Integration
{
    private $app;

    /**
     * @Configure
     */
    private $iconfigure;

    public function __construct(Application $app, Configure $iconfigure)
    {
        $this->app = $app;

        $this->iconfigure = $iconfigure;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @param $configure
     * @param String $sign
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next, $configure, $sign, $power = "", $cache = "")
    {
        $cacheName = "";
        $cacheTime = "";
        if ($cache) {
            $cache = \Crypt::decrypt($cache);
            $cache = json_decode($cache, true);
            $cacheTime = $cache['caching_time'];
            $cacheName = $cache['cache_name'];
            if ( $cacheTime ) {
                if ($response = Cache::get($cacheName)) {
                    return $response;
                }
            }

        }

        $this->iconfigure->setName($configure);
        /**
         * 权限验证
         */
        if (!\Config::get('app.debug')) {
            $authResult = $this->app->make('integration.auth')->auth();
            if (!($authResult instanceof Message) ) {
                throw new TypeErrorException(gettype($authResult).' is not '.Authentication::class);
            }
            if (!$authResult->isSuccess()) {
                return $this->iconfigure->getSendResponseWithMessage($authResult);
            }
        }

        /**
         * 签名验证
         */
        if (!\Config::get('app.debug') && $sign == "true") {
            $signatureOperation = $this->app->make('integration.signatureOperation');

            $signMessage = $this->app->make('integration.signmessage');

            $signResult = $signatureOperation::signature($signMessage);

            if (!($signResult instanceof Message) ) {
                throw new TypeErrorException(gettype($signResult).' is not Integration\Api\Services\Message');
            }
            if (!$signResult->isSuccess()) {
                return $this->iconfigure->getSendResponseWithMessage($signResult);
            }
        }


        /**
         * 参数验证
         */
        $message = $this->iconfigure->validate($request->all());
        if (!$message->isSuccess()) {
            return $this->iconfigure->send();
        }

        //dd($configureClass->getDisposed());
        $response = $next($request);

        if ( $cacheTime ) {
            Cache::put($cacheName, $response, $cacheTime);
        }
        // Perform action

        return $response;
    }
}