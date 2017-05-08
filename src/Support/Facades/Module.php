<?php namespace Xcms\ModuleManager\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Module extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'module';
    }
}
