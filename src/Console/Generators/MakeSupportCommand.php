<?php namespace Xcms\ModuleManager\Console\Generators;

class MakeSupportCommand extends AbstractGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make:support
    	{alias : The alias of the module}
    	{name : The class name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new module support class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Support';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../resources/stubs/support/support.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return 'Support\\' . $this->argument('name');
    }
}
