<?php namespace Xcms\ModuleManager\Providers;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use Xcms\ModuleManager\Services\ModuleMigrator;
use Xcms\ModuleManager\Support\ModuleManager;

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
        $this->registerMigrateRollbackCommand();
    }

    /**
     * register generator commands
     */
    private function generatorCommands()
    {
        $this->commands([
            \Xcms\ModuleManager\Console\Generators\MakeModuleCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeProviderCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeControllerCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeMiddlewareCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeRequestCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeModelCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeRepositoryCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeFacadeCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeServiceCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeSupportCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeViewCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeMigrationCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeCommand::class,
            \Xcms\ModuleManager\Console\Generators\MakeDataTableCommand::class,
        ]);
    }

    private function otherCommands()
    {
        $commands = [
//            'module.console.command.module-install' => \Xcms\ModuleManager\Console\Commands\InstallModuleCommand::class,
//            'module.console.command.module-uninstall' => \Xcms\ModuleManager\Console\Commands\UninstallModuleCommand::class,
//            'module.console.command.disable-module' => \Xcms\Modules\Console\Commands\DisableModuleCommand::class,
//            'module.console.command.enable-module' => \Xcms\Modules\Console\Commands\EnableModuleCommand::class,
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
            $module = $app->make(ModuleManager::class);
            return new \Xcms\ModuleManager\Console\Commands\ModuleMigrateCommand($app['migrator'], $module);
        });

        $this->commands('module.console.command.migrate');
    }

    /**
     * Register the module:migrate:rollback command.
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('module.console.command.migrate.rollback', function ($app) {
            $repository = $app['migration.repository'];

            $migrator = new Migrator($repository, $app['db'], $app['files']);

            $module = $app->make(ModuleManager::class);

            return new \Xcms\ModuleManager\Console\Commands\ModuleMigrateRollbackCommand($migrator, $module);
        });

        $this->commands('module.console.command.migrate.rollback');
    }

}
