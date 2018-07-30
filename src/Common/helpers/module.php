<?php

use Creatyon\Core\Module\ModuleManager;

if (! function_exists('module')) {
    function module()
    {
        return app(ModuleManager::class);
    }
}

if (!function_exists('check_module')) {
    /**
     * Check if there is a Module table in database.
     *
     * @return boolean
     */
    function check_module($module, $collection = null)
    {
        return Module::has($module)? Module::get($module,$collection) : false;
    }
}
