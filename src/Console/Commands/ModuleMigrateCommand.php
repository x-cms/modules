<?php

namespace Xcms\Modules\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Xcms\Modules\Support\Module;

class ModuleMigrateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations for a specific or all modules';

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var Migrator
     */
    protected $migrator;

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
        $this->prepareDatabase();
        if (!empty($this->argument('alias'))) {
            $module = $this->module->getModule($this->argument('alias'));
            if ($this->module->isActivated($module['alias'])) {
                return $this->migrate($module['alias']);
            } elseif ($this->option('force')) {
                return $this->migrate($module['alias']);
            } else {
                return $this->error('Nothing to migrate.');
            }
        } else {
            if ($this->option('force')) {
                $modules = get_all_module_information();
            } else {
                $modules = get_all_module_information();
            }

            foreach ($modules as $module) {
                $this->migrate($module['alias']);
            }
        }
    }

    /**
     * Run migrations for the specified module.
     *
     * @param string $alias
     *
     * @return mixed
     */
    protected function migrate($alias)
    {
        if ($this->module->exists($alias)) {
            $module = $this->module->getModule($alias);
            $pretend = Arr::get($this->option(), 'pretend', false);
            $step = Arr::get($this->option(), 'step', false);
            $path = $this->getMigrationPath($alias);

            $this->migrator->run($path, ['pretend' => $pretend, 'step' => $step]);

            event($alias.'.module.migrated', [$module, $this->option()]);

            // Once the migrator has run we will grab the note output and send it out to
            // the console screen, since the migrator itself functions without having
            // any instances of the OutputInterface contract passed into the class.
            foreach ($this->migrator->getNotes() as $note) {
                if (!$this->option('quiet')) {
                    $this->line($note);
                }
            }

            // Finally, if the "seed" option has been given, we will re-run the database
            // seed task to re-populate the database, which is convenient when adding
            // a migration and a seed at the same time, as it is only this command.
            if ($this->option('seed')) {
                $this->call('module:seed', ['module' => $alias, '--force' => true]);
            }
        } else {
            return $this->error('Module does not exist.');
        }
    }

    /**
     * Get migration directory path.
     *
     * @param string $slug
     *
     * @return string
     */
    protected function getMigrationPath($slug)
    {
        return module_base_path($slug.'/database/migrations');
    }

    /**
     * Prepare the migration database for running.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->option('database'));

        if (!$this->migrator->repositoryExists()) {
            $options = ['--database' => $this->option('database')];

            $this->call('migrate:install', $options);
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
            ['seed', null, InputOption::VALUE_NONE, 'Indicates if the seed task should be re-run.'],
            ['step', null, InputOption::VALUE_NONE, 'Force the migrations to be run so they can be rolled back individually.'],
        ];
    }
}
