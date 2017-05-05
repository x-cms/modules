<?php namespace Xcms\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Xcms\Modules\Support\Facades\ModulesManagementFacade;

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

        //Register related facades
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('ModulesManagement', ModulesManagementFacade::class);
    }

    protected function loadHelpers()
    {
        $helpers = $this->app['files']->glob(__DIR__ . '/../../helpers/*.php');
        foreach ($helpers as $helper) {
            require_once $helper;
        }
    }
}
