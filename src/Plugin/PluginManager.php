<?php

namespace Creatyon\Core\Plugin;

use Creatyon\Core\Plugin\Plugin as PluginModel;


class PluginManager implements PluginContract
{
    /**
     * Plugins Root Path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All Plugins Information.
     *
     * @var $plugins
     */
    protected $plugins;

    /**
     * Plugin constructor.
     *
     */
    public function __construct()
    {
        $this->basePath = config('plugin.path');
        $this->scanPlugins();
    }

    /**
     * Check if exists in dataBase.
     *
     * @param $plugin
     * @return bool
     */

    public function check($plugin)
    {
        $plugin = check_table_db(config('plugin.database.table'))?  PluginModel::where('plugin', $plugin)->first(): null;
        return $plugin;
    }

    /**
     * Check if plugin exists.
     *
     * @param $plugin
     * @return bool
     */
    public function has($plugin)
    {
        return $this->plugins->isNotEmpty() && $this->plugins->has($plugin);
    }

    /**
     * Get particular plugin all information.
     *
     * @param $PluginName
     * @return null|PluginManager
     */
    public function getPluginInfo($PluginName)
    {
        return isset($this->plugins[$PluginName]) ? $this->plugins[$PluginName] : null;
    }

    /**
     * Returns current plugin or particular plugin information.
     *
     * @param null $plugin
     * @param bool $collection
     *
     * @return bool
     */
    public function get($plugin = null, $collection = false)
    {
        if (is_null($plugin) || !$this->has($plugin)) {
            return false;
        }
        return !$collection ? $this->plugins[$plugin]->all() : $this->plugins[$plugin];
    }


    /**
     * Get all theme information.
     *
     * @return Collection
     */
    public function all()
    {
        return $this->plugins;
    }

    /**
     * Scan for all available Plugins.
     *
     * @return void
     */
    private function scanPlugins()
    {
        $pluginDirectories = glob($this->basePath.'/*', GLOB_ONLYDIR);
        $plugins = collect();

        if(count($pluginDirectories)){
            foreach ($pluginDirectories as $key => $pluginPath) {
                $pluginConfigPath = $pluginPath.'/'.config('plugin.config.name');
                $pluginChangelogPath = $pluginPath.'/'.config('plugin.config.changelog');

                if (file_exists($pluginConfigPath)) {
                    $p = json_decode(file_get_contents($pluginConfigPath), true);
                    $pluginConfig = $p;
                    $pluginConfig =  $this->checkInDatabase($pluginConfig, $key);

                    if($pluginConfig['core']){
                        $pluginConfig['active'] = true;
                    }

                    $c = json_decode(file_get_contents($pluginChangelogPath), true);
                    $pluginConfig['changelog'] = $c;
                    $pluginConfig['path'] = $pluginPath;

                    if (array_has($pluginConfig, 'plugin')) {
                        $plugins[data_get($pluginConfig, 'plugin')] = collect($pluginConfig);
                    }
                }
            }
        }

        $this->plugins = $plugins;
    }

    /**
     * Check Module to Database.
     * @param $plugin
     * @param $key
     * @return
     */
    public function checkInDatabase($plugin, $key)
    {
        $check = check_table_db(config('plugin.database.table'))? $this->check($plugin['name']) ?? $this->insertPlugin($plugin) : null;
        $plugin['id'] = $check ? $check->id : $key+1 ;
        $plugin['str'] = strtolower ( $plugin['name']) ;
        $plugin['check'] = $check ? true : false;
        $plugin['slug'] = $check ? $check ->slug : false;
        $plugin['icon'] = $check ? $check ->icon : false;
        $plugin['active'] = $check ? $check ->active : false;

        return $plugin;
    }

    /**
     * Insert Module in Database.
     *
     * @param $insert
     * @return PluginModel
     */
    public function insertPlugin($insert){

        $plugin = new PluginModel();
        $plugin->name = $insert['name'];
        $plugin->plugin = $insert['plugin'];
        $plugin->active = $insert['active'];
        $plugin->save();

        return $plugin;

    }

}
