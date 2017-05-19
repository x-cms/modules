<?php namespace Xcms\ModuleManager\Console\Generators;

use Illuminate\Console\Command;

class MakeMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:migration 
        {alias : The alias of the module.}
        {name : The name of the migration.}
        {--create : The table to be created.}
        {--table= : The table to migrate.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new module based migration file';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $arguments = $this->argument();
        $option = $this->option();
        $options = [];

        array_walk($option, function (&$value, $key) use (&$options) {
            $options['--'.$key] = $value;
        });

        unset($arguments['alias']);

        $options['--path'] = str_replace(realpath(base_path()), '', $this->getMigrationPath());
        $options['--path'] = ltrim($options['--path'], '/');

        return $this->call('make:migration', array_merge($arguments, $options));
    }

    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        $module = get_module_information($this->argument('alias'));
        $baseDir = get_base_folder(array_get($module, 'file'));
        $path = $baseDir . 'database/migrations';
        return $path;
    }
}
