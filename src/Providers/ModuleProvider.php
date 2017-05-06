<?php namespace Xcms\Modules\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;
use Xcms\Modules\Support\Facades\Module as ModuleFacade;
use Xcms\Modules\Support\Module;

class ModuleProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config' => base_path('config'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //Load config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/module_manager.php', 'module_manager'
        );

        //Load helpers
        $this->loadHelpers();

        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(LoadModulesServiceProvider::class);

        //Load module
        $this->app->singleton('module', function ($app) {
            $composer = $app->make(Composer::class);
            return new Module($composer);
        });

        //Register related facades
        $loader = AliasLoader::getInstance();
        $loader->alias('Module', ModuleFacade::class);
    }

    protected function loadHelpers()
    {
        $helpers = $this->app['files']->glob(__DIR__ . '/../../helpers/*.php');
        foreach ($helpers as $helper) {
            require_once $helper;
        }
    }
}
