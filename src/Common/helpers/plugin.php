<?php

use Creatyon\Core\Plugin\PluginManager;

if (! function_exists('plugin')) {
    function plugin()
    {
        return app(PluginManager::class);
    }
}



if (!function_exists('check_plugin')) {
    /**
     * Check if there is a plugin table in database.
     *
     * @return boolean
     */
    function check_plugin($module, $collection = null)
    {
        return Plugin::has($module)? Plugin::get($module,$collection) : false;
    }
}
