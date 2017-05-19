<?php namespace Xcms\ModuleManager\Console\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeModuleCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:module {alias : The alias of the module}';

    /**
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Array to store the configuration details.
     *
     * @var array
     */
    protected $container = [];

    /**
     * Accepted module types
     * @var array
     */
    protected $acceptedTypes = [
        'module' => 'Module',
        'plugin' => 'Plugins',
    ];

    protected $moduleType;

    protected $moduleFolderName;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->moduleType = 'module';
        if (!in_array($this->moduleType, array_keys($this->acceptedTypes))) {
            $this->moduleType = 'module';
        }

        $this->container['alias'] = snake_case($this->argument('alias'));

        $this->step1();
    }

    private function step1()
    {
        $this->moduleFolderName = $this->ask('Please enter the folder for the module:', $this->container['alias']);
        $this->container['name'] = $this->ask('Please enter the name of the module:', studly_case($this->container['alias']));
        $this->container['author'] = $this->ask('Please enter the author for the module:');
        $this->container['description'] = $this->ask('Please enter the description of the module:', 'This is the description for the '.$this->container['name'].' module.');
        $this->container['namespace'] = $this->ask('Please enter the namespace of the module:', $this->laravel->getNamespace() . $this->acceptedTypes[$this->moduleType] . '\\' . studly_case($this->container['alias']));
        $this->container['version'] = $this->ask('Please enter the module version:', '1.0');
        $this->container['autoload'] = $this->ask('Please enter the autoload of the module:', 'psr-4');

        $this->step2();
    }

    private function step2()
    {
        $this->generatingModule();

        $modules = get_all_module_information();

        \ModuleManager::setModules($modules);
        \ModuleManager::enableModule($this->argument('alias'));
        \ModuleManager::modifyModuleAutoload($this->argument('alias'));

        $this->info("\nYour module generated successfully.");
    }

    private function generatingModule()
    {
        $pathType = $this->makeModuleFolder();
        $directory = $pathType($this->moduleFolderName);
        $source = __DIR__ . '/../../../resources/stubs/_folder-structure';

        /**
         * Make directory
         */
        $this->files->makeDirectory($directory);
        $this->files->copyDirectory($source, $directory, null);

        /**
         * Replace files placeholder
         */
        $files = $this->files->allFiles($directory);
        foreach ($files as $file) {
            $contents = $this->replacePlaceholders($file->getContents());
            $filePath = $pathType($this->moduleFolderName . '/' . $file->getRelativePathname());

            $this->files->put($filePath, $contents);
        }

        /**
         * Modify the module.json information
         */
        \File::put($directory . '/module.json', json_encode_pretify($this->container));
    }

    private function makeModuleFolder()
    {
        switch ($this->moduleType) {
            case 'module':
                if (!$this->files->isDirectory(module_base_path())) {
                    $this->files->makeDirectory(module_base_path());
                }
                return 'module_base_path';
                break;
            case 'plugin':
            default:
                if (!$this->files->isDirectory(plugins_base_path())) {
                    $this->files->makeDirectory(plugins_base_path());
                }
                return 'plugins_base_path';
                break;
        }
    }

    protected function replacePlaceholders($contents)
    {
        $find = [
            'DummyNamespace',
            'DummyAlias',
            'DummyName',
        ];

        $replace = [
            $this->container['namespace'],
            $this->container['alias'],
            $this->container['name'],
        ];

        return str_replace($find, $replace, $contents);
    }
}
