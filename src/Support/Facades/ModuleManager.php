<?php namespace Xcms\ModuleManager\Support\Facades;

use Illuminate\Support\Facades\Facade;

class ModuleManager extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Xcms\ModuleManager\Support\ModuleManager::class;
    }
}
