<?php namespace Xcms\ModuleManager\Providers;

use \Illuminate\Support\ServiceProvider;
use File;

class LoadModulesServiceProvider extends ServiceProvider
{
    /**
     * Modules information
     * @var array
     */
    protected $modules = [];

    protected $notLoadedModules = [];

    public function register()
    {
        $this->modules = get_all_module_information();

        foreach ($this->modules as $module) {
            $needToBootstrap = false;
            if (array_get($module, 'enabled', null) === true) {
                $needToBootstrap = true;
            }
            if ($needToBootstrap) {
                /**
                 * Register module
                 */
                $moduleProvider = $module['namespace'] . '\Providers\ModuleServiceProvider';

                if (class_exists($moduleProvider)) {
                    $this->app->register($moduleProvider);
                } else {
                    $this->notLoadedModules[] = $moduleProvider;
                }
            }
        }
    }

    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    private function booted()
    {
        \ModuleManager::setModules($this->modules);
    }
}
