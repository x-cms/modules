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
        $this->migrationCommands();
        $this->otherCommands();
    }

    /**
     * register generator commands
     */
    private function generatorCommands()
    {
        $generators = [
            'module_manager.console.generator.make-module' => \Xcms\Modules\Console\Generators\MakeModule::class,
            'module_manager.console.generator.make-provider' => \Xcms\Modules\Console\Generators\MakeProvider::class,
            'module_manager.console.generator.make-controller' => \Xcms\Modules\Console\Generators\MakeController::class,
            'module_manager.console.generator.make-middleware' => \Xcms\Modules\Console\Generators\MakeMiddleware::class,
            'module_manager.console.generator.make-request' => \Xcms\Modules\Console\Generators\MakeRequest::class,
            'module_manager.console.generator.make-model' => \Xcms\Modules\Console\Generators\MakeModel::class,
            'module_manager.console.generator.make-repository' => \Xcms\Modules\Console\Generators\MakeRepository::class,
            'module_manager.console.generator.make-facade' => \Xcms\Modules\Console\Generators\MakeFacade::class,
            'module_manager.console.generator.make-service' => \Xcms\Modules\Console\Generators\MakeService::class,
            'module_manager.console.generator.make-support' => \Xcms\Modules\Console\Generators\MakeSupport::class,
            'module_manager.console.generator.make-view' => \Xcms\Modules\Console\Generators\MakeView::class,
            'module_manager.console.generator.make-migration' => \Xcms\Modules\Console\Generators\MakeMigration::class,
            'module_manager.console.generator.make-command' => \Xcms\Modules\Console\Generators\MakeCommand::class,
        ];
        foreach ($generators as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
    }

    /**
     * register database migrate related command
     */
    private function migrationCommands()
    {
        $this->registerModuleMigrator();
        $this->registerMigrateCommand();
    }

    private function registerMigrateCommand()
    {
        $commands = [
            'module_manager.console.command.module-migrate' => \Xcms\Modules\Console\Migrations\ModuleMigrateCommand::class
        ];
        foreach ($commands as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
        $this->registerRollbackCommand();

    }
    private function otherCommands()
    {
        $commands = [
            'module_manager.console.command.module-install' => \Xcms\Modules\Console\Commands\InstallModuleCommand::class,
            'module_manager.console.command.module-uninstall' => \Xcms\Modules\Console\Commands\UninstallModuleCommand::class,
            'module_manager.console.command.disable-module' => \Xcms\Modules\Console\Commands\DisableModuleCommand::class,
            'module_manager.console.command.enable-module' => \Xcms\Modules\Console\Commands\EnableModuleCommand::class,
            'module_manager.console.command.module-route-list' => \Xcms\Modules\Console\Commands\RouteListCommand::class,
        ];
        foreach ($commands as $slug => $class) {
            $this->app->singleton($slug, function ($app) use ($slug, $class) {
                return $app[$class];
            });

            $this->commands($slug);
        }
    }
    /**
     * Register the "rollback" migration command.
     *
     * @return void
     */
    protected function registerRollbackCommand()
    {
        $this->app->singleton('module_manager.console.command.migration-rollback', function ($app) {
            return new \Xcms\Modules\Console\Migrations\RollbackCommand($app['module.migrator']);
        });
        $this->commands('module_manager.console.command.migration-rollback');
    }


    protected function registerModuleMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('module.migrator', function ($app) {

            return new ModuleMigrator($app);
        });
    }
}
