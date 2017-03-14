<?php

namespace Integration\BladeExtends;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Integration\BladeExtends\Contracts\Directive;
use Psy\Exception\RuntimeException;

class Directives
{
    private static $directives = [];

    public static function load(Application $app, $directiveString)
    {
        if ($directiveString instanceof Directive) {
            $directive = $directiveString;
            $directiveString = get_class($directiveString);
        } else {
            $directive = $app->make($directiveString);
            if (!($directive instanceof Directive)) {
                throw new RuntimeException("directiveString error");
            }
        }

        self::$directives[$directiveString] = $directive;
    }

    public static function getConfig($name= '')
    {
        static $config;

        $name = $name ? 'blade_extends.'.$name : $name;
        if ($data = \Config::get($name)) {
            return $data;
        }
        $configPath = __DIR__ . '/./Config/blade-extends.php';

        if (!$config) {
            $config = require_once $configPath;
        }
        $name = str_replace('blade_extends.', '', $name);

        return array_get($config, $name);
    }
}