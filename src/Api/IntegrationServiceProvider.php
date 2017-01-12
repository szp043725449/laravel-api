<?php

namespace Integration\Api;

use Illuminate\Support\ServiceProvider;
use Integration\Api\Configure\Configure;
use Integration\Api\Exceptions\NotFoundConfigurePathException;
use \Config;
use Integration\Api\Console\ConfigureCommand;
use Integration\Api\Middleware\Integration;
use Integration\Api\Services\Authentication;
use Integration\Api\Services\SignatureOperation;
use Integration\Api\Services\SignMessage;
use Symfony\Component\Debug\Exception\ClassNotFoundException;

class IntegrationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Config/integration.php' => config_path('integration.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->checkConfigurePath();
        $this->registerConsoleCommands();
        $this->registerMiddleware();
        $this->registerConfigure();
        $this->registerSignatureOperation();
        $this->registerSignMessage();
        $this->registerAuth();
    }

    /**
     * @return bool
     * @throws NotFoundConfigurePathException
     */
    private function checkConfigurePath()
    {
        $path = Config::get('integration.configure_path');
        if ($path === false) {
            return;
        }
        if ($path && is_dir($path) && file_exists($path)) {
            return true;
        }

        throw new NotFoundConfigurePathException();
    }

    /**
     * Register console commands
     */
    private function registerConsoleCommands()
    {
        $this->commands([
            ConfigureCommand::class,
        ]);
    }

    /**
     * Register console middleware
     */
    private function registerMiddleware()
    {
        $this->app->make('router')->middleware('integration', Integration::class);
    }

    /**
     * Register Configure
     *
     * @return void
     */
    protected function registerConfigure()
    {
        $this->app->singleton('integration.configure', function ($app) {
            $path = $app->make('config')->get('integration.configure_path');
            $configure = new Configure($path);
            $app->instance('iconfigure', $configure);

            $app->alias( 'iconfigure', 'Integration\Api\Configure\Configure');

            return $configure;
        });

        $this->app->make('integration.configure');
    }

    /**
     * registerSignatureOperation
     *
     * @return void
     */
    protected function registerSignatureOperation()
    {
        $this->app->singleton('integration.signatureOperation', function ($app) {
            $className = $app->make('config')->get('integration.signOperationClass');
            $class = $app->make($className);

            if ($class instanceof SignatureOperation) {
                return $class;
            }
            throw new ClassNotFoundException($className.' not found', null);
        });
    }

    /**
     * registerSignatureOperation
     *
     * @return void
     */
    protected function registerSignMessage()
    {
        $this->app->singleton('integration.signmessage', function ($app) {
            $className = $app->make('config')->get('integration.signMessageClass');
            $class =  $app->make($className);

            if ($class instanceof SignMessage) {
                return $class;
            }

            throw new ClassNotFoundException($className.' not found', null);
        });
    }

    /**
     * registerAuth
     *
     * @return void
     */
    protected function registerAuth()
    {
        $this->app->singleton('integration.auth', function ($app) {
            $className = $app->make('config')->get('integration.authClass');
            $class =  $app->make($className);

            if ($class instanceof Authentication) {
                return $class;
            }

            throw new ClassNotFoundException($className.' not found', null);
        });
    }
}