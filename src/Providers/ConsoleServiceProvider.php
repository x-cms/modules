<?php namespace Xcms\ModuleManager\Providers;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use Xcms\ModuleManager\Services\ModuleMigrator;

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
        $generators = [
            'module.console.generator.make-module' => \Xcms\ModuleManager\Console\Generators\MakeModuleCommand::class,
            'module.console.generator.make-provider' => \Xcms\ModuleManager\Console\Generators\MakeProviderCommand::class,
            'module.console.generator.make-controller' => \Xcms\ModuleManager\Console\Generators\MakeControllerCommand::class,
            'module.console.generator.make-middleware' => \Xcms\ModuleManager\Console\Generators\MakeMiddlewareCommand::class,
            'module.console.generator.make-request' => \Xcms\ModuleManager\Console\Generators\MakeRequestCommand::class,
            'module.console.generator.make-model' => \Xcms\ModuleManager\Console\Generators\MakeModelCommand::class,
            'module.console.generator.make-repository' => \Xcms\ModuleManager\Console\Generators\MakeRepositoryCommand::class,
            'module.console.generator.make-facade' => \Xcms\ModuleManager\Console\Generators\MakeFacadeCommand::class,
            'module.console.generator.make-service' => \Xcms\ModuleManager\Console\Generators\MakeServiceCommand::class,
            'module.console.generator.make-support' => \Xcms\ModuleManager\Console\Generators\MakeSupportCommand::class,
            'module.console.generator.make-view' => \Xcms\ModuleManager\Console\Generators\MakeViewCommand::class,
            'module.console.generator.make-migration' => \Xcms\ModuleManager\Console\Generators\MakeMigrationCommand::class,
            'module.console.generator.make-command' => \Xcms\ModuleManager\Console\Generators\MakeCommand::class,
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
            return new \Xcms\ModuleManager\Console\Commands\ModuleMigrateCommand($app['migrator'], $app['module']);
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

            return new \Xcms\ModuleManager\Console\Commands\ModuleMigrateRollbackCommand($migrator, $app['module']);
        });

        $this->commands('module.console.command.migrate.rollback');
    }

}
