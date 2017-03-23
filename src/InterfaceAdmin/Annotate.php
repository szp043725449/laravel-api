<?php

namespace Integration\InterfaceAdmin;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Integration\InterfaceAdmin\Annotions\Controller;

class Annotate
{
    private $reg = [
        'Post' => [
            'format' => '@%s("%s", as="%s")',
            'args' => ['@request', '@routeAddress', '@routeName'],
        ],
        'api' => [
            'format' => '@api {%s} %s %s',
            'args' => ['@api'],
        ],
        'apiName' => [
            'format' => '@apiName %s',
            'args' => ['@apiName'],
        ],
        'apiGroup' => [
            'format' => '@apiGroup %s',
            'args' => ['@apiGroup'],
        ],
        'apiParam' => [
            'format' => '@apiParam {%s} %s %s',
            'args' => '@apiParam',
        ],
        'apiSuccess' => [
            'format' => '@apiSuccess {%s} %s %s',
            'args' => '@apiSuccess',
        ],
    ];

    /**
     * @var string
     */
    private $api;

    /**
     * @var string
     */
    private $apiName;

    /**
     * @var string
     */
    private $apiHeader;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var array
     */
    private $apiParam = [];

    /**
     * @var array
     */
    private $apiSuccess = [];

    /**
     * @var string
     */
    private $request = "Post";

    /**
     * @var string
     */
    private $routeName = "";

    /**
     * @var string
     */
    private $routeAddress = "";

    /**
     * @var string
     */
    private $apiGroup;

    /**
     * @var string
     */
    private $controller;

    private $prefix;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->setPrefix();
    }

    /**
     * @return array
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param array $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getApiName()
    {
        return $this->apiName;
    }

    /**
     * @param string $apiName
     */
    public function setApiName($apiName)
    {
        $this->apiName = $apiName;
    }

    /**
     * @return string
     */
    public function getApiHeader()
    {
        return $this->apiHeader;
    }

    /**
     * @param string $apiHeader
     */
    public function setApiHeader($apiHeader)
    {
        $this->apiHeader = $apiHeader;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * @return array
     */
    public function getApiParam()
    {
        return $this->apiParam;
    }

    /**
     * @param array $apiParam
     */
    public function setApiParam($apiParam)
    {
        $this->apiParam = $apiParam;
    }

    /**
     * @return array
     */
    public function getApiSuccess()
    {
        return $this->apiSuccess;
    }

    /**
     * @param array $apiSuccess
     */
    public function setApiSuccess($apiSuccess)
    {
        $this->apiSuccess = $apiSuccess;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getRouteAddress()
    {
        return $this->routeAddress;
    }

    /**
     * @param string $routeAddress
     */
    public function setRouteAddress($routeAddress)
    {
        $this->routeAddress = $routeAddress;
    }

    /**
     * @return mixed
     */
    public function getApiGroup()
    {
        return $this->apiGroup;
    }

    /**
     * @param mixed $apiGroup
     */
    public function setApiGroup($apiGroup)
    {
        $this->apiGroup = $apiGroup;
    }

    /**
     * @return mixed
     */
    public function getRouteFullAddress()
    {
        return str_replace('//', '/', $this->getPrefix().$this->getRouteAddress());
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }


    /**
     * @return array
     */
    public function getAnnotateCode()
    {
        $annotate = [];
        $annotateFormat = "* %s";
        foreach ($this->reg as $key=>$reg)
        {
            if (is_string($reg['args'])) {
                $method = 'get'.ucfirst(str_replace('@', '', $reg['args']));
                $result = call_user_func([
                    $this,
                    $method
                ]);

                foreach ($result as $_k => $_v) {
                    $annotate[] = sprintf($annotateFormat, call_user_func_array('sprintf', array_merge([$reg['format']], $_v)));
                }
            } elseif(is_array($reg['args'])) {
                $args = [];
                foreach ($reg['args'] as $_k => $_v) {
                    $method = 'get'.ucfirst(str_replace('@', '', $_v));
                    $_result = call_user_func([
                        $this,
                        $method
                    ]);
                    if (is_array($_result)) {
                        $args = array_merge($args, $_result);
                    } else {
                        $args[] = $_result;
                    }
                }
                $annotate[] = sprintf($annotateFormat, call_user_func_array('sprintf', array_merge([$reg['format']], $args)));
            }
        }

        return $annotate;
    }

    private function setPrefix()
    {
        try {
            //注册Annotation解析文件
            $annotationPath = base_path('vendor/laravelextends/requestapi/src/InterfaceAdmin/Annotions/Controller.php');
            AnnotationRegistry::registerFile($annotationPath);

            $ref = new \ReflectionClass($this->controller);
            $reader = new SimpleAnnotationReader();
            $reader->addNamespace(__NAMESPACE__.'\\Annotions');
            $classAnnotations = $reader->getClassAnnotation($ref, Controller::class);
            $this->prefix = $classAnnotations->prefix;
        } catch (\ReflectionException $e) {
            throw $e;
        }
    }
}