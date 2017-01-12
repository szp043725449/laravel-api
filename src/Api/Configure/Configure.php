<?php

namespace Integration\Api\Configure;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Integration\Api\Exceptions\NotFoundConfigurePathException;
use Integration\Api\Exceptions\ValidateFunctionReturnParameterException;
use Integration\Api\Services\ErrorMessage;
use Integration\Api\Services\Message;
use Integration\Api\Services\SuccessMessage;
use ReflectionClass;
use Validator;
use Closure;
use ReflectionFunction;

class Configure
{
    private $path;

    private $name;

    private $message = null;

    /**
     * @Disposed
     */
    private $disposed;

    private $fistName;

    private $closure;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return Disposed
     */
    public function getFirstDisposed()
    {
        if ( !$this->disposed[$this->fistName] ) {
            $this->getDisposedWithName($this->getName());
        }

        return $this->disposed[$this->fistName];
    }

    /**
     * @return mixed
     */
    public function getFistName()
    {
        return $this->fistName;
    }

    /**
     * @return mixed
     */
    public function getAllDisposed()
    {
        return $this->disposed;
    }


    /**
     * @return Disposed
     */
    public function getDisposedByName($name)
    {
        return $this->disposed[$name] ? $this->disposed[$name] : $this->getDisposedWithName($name);
    }

    public function getCurrentDisposed()
    {
        return $this->getDisposedWithName($this->getName());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param $name
     * @return Configure
     */
    public function setName($name)
    {
        if (!$this->fistName) {
            $this->fistName = $name;
        }
        $this->name = $name;

        return $this;
    }


    /**
     * @param $data
     * @return message
     */
    public function validate($data)
    {
        $disposed = $this->getFirstDisposed();
        $paramters = $disposed->getValidateData();
        foreach ($paramters as $key => $value) {
            if (isset($value['validate'])) {
                if (isset($value['validate']['rules']) && is_string($value['validate']['rules']) && $value['validate']['rules']) {
                    $message = isset($value['validate']['message']) && is_array($value['validate']['message']) && $value['validate']['message'] ? $value['validate']['message'] : [];
                    $validator = Validator::make($data, [$key => $value['validate']['rules']], $message);
                    if ($validator->failed()) {
                        $errorMessages = $validator->errors()->getMessages();
                        $code = isset($errorMessages[$key][0]['code']) ? $errorMessages[$key][0]['code'] : '';
                        $message = isset($errorMessages[$key][0]['message']) ? $errorMessages[$key][0]['message'] : '';

                        return $this->message = new ErrorMessage($code, $message);

                    }
                }
                if (isset($value['validate']['validate_function'])) {
                    $function = $value['validate']['validate_function'];
                    if ($function instanceof Closure) {

                        $result = $this->makeWithClosure($function);

                        if (!($result instanceof Message)) {
                            throw new ValidateFunctionReturnParameterException($key);
                        }
                        if ( $result instanceof ErrorMessage) {
                            return $this->message = $result;
                        }

                    }
                }
            }

        }

        return $this->message = new SuccessMessage();
    }

    /**
     * @return ParameterBag
     */
    public function attachedValue()
    {
        static $paramterBag;
        if ($paramterBag) {
            return $paramterBag;
        }
        $paramterBag = new ParameterBag();
        $disposed = $this->getFirstDisposed();
        foreach ($disposed->getValidateData() as $key=>$paramter) {
            if (isset($paramter['attached_value'])) {
                if (isset($paramter['attached_value']['value'])) {
                    $function = $paramter['attached_value']['value'];
                    if (isset($paramter['attached_value']['realParamterName']) && $paramter['attached_value']['realParamterName']) {
                        if ($function instanceof Closure) {
                            $result = $this->makeWithClosure($function);
                            $paramterBag->set($paramter['attached_value']['realParamterName'] ? $paramter['attached_value']['realParamterName'] : $key, $result);
                        }
                    }
                }
            }
        }

        return $paramterBag;
    }

    /**
     * @param Closure $closure
     * @return mixed
     */
    public function makeWithClosure(Closure $closure)
    {
        $ref = new ReflectionFunction($closure);
        $args = [];
        foreach ($ref->getParameters() as $reflectionParameter) {
            $app = \App::getInstance();
            $args[$reflectionParameter->getName()] = $app[$reflectionParameter->getName()];
        }
        $result = $ref->invokeArgs($args);

        return $result;
    }

    /**
     * @param $closure
     * @param array $args
     * @param bool $share
     * @return mixed
     */
    public function callFunction($closure, $args = [], $share = false)
    {
        $name = "";
        if (is_array($closure)) {
            if (isset($closure[0]) && isset($closure[1])) {
                if (method_exists($closure[0], $closure[1])) {
                    $class = $closure[0];
                    $method = $closure[1];
                    $return = null;
                    $reflectionClass = new ReflectionClass($class);
                    $reflectionFcuntion = $reflectionClass->getMethod($method);
                    $name = md5($reflectionClass->getName().$reflectionFcuntion->getName().json_encode($args));

                    if (isset($this->closure['class_function']) && $share) {
                        $classFunction = $this->closure['class_function'];
                        return $classFunction[$name];
                     }

                    foreach ($reflectionFcuntion->getParameters() as $reflectionParameter) {
                        $app = \App::getInstance();
                        if (isset($app[$reflectionParameter->getName()])) {
                            $args[$reflectionParameter->getName()] = $app[$reflectionParameter->getName()];
                        }
                    }
                    $contetns = $reflectionFcuntion->invokeArgs($class, $args);

                    $return[$name] = $contetns;

                    $this->closure['class_function'] = isset($this->closure['class_function']) ? array_merge($this->closure['class_function'], $return) : $return;
                    return $contetns;
                }
            }
        }
    }

    /**
     * @return ParameterBag
     */
    public function shareParameterBag()
    {
        static $parameterBag;
        if ($parameterBag) {
            return $parameterBag;
        }

        return $parameterBag = new ParameterBag();
    }

    /**
     * @return Response
     */
    public function send()
    {
        return $this->getSendResponseWithMessage($this->message);

    }

    /**
     * @param Message $message
     * @return Response
     */
    public function getSendResponseWithMessage(Message $message)
    {
        if ($message instanceof Message) {
            if ($this->getFirstDisposed()->getResponseType() == Disposed::RETURN_TYPE_TO_JSON) {
                return new JsonResponse($message->getContents(), Response::HTTP_OK);
            } elseif ($this->getFirstDisposed()->getMethod() == Disposed::RETURN_TYPE_TO_HTML) {

            }
        }
    }

    /**
     * @return \Integration\Api\Services\SignMessage|mixed
     */
    public function getSignMessage()
    {
        return \App::make('integration.signmessage');
    }

    /**
     * @param Disposed $disposed
     */
    protected function setDisposed($name, $disposed)
    {
        $this->disposed[$name] = $disposed;
    }

    /**
     * @param $name
     * @return Disposed
     */
    protected function getDisposedWithName($name)
    {
        static $s_disposed;

        if (isset($s_disposed[$name])) {
            return $s_disposed[$name];
        }

        $this->setName($name);

        $contents = $this->getDisposedContents($name);

        $disposed = $this->getDisposeWithData($contents);

        $this->setDisposed($name, $disposed);
        $s_disposed[$name] = $disposed;

        return $disposed;
    }

    /**
     * @param $name
     * @return string
     */
    private function getDisposedFilepath($name)
    {
        $nameArray = explode('.', $name);
        $fileName = $nameArray[count($nameArray)-1];
        $nameArray = array_map(function($value) use($fileName){
            if ($fileName != $value) {
                return ucfirst($value);
            }

            return $value;
        }, $nameArray);

        return $this->path."/".join('/', $nameArray).'.php';
    }

    /**
     * @param $name
     * @return array
     */
    private function getDisposedContents($name)
    {
        static $data;

        if (isset($data[$name])) {
            return $data[$name];
        }

        $disposedFilepath = $this->getDisposedFilepath($name);
        if (!file_exists($disposedFilepath)) {
            throw new NotFoundConfigurePathException($name."is not found");
        }

        $data[$name] = include_once $disposedFilepath;

        return $data[$name];
    }

    /**
     * @param array $data
     * @return Disposed
     */
    private function getDisposeWithData($data = [])
    {
        $reflector = new ReflectionClass(Disposed::class);
        $args = [];
        foreach ($reflector->getConstructor()->getParameters() as $parameter) {
            $name = $parameter->getName();
            if ( isset($data[$name]) ) {
                $args[$name] = $data[$name];
            } else {
                $args[$name] = null;
            }
        }

        return $reflector->newInstanceArgs($args);
    }
}