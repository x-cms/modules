<?php namespace Xcms\Modules\Support\Facades;

use Illuminate\Support\Facades\Facade;

class ModulesManagementFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Xcms\Modules\Support\ModulesManagement::class;
    }
}
