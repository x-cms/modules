<?php

namespace Xcms\ModuleManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Xcms\ModuleManager\Support\Module;
use Xcms\ModuleManager\Traits\MigrationTrait;

class ModuleMigrateRollbackCommand extends Command
{
    use MigrationTrait, ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the last database migrations for a specific or all modules';

    /**
     * The migrator instance.
     *
     * @var \Illuminate\Database\Migrations\Migrator
     */
    protected $migrator;

    /**
     * @var Module
     */
    protected $module;

    /**
     * Create a new command instance.
     *
     * @param Migrator $migrator
     * @param Module  $module
     */
    public function __construct(Migrator $migrator, Module $module)
    {
        parent::__construct();

        $this->migrator = $migrator;
        $this->module = $module;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->migrator->setConnection($this->option('database'));

        $paths = $this->getMigrationPaths();
        $this->migrator->rollback(
            $paths, ['pretend' => $this->option('pretend'), 'step' => (int) $this->option('step')]
        );

        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [['alias', InputArgument::OPTIONAL, 'Module alias.']];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run while in production.'],
            ['pretend', null, InputOption::VALUE_NONE, 'Dump the SQL queries that would be run.'],
            ['step', null, InputOption::VALUE_OPTIONAL, 'The number of migrations to be reverted.'],
        ];
    }

    /**
     * Get all of the migration paths.
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        $alias = $this->argument('alias');
        $paths = [];

        if ($alias) {
            $paths[] = module_base_path($alias.'/database/migrations');
        } else {
            foreach (get_all_module_information() as $module) {
                $paths[] = module_base_path($module['alias'].'/database/migrations');
            }
        }

        return $paths;
    }
}
