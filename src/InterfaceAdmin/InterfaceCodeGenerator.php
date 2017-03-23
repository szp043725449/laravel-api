<?php

namespace Integration\InterfaceAdmin;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Integration\InterfaceAdmin\Annotions\Controller;

class InterfaceCodeGenerator
{
    /**
     * @var int
     */
    private $numSpaces = 4;

    /**
     * @var array
     */
    private $extendsAnnotate = [];

    /**
     * @var string
     */
    private $power = "";

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var Annotate
     */
    private $annotate;

    /**
     * @var
     */
    private $actionName;

    /**
     * @var string
     */
    private $middleware;

    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private $disk;

    /**
     * @var string
     */
    private $configure;

    /**
     * @var array
     */
    private $updateParams = [];

    /**
     * @var bool
     */
    private $isDeleteParam = false;

    /**
     * InterfaceCodeGenerator constructor.
     * @param Annotate $annotate
     * @param $actionName
     */
    public function __construct(Annotate $annotate = null, $actionName = "", $middleware = '', $configure = '')
    {
        $this->annotate = $annotate;
        $this->actionName = $actionName;
        $this->middleware = $middleware;
        $this->configure = $configure;

        $this->disk = \Storage::disk('controller');
    }

    /**
     * @param boolean $isDeleteParam
     */
    public function setIsDeleteParam($isDeleteParam)
    {
        $this->isDeleteParam = $isDeleteParam;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->annotate->getRouteName();
    }

    /**
     * @return string
     */
    public function getRouteAddress()
    {
        return $this->annotate->getRouteAddress();
    }

    /**
     * @return string
     */
    public function getConfigure()
    {
        return $this->configure;
    }

    /**
     * @return string
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @param string $middleware
     */
    public function setMiddleware($middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * @param string $configure
     */
    public function setConfigure($configure)
    {
        $this->addAnnotate($configure);

        $this->configure = $configure;
    }

    /**
     * @return string
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * @param string $power
     */
    public function setPower($power)
    {
        $this->power = $power;
    }

    /**
     * @return array
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param array $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     *
     * @param $numSpaces
     */
    public function setNumSpaces($numSpaces)
    {
        $this->numSpaces = $numSpaces;

    }

    /**
     * @return int
     */
    public function getNumSpaces()
    {
        return $this->numSpaces;
    }

    /**
     * @return Annotate
     */
    public function getAnnotate()
    {
        return $this->annotate;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->annotate->getRequest();
    }

    public function getController()
    {
        return $this->getAnnotate()->getController();
    }

    /**
     * @param $annotate
     */
    public function addAnnotate($annotate)
    {
        $this->extendsAnnotate[] = $annotate;
    }

    public function generator()
    {
        $controller = $this->getController();
        $controller = \App::make($controller);
        $classFullName = get_class($controller);
        $controllerConfigure = $this->getControllers($classFullName);
        $path = $controllerConfigure['className'] . '.php';
        $contents = $this->disk->get($path);
        if (!method_exists($controller, $this->actionName)) {
            # 如果控制器里不存在该方法
            $code = $this->getAnnotateCode() . $this->getActionCode();
            preg_match('/^(<\?php)([\s\S]*)}/', $contents, $data);
            $contents = "<?php" . $data[2] . PHP_EOL . $code . PHP_EOL . "}";
        } else {
            $reflection = new \ReflectionClass($controller);
            $docComment = $reflection->getMethod($this->actionName)->getDocComment();
            /*
            $docCommentArray = explode(PHP_EOL, $docComment);
            $docCommentArray = array_map(function ($value) {
                return trim($value, ' ');
            }, $docCommentArray);
            array_shift($docCommentArray);
            array_pop($docCommentArray);
            $docComment = $this->getAnnotateCode($docCommentArray);
            */
            $replace = $this->getAnnotateCode();
            $replace = ltrim($replace);
            $replace = rtrim($replace);
            $contents = urldecode(str_replace(urlencode($docComment), urlencode($replace), urlencode($contents)));
            # 如果控制器存在该方法
        }
        $this->initConfigure();
        $this->disk->put($path, $contents);
        /**
         * @var \App\Console\Kernel $kernel
         */
        $kernel = \App::make(\Illuminate\Contracts\Console\Kernel::class);

        $kernel->call('integration:annotaion:create');
    }

    /**
     * @return mixed
     */
    public static function getAllControllers()
    {
        return (new static())->getControllers();
    }

    /**
     * @param $controller
     */
    public static function getMethod($controller)
    {
        $reflection = new \ReflectionClass($controller);
        $methods = [];
        foreach ($reflection->getMethods() as $method) {
            $methods[] = $method->getName();
        }

        return $methods;
    }

    /**
     * @param $class
     * @param $method
     * @return null|static
     * @throws \ReflectionException
     */
    public static function getAnnotateGenerator($class, $method)
    {

        try {
            $annotate = new Annotate($class);
            //注册Annotation解析文件
            $ref = new \ReflectionClass($class);
            $methodClass = $ref->getMethod($method);
            $pregs = [
                'request' => '/\@(Post|Get)\(\"([_\w\/]*)\"\s*,\s*as=\"([_\w]*)\"\)/',
                'api' => '/\@(api)\s*\{(get|post)\}\s*([\w\/]*)\s*(.*)/',
                'apiName' => '/\@(apiName)\s*(.*)/',
                'apiGroup' => '/\@(apiGroup)\s*(.*)/',
                'middleware' => '/\@(middleware)\(\"(.*)"\)/',
                'apiParam' => '/\@(apiParam)\s*\{([\w\[\]]*)\}\s*(\w*)\s*(.*)/',
                'apiSuccess' => '/\@(apiSuccess)\s*\{([\w\[\]]*)\}\s*(\w*)\s*(.*)/',
                'Integration' => '/\@(Integration)\((.*)\)/',

            ];
            $docComment = $methodClass->getDocComment();
            $static = new static($annotate, $method);
            foreach ($pregs as $key => $preg) {
                if (preg_match($preg, $docComment, $data)) {
                    if ($key == "request") {
                        $annotate->setRouteName($data[3]);
                        $annotate->setRouteAddress($data[2]);
                        $annotate->setRequest($data[1]);
                    } elseif ($key == 'api') {
                        $annotate->setApi([lcfirst($data[2]), $annotate->getRouteFullAddress(), $data[4]]);

                    } elseif ($key == "apiName") {
                        $annotate->setApiName($data[2]);
                    } elseif ($key == "apiGroup") {
                        $annotate->setApiGroup($data[2]);
                    } elseif ($key == "apiParam") {
                        $apiParams = [];
                        if (preg_match_all($preg, $docComment, $data)) {
                            foreach ($data[1] as $_k=>$_data)
                            {
                                $apiParams[] = [
                                    $data[2][$_k],
                                    $data[3][$_k],
                                    $data[4][$_k]
                                ];
                            }
                        }
                        $annotate->setApiParam($apiParams);
                    } elseif ($key == "apiSuccess") {
                        $apiGroup = [];
                        if (preg_match_all($preg, $docComment, $data)) {
                            foreach ($data[1] as $_k=>$_data)
                            {
                                $apiParams[] = [
                                    $data[2][$_k],
                                    $data[3][$_k],
                                    $data[4][$_k]
                                ];
                            }
                        }
                        $annotate->setApiSuccess([
                            $data[2],
                            $data[3],
                            $data[4]
                        ]);
                    } elseif ($key == "Integration") {
                        $static->setConfigure($data[2]);
                    } elseif ($key == "middleware") {
                        $static->setMiddleware($data[2]);
                    }


                }
            }

            return $static;
        } catch (\ReflectionException $e) {
            throw $e;
        }

        return null;
    }

    public function getControllers($classFullName = "")
    {
        static $controllers = [];
        if ($controllers) {
            if ($classFullName) {
                return array_get($controllers, $classFullName);
            }

            return $controllers;
        }
        $files = $this->disk->files(null, true);
        $controllerPath = $this->getControllerPath();
        foreach ($files as $file)
        {
            if (strstr($file, '.php')) {
                $filePath = $controllerPath . $file;
                $controller = $this->getClassNameAndNameSpaceByFileName($filePath);
                $controllers[key($controller)] = [
                    'className' => current($controller),
                    'path' => $filePath
                ];
            }
        }
        if ($classFullName) {
            return array_get($controllers, $classFullName);
        }

        return $controllers;
    }

    public function setUpdateParam($params)
    {
        $this->updateParams = $params;
    }

    /**
     * @param string $annotateCode
     * @return string
     */
    protected function getAnnotateCode($annotateCode = '')
    {
        $_annotateCode = "";
        if (!$annotateCode) {
            $annotateCode = $this->getAnnotate()->getAnnotateCode();
            if ($this->middleware) {
                $annotateCode[] = sprintf("* %s", '@middleware("' . $this->middleware . '")');
            }
            foreach ($this->extendsAnnotate as $_annotate) {
                $annotateCode[] = sprintf("* %s", $_annotate);
            }
        }
        foreach ($annotateCode as $code) {
            $_annotateCode .= '     ' . $code . PHP_EOL;
        }

        return '    /**' . PHP_EOL . $_annotateCode . '     */' . PHP_EOL;
    }

    protected function initConfigure()
    {
        if ($this->configure) {
            //@Integration(configure="user.login", power="admin", cache={"caching_time":0.0, "cache_name"="@getDefaultCacheName"})
            if (preg_match('/configure=\"([\w\.]*)\"/', $this->configure, $data)) {
                $configure = $data[1];
                $iconfigure = \app::make('integration.configure');
                $apiParam = $this->getAnnotate()->getApiParam();
                $updateParamters = "";
                $path = $iconfigure->getDisposedFilepath($configure);
                if (file_exists($path)) {
                    $configContents = file_get_contents($path);
                    $configArray = include_once $path;
                    $deleteKeys = [];
                    $arrayKeys = array_keys($configArray['requestParamters']);
                    foreach ($apiParam as $param)
                    {
                        if (!isset($configArray['requestParamters'][$param[1]])) {
                            $updateParamters .= "        /** start ".$param[1]." */". PHP_EOL;
                            $updateParamters .= "        \"" . $param[1] . "\" => [//" . $param[2] . PHP_EOL;
                            $updateParamters .= "            \"validate\" => [" . PHP_EOL;
                            $updateParamters .= "                 \"rules\" => \"\"," . PHP_EOL;
                            $updateParamters .= "                 \"message\" => []," . PHP_EOL;
                            $updateParamters .= "                 \"validate_function\" => function(){" . PHP_EOL;
                            $updateParamters .= "                 " ."    return new SuccessMessage();". PHP_EOL;
                            $updateParamters .= "                 }," . PHP_EOL;
                            $updateParamters .= "            ]," . PHP_EOL;
                            $updateParamters .= "            \"attached_value\" => [" . PHP_EOL;
                            $updateParamters .= "                \"realParamterName\" => \"".$param[1]."\"," . PHP_EOL;
                            $updateParamters .= "                \"value\" => function(Request \$request, Configure \$iconfigure){" . PHP_EOL;
                            $updateParamters .= "                 " ."    return \$request->get('".$param[1]."');". PHP_EOL;
                            $updateParamters .= "                }," . PHP_EOL;
                            $updateParamters .= "            ]," . PHP_EOL;
                            $updateParamters .= "        ]," . PHP_EOL;
                            $updateParamters .= "        /** end ".$param[1]." */". PHP_EOL;
                        }
                    }
                    foreach ($arrayKeys as $arrayKey) {
                        $keyexist = false;
                        array_walk($apiParam, function($value,$key) use($arrayKey, &$keyexist){
                            if ($value[1] == $arrayKey) {
                                $keyexist = true;
                            }
                        });
                        if (!$keyexist) {
                            $deleteKeys[] = $arrayKey;
                        }
                    }
                    $lastName = end($arrayKeys);
                    if ($lastName && $updateParamters) {
                        $addPreg = '/\/\*\*\s{1}end\s{1}'.$lastName.'\s{1}\*\/\s{1}/';
                        $configContents = preg_replace($addPreg, "/** end ".$lastName." */". PHP_EOL.$updateParamters, $configContents);
                    }
                    if ($this->isDeleteParam) {
                        foreach ($deleteKeys as $deleteKey) {
                            $delPreg = "/\/\*\*[\s]{1}start[\s]{1}" . $deleteKey . "\s{1}\*\/[\s]{1}([\s\S]*)\/\*\*[\s]{1}end[\s]{1}" . $deleteKey . "\s{1}\*\/\s{1}/";
                            $configContents = preg_replace($delPreg, PHP_EOL, $configContents);
                        }
                    }
                    file_put_contents($path, $configContents);
                    return;
                }
                $this->mkDirs(dirname($path));
                $requestParamters = "";
                $requestParamters .= PHP_EOL;
                foreach ($apiParam as $param) {
                    $requestParamters .= "        /** start ".$param[1]." */". PHP_EOL;
                    $requestParamters .= "        \"" . $param[1] . "\" => [//" . $param[2] . PHP_EOL;
                    $requestParamters .= "            \"validate\" => [" . PHP_EOL;
                    $requestParamters .= "                 \"rules\" => \"\"," . PHP_EOL;
                    $requestParamters .= "                 \"message\" => []," . PHP_EOL;
                    $requestParamters .= "                 \"validate_function\" => function(){" . PHP_EOL;
                    $requestParamters .= "                 " ."    return new SuccessMessage();". PHP_EOL;
                    $requestParamters .= "                 }," . PHP_EOL;
                    $requestParamters .= "            ]," . PHP_EOL;
                    $requestParamters .= "            \"attached_value\" => [" . PHP_EOL;
                    $requestParamters .= "                \"realParamterName\" => \"".$param[1]."\"," . PHP_EOL;
                    $requestParamters .= "                \"value\" => function(Request \$request, Configure \$iconfigure){" . PHP_EOL;
                    $requestParamters .= "                 " ."    return \$request->get('".$param[1]."');". PHP_EOL;
                    $requestParamters .= "                }," . PHP_EOL;
                    $requestParamters .= "            ]," . PHP_EOL;
                    $requestParamters .= "        ]," . PHP_EOL;
                    $requestParamters .= "        /** end ".$param[1]." */". PHP_EOL;
                }

                $contents = sprintf('
use Illuminate\Http\Request;
use Integration\Api\Configure\Configure;
use Integration\Api\Services\SuccessMessage;
use Integration\Api\Services\ErrorMessage;

return [
    \'parent\' => %s,//继承的配置文件

    "responseType" => "%s",
    
    /** start requestParamters */
    "requestParamters" => [
        %s
    ],
    /** end requestParamters */
];', '[]', 'json', $requestParamters);
                file_put_contents($path, '<?php' . PHP_EOL . $contents . '');
            }

        }
    }

    /**
     * @return string
     */
    protected function getActionCode()
    {
        return "    public function " . $this->actionName . "(Request \$request, Configure \$iconfigure)" . PHP_EOL . "    {" . PHP_EOL.\Config::get('interface_config.defaultActionCode').PHP_EOL."    }";
    }

    /**
     * 递归创建文件夹
     * @param $dir
     * @return bool
     */
    private function mkDirs($dir)
    {
        if (!is_dir($dir)) {
            if (!$this->mkDirs(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir, 0777)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $fielName
     *
     * @return array
     */
    private function getClassNameAndNameSpaceByFileName($fielName)
    {
        $file = fopen($fielName, "r");
        $result = 0;
        $nameSpace = "";
        $className = "";
        while(!feof($file))
        {
            $row = fgets($file);
            if ($result == 0) {
                if (preg_match("/^namespace(\s*)(.*?);/", $row, $data)) {
                    $nameSpace = $data[2];
                    $result = 1;
                }

            } elseif ($result == 1) {
                if (preg_match('/^class(\s*)(\w*)(\s*)/', $row, $data)) {

                    $className = $data[2];
                    break;
                }
            }
        }
        fclose($file);
        if ($result == 1) {
            return [$nameSpace.'\\'.$className => $className];
        }

        return [];
    }

    private function getControllerPath()
    {
        /**
         * @var \League\Flysystem\Filesystem $diriver
         */
        $diriver = $this->disk->getDriver();
        /**
         * @var \League\Flysystem\Adapter\Local $adapter
         */
        $adapter = $diriver->getAdapter();

        return $adapter->getPathPrefix();
    }

}