<?php namespace Xcms\ModuleManager\Console\Generators;

class MakeTraitCommand extends AbstractGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:trait
    	{alias : The alias of the module}
    	{name : The class name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new module trait class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Trait';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../resources/stubs/traits/trait.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return 'Traits\\' . $this->argument('name');
    }
}
