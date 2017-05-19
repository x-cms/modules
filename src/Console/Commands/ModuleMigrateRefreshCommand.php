<?php

namespace Xcms\ModuleManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMigrateRefreshCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and re-run all migrations for a specific or all modules';

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

        $alias = $this->argument('alias');

        $this->call('module:migrate:reset', [
            'alias'       => $alias,
            '--database' => $this->option('database'),
            '--force'    => $this->option('force'),
            '--pretend'  => $this->option('pretend'),
        ]);

        $this->call('module:migrate', [
            'alias'       => $alias,
            '--database' => $this->option('database'),
        ]);

        if ($this->needsSeeding()) {
            $this->runSeeder($alias, $this->option('database'));
        }

        if (isset($alias)) {
            $module = $this->laravel['module']->getModule($alias);

            event($alias.'.module.refreshed', [$module, $this->option()]);

            $this->info('Module has been refreshed.');
        } else {
            $this->info('All modules have been refreshed.');
        }
    }

    /**
     * Determine if the developer has requested database seeding.
     *
     * @return bool
     */
    protected function needsSeeding()
    {
        return $this->option('seed');
    }

    /**
     * Run the module seeder command.
     *
     * @param string $database
     */
    protected function runSeeder($alias = null, $database = null)
    {
        $this->call('module:seed', [
            'alias'       => $alias,
            '--database' => $database,
        ]);
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
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
        ];
    }
}
