<?php

namespace Xcms\Modules\Console\Commands;

use Xcms\Modules\Support\Module;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleMigrateResetCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:migrate:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback all database migrations for a specific or all modules';

    /**
     * @var Module
     */
    protected $module;

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param Module    $module
     * @param Filesystem $files
     * @param Migrator   $migrator
     */
    public function __construct(Module $module, Filesystem $files, Migrator $migrator)
    {
        parent::__construct();

        $this->module = $module;
        $this->files = $files;
        $this->migrator = $migrator;
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

        $this->reset();
    }

    /**
     * Run the migration reset for the current list of aliass.
     *
     * Migrations should be reset in the reverse order that they were
     * migrated up as. This ensures the database is properly reversed
     * without conflict.
     *
     * @param string $alias
     *
     * @return mixed
     */
    protected function reset()
    {
        $this->migrator->setconnection($this->input->getOption('database'));

        $files = $this->migrator->getMigrationFiles($this->getMigrationPaths());

        $migrations = array_reverse($this->migrator->getRepository()->getRan());

        if(count($migrations) == 0){
            $this->output("Nothing to rollback.");
        } else {
            $this->migrator->requireFiles($files);

            foreach($migrations as $migration){
                if(!array_key_exists($migration, $files)){
                    continue;
                }

                $this->runDown($files[$migration], (object) ["migration" => $migration]);
            }
        }

        foreach($this->migrator->getNotes() as $note){
            $this->output->writeln($note);
        }
    }

    /**
     * Run "down" a migration instance.
     *
     * @param string $alias
     * @param object $migration
     * @param bool   $pretend
     */
    protected function runDown($file, $migration)
    {
        $file = $this->migrator->getMigrationName($file);

        $instance = $this->migrator->resolve($file);

        $instance->down();

        $this->migrator->getRepository()->delete($migration);

        $this->info("Rolledback: ".$file);
    }

    /**
     * Generate a list of all migration paths, given the arguments/operations supplied.
     *
     * @return array
     */
    protected function getMigrationPaths(){
        $migrationPaths = [];

        foreach ($this->getaliassToReset() as $alias) {
            $migrationPaths[] = $this->getMigrationPath($alias);

            event($alias.'.module.reset', [$this->module, $this->option()]);
        }

        return $migrationPaths;
    }

    /**
     * Using the arguments, generate a list of aliass to reset the migrations for.
     *
     * @return array
     */
    protected function getAliasToReset(){
        if($this->validaliasProvided()){
            return [$this->argument("alias")];
        }

        if($this->option("force")){
            return $this->module->all()->pluck("alias");
        }

        return $this->module->isActivated()->pluck("alias");
    }

    /**
     * Determine if a valid alias has been provided as an argument.
     *
     * We will accept a alias as long as it is not empty and is enalbed (or force is passed).
     *
     * @return bool
     */
    protected function validaliasProvided(){
        if(empty($this->argument("alias"))){
            return false;
        }

        if($this->module->isActivated($this->argument("alias"))){
            return true;
        }

        if($this->option("force")){
            return true;
        }

        return false;
    }

    /**
     * Get the console command parameters.
     *
     * @param string $alias
     *
     * @return array
     */
    protected function getParameters($alias)
    {
        $params = [];

        $params['--path'] = $this->getMigrationPath($alias);

        if ($option = $this->option('database')) {
            $params['--database'] = $option;
        }

        if ($option = $this->option('pretend')) {
            $params['--pretend'] = $option;
        }

        if ($option = $this->option('seed')) {
            $params['--seed'] = $option;
        }

        return $params;
    }

    /**
     * Get migrations path.
     *
     * @return string
     */
    protected function getMigrationPath($alias)
    {
        return module_base_path($alias.'/database/migrations');
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
            ['pretend', null, InputOption::VALUE_OPTIONAL, 'Dump the SQL queries that would be run.'],
            ['seed', null, InputOption::VALUE_OPTIONAL, 'Indicates if the seed task should be re-run.'],
        ];
    }
}
