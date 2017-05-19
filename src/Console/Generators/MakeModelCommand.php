<?php namespace Xcms\ModuleManager\Console\Generators;

use Illuminate\Support\Str;

class MakeModelCommand extends AbstractGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:model
    	{alias : The alias of the module}
    	{name : The class name}
    	{--migration : Create a new migration file for the model}
    	{--with-contract : create model related contract}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new module model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    protected $buildContract = false;

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $nameInput = $this->getNameInput();

        $name = $this->parseName($nameInput);

        $path = $this->getPath($name);

        if ($this->alreadyExists($nameInput)) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        $this->makeDirectory($path);

        $this->files->put($path, $this->buildClass($name));

        /**
         * Create model contract
         */

        if($this->option('with-contract')) {
            $this->buildContract = true;

            $contractName = 'Contracts/' . get_file_name($path, '.php');
            $contractPath = get_base_folder($path) . $contractName . 'ModelContract.php';

            $this->makeDirectory($contractPath);
            $this->files->put($contractPath, $this->buildClass('Models\\' . $contractName));
        }

        /**
         * Create model migration
         */
        if ($this->option('migration')) {
            $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

            $this->call('module:make:migration', [
                'alias'     => $this->argument('alias'),
                'name'     => "create_{$table}_table",
                '--create' => $table,
            ]);
        }

        $this->info($this->type . ' created successfully.');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->buildContract === true) {
            return __DIR__ . '/../../../resources/stubs/models/model.contract.stub';
        }
        return __DIR__ . '/../../../resources/stubs/models/model.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        if ($this->buildContract === true) {
            return 'Models\\Contracts\\' . $this->argument('name');
        }
        return 'Models\\' . $this->argument('name');
    }

    protected function replaceParameters(&$stub)
    {
        $stub = str_replace([
            '{table}',
        ], [
            snake_case(str_plural($this->argument('name'))),
        ], $stub);
    }
}
