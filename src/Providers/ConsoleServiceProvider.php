<?php namespace Xcms\Modules\Providers;

use Illuminate\Support\ServiceProvider;
use Xcms\Modules\Services\ModuleMigrator;

/**
 * Class ConsoleServiceProvider
 * @package Mrabbani\ModuleManager\Providers
 */
class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->generatorCommands();
        $this->otherCommands();
        $this->registerMigrateCommand();
    }

    /**
     * register generator commands
     */
    private function generatorCommands()
    {
        $generators = [
            'module.console.generator.make-module' => \Xcms\Modules\Console\Generators\MakeModule::class,
            'module.console.generator.make-provider' => \Xcms\Modules\Console\Generators\MakeProvider::class,
            'module.console.generator.make-controller' => \Xcms\Modules\Console\Generators\MakeController::class,
            'module.console.generator.make-middleware' => \Xcms\Modules\Console\Generators\MakeMiddleware::class,
            'module.console.generator.make-request' => \Xcms\Modules\Console\Generators\MakeRequest::class,
            'module.console.generator.make-model' => \Xcms\Modules\Console\Generators\MakeModel::class,
            'module.console.generator.make-repository' => \Xcms\Modules\Console\Generators\MakeRepository::class,
            'module.console.generator.make-facade' => \Xcms\Modules\Console\Generators\MakeFacade::class,
            'module.console.generator.make-service' => \Xcms\Modules\Console\Generators\MakeService::class,
            'module.console.generator.make-support' => \Xcms\Modules\Console\Generators\MakeSupport::class,
            'module.console.generator.make-view' => \Xcms\Modules\Console\Generators\MakeView::class,
            'module.console.generator.make-migration' => \Xcms\Modules\Console\Generators\MakeMigration::class,
            'module.console.generator.make-command' => \Xcms\Modules\Console\Generators\MakeCommand::class,
        ];
        foreach ($generators as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
    }

    private function otherCommands()
    {
        $commands = [
//            'module.console.command.module-install' => \Xcms\Modules\Console\Commands\InstallModuleCommand::class,
//            'module.console.command.module-uninstall' => \Xcms\Modules\Console\Commands\UninstallModuleCommand::class,
            'module.console.command.disable-module' => \Xcms\Modules\Console\Commands\DisableModuleCommand::class,
            'module.console.command.enable-module' => \Xcms\Modules\Console\Commands\EnableModuleCommand::class,
//            'module.console.command.module-route-list' => \Xcms\Modules\Console\Commands\RouteListCommand::class,
        ];
        foreach ($commands as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
    }

    /**
     * Register the module:migrate command.
     */
    private function registerMigrateCommand()
    {
        $this->app->singleton('module.console.command.migrate', function ($app) {
            return new \Xcms\Modules\Console\Commands\ModuleMigrateCommand($app['migrator'], $app['module']);
        });

        $this->commands('module.console.command.migrate');
    }

}
