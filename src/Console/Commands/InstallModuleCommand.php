<?php namespace Xcms\ModuleManager\Console\Commands;

use Illuminate\Console\Command;

class InstallModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:install {alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install module';

    /**
     * @var array
     */
    protected $container = [];

    /**
     * @var array
     */
    protected $dbInfo = [];

    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    protected $app;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->app = app();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /**
         * Migrate tables
         */
        \ModuleManager::enableModule($this->argument('alias'));
        \ModuleManager::modifyModuleAutoload($this->argument('alias'));

        $this->line('Install module dependencies...');

        $this->info("\nModule " . $this->argument('alias') . " installed.");
    }
}