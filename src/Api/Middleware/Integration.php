<?php

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
        if (!\Config::get('integration.start_cache')) {
            $cache = "";
        }
        if ($cache) {
            $cache = \Crypt::decrypt($cache);
            $cache = json_decode($cache, true);
            //dd($cache);exit;
            $cacheTime = $cache['caching_time'];
            //$cacheName = $this->getCacheName($cache['cache_name']);
            $cacheName = $this->iconfigure->callFunction([$this, 'getCacheName'], [$cache['cache_name'], $request], true);
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
        if (\Config::get('integration.start_auth')) {
            $authResult = $this->app->make('integration.auth')->auth($power);
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
        if (!\Config::get('app.debug') && $sign == "true" && \Config::get('integration.start_sign')) {
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

    /**
     * @param $name
     * @return mixed
     */
    public function getCacheName($name, Request $request)
    {
        $first = substr($name, 0, 1);
        if (":" == $first) {
            return eval('return '.substr($name, 1));
        } elseif ("!" == $first) {
            $closure = substr($name, 1);
            eval(' $closure = '.substr($name, 1).";");
            $name = $this->iconfigure->makeWithClosure($closure);
            return $name;
        } elseif ("default" == $name) {
            return md5($request->getRequestUri().json_encode($request->all()));
        } elseif ("@" == $first) {
            $cacheNameClass = \Config::get('integration.cacheNameClass');
            if (class_exists($cacheNameClass)) {
                $staticMethod = substr($name, 1);
                $staticMethod = rtrim($staticMethod, ';');
                $result =  $cacheNameClass::$staticMethod();
                if ($result instanceof Closure) {
                    return $this->iconfigure->makeWithClosure($result);
                } elseif (is_string($result)) {
                    return $result;
                }
            }

            throw new \RuntimeException("@cache error");
        }

        return $name;
    }
}