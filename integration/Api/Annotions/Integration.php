<?php
/**
 * Created by PhpStorm.
 * User: sunzhiping
 * Date: 17/1/3
 * Time: 下午4:35
 */

namespace Integration\Api\Annotions;

use Collective\Annotations\Routing\Annotations\Annotations\Annotation;
use Collective\Annotations\Routing\Annotations\EndpointCollection;
use Collective\Annotations\Routing\Annotations\MethodEndpoint;
use ReflectionClass;
use ReflectionMethod;
use Crypt;

/**
 * @Annotation
 */
class Integration extends Annotation
{
    /**
     * {@inheritdoc}
     */
    public function modify(MethodEndpoint $endpoint, ReflectionMethod $method)
    {
        $templete = "integration:%s,%s,%s,%s";
        $cache = "";
        if (isset($this->values['cache'])) {
            $cache = json_encode($this->values['cache']);
            $cache = Crypt::encrypt($cache);
        }
        $middleware = sprintf($templete, (isset($this->values['configure']) ? $this->values['configure'] :""),
            ( isset($this->values['sign']) && $this->values['sign'] == "false"?"false":"true"),
            ( isset($this->values['power']) && $this->values['power']?$this->values['power']:""),
            $cache
            );

        if ($endpoint->hasPaths()) {
            foreach ($endpoint->getPaths() as $path) {
                $path->middleware = array_merge([$middleware], $path->middleware);
            }
        } else {
            $endpoint->middleware = array_merge([$middleware], $endpoint->middleware);
        }
    }

}