<?php

namespace Integration\BladeExtends;

use Illuminate\Support\ServiceProvider;

class BladeExtendsServiceProvider  extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/blade-extends.php' => config_path('blade_extends.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $classArray = Directives::getConfig('directiveExtendsClass');
        foreach ($classArray as $class) {
            Directives::load($this->app, $class);
        }

    }
}