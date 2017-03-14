<?php

namespace Integration\BladeExtends\Contracts;

use Illuminate\Contracts\Foundation\Application;
use Integration\BladeExtends\Directives;

abstract class Directive
{
    private static $configDirective = [];

    private $config;

    private $app;

    public function __construct(Application $app)
    {
        $this->config = $app->make('config');
        $this->app = $app;
        if (!self::$configDirective) {
            self::$configDirective = Directives::getConfig('directive');
        }
        /** @var \Illuminate\View\Compilers\BladeCompiler $blade */
        $blade = $app->make('view')->getEngineResolver()->resolve('blade')->getCompiler();
        $directive = $this;
        foreach (self::$configDirective as $key=>$value) {
            $method = $key.'Tag';
            $replacement = $value['replacement'];
            $pattern = $value['pattern'];
            if (method_exists($this, $method)) {
                $blade->extend(function ($bladeString) use ($app, $directive, $method, $replacement, $pattern, $blade) {
                    return $directive->$method($bladeString, $app, $pattern, $replacement, $blade);
                });
            }
        }
    }
}