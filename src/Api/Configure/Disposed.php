<?php

namespace Integration\Api\Configure;

class Disposed
{
    const RETURN_TYPE_TO_JSON = 'json';

    const RETURN_TYPE_TO_HTML = 'html';

    protected $method;

    protected $responseType;

    protected $parent;

    protected $paramters;

    /**
     * Disposed constructor.
     * @param $method
     * @param $responseType
     * @param $parent
     * @param array $requestParamters
     */
    public function __construct($method = "", $responseType = "", $parent = "", $requestParamters = [])
    {

        $this->method = $method;
        $this->responseType = $responseType;
        if ($parent) {
            $_parent = [];
            if (is_array($parent)) {

                foreach ($parent as $p) {
                    $disposed = \App::make('integration.configure')->setName($p)->getCurrentDisposed();
                    $_parent[] = $disposed;
                }
            } else {
                $disposed = \App::make('integration.configure')->setName($parent)->getCurrentDisposed();
                $_parent[] = $disposed;
            }
            $this->parent = $_parent;
        }
        $requestParamters = (array)$requestParamters;

        $this->paramters = new ParameterBag([
            'method' => $method,
            'responseType' => $responseType,
            'requestParamters' => $requestParamters,
        ]);

        $this->init();

    }

    /**
     * @return ParameterBag
     */
    public function getParamters()
    {
        return $this->paramters;
    }

    /**
     * @return array
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * @param mixed $responseType
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
    }

    /**
     * @return mixed
     */
    public function getValidateData()
    {
        $paramters = $this->getParamters();
        $requestParamters = $paramters->get('requestParamters');

        return $requestParamters;
    }

    private function init()
    {
        if ($_parent = $this->getParent()) {
            foreach ($_parent as $parent) {
                foreach ($parent->getParamters() as $key => $value) {
                    $this->getParamters()->set($key, $this->merge($value, $this->getParamters()->get($key)));
                    if (property_exists($this, $key)) {
                        $this->$key = $this->getParamters()->get($key);
                    }
                }
            }
        }
    }

    /**
     * 算法需要重新改
     * @param $object1
     * @param $object2
     * @return mixed
     */
    private function merge($object1, $object2)
    {
        if (gettype($object1) != gettype($object2)) {
            if (!is_null($object2)) {
               return $object2;
            }
            if (!is_null($object1)) {
                return $object1;
            }

            return $object2;
        }


        if (is_string($object2)) {
            if (strlen($object2) >0 ){
                return $object2;
            }

            return $object1;
        }

        $result = $object2;

        if (is_array($object1)) {
            foreach ($object1 as $key => $value) {
                if (!isset($object1[$key])) {
                    $result[$key] = $value;
                } else {
                    $mergeArray = $this->merge($value, isset($result[$key]) ? $result[$key] : null);
                    $result[$key] = $mergeArray;
                }
            }
        }

        return $result;
    }
}