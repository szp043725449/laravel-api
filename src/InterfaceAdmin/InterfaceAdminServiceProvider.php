<?php

namespace Integration\InterfaceAdmin;

use Illuminate\Support\ServiceProvider;

class InterfaceAdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/interface_config.php' => config_path('interface_config.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}