<?php

namespace Integration\BladeExtends;


abstract class DataSource
{
    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var string
     */
    protected $key = "_key";

    /**
     * @var string
     */
    protected $value = "_value";

    public abstract function getIterator();

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string
     *
     * @return DataSource
     */
    public static function init($string)
    {
        $string = "{".$string."}";
        $args = json_decode($string , true);
        $class = $args['class'];
        $class = '\\'.str_replace('.', '\\', $class);
        /** @var DataSource $dataSource */
        $dataSource = \App::make($class);
        $dataSource->key = array_get($args, 'key', $dataSource->key);
        $dataSource->value = array_get($args, 'value', $dataSource->value);
        $dataSource->args = array_get($args, 'args', $dataSource->args);

        return $dataSource;
    }
}