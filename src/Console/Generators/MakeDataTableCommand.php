<?php namespace Xcms\ModuleManager\Console\Generators;

class MakeDataTableCommand extends AbstractGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:datatable
    	{alias : The alias of the module}
    	{name : The class name}';

    protected $description = 'Create a new DataTable service class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Datatable';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../resources/stubs/datatables/datatable.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return 'Http\\DataTables\\' . $this->argument('name');
    }
}