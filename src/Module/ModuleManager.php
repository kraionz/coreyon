<?php

namespace Creatyon\Core\Module;

use Creatyon\Core\Module\Module as ModuleModel;


class ModuleManager implements ModuleContract
{

    /**
     * Modules Root Path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All Modules Information.
     *
     * @var $modules
     */
    protected $modules;

    /**
     * Module constructor.
     *
     */
    public function __construct()
    {
        $this->basePath = config('module.path');
        $this->scanModules();
    }

    /**
     * Check if exists in dataBase.
     *
     * @param string $module
     *
     * @return bool
     */

    public function check($module)
    {
        $module =  check_table_db(config('module.database.table'))? ModuleModel::where(['module' => $module])->first(): null;
        return $module;
    }

    /**
     * Check if module exists.
     *
     * @param string $module
     *
     * @return bool
     */
    public function has($module)
    {
        return $this->modules->isNotEmpty() && $this->modules->has($module);
    }

    /**
     * Get particular module all information.
     *
     * @param string $moduleName
     *
     * @return null|ModuleManager
     */
    public function getModuleInfo($moduleName)
    {
        return isset($this->modules[$moduleName]) ? $this->modules[$moduleName] : null;
    }

    /**
     * Returns current module or particular module information.
     *
     * @param string $module
     * @param bool   $collection
     *
     * @return bool
     */
    public function get($module = null, $collection = false)
    {
        if (is_null($module) || !$this->has($module)) {
            return false;
        }
        return !$collection ? $this->modules[$module]->all() : $this->modules[$module];
    }


    /**
     * Get all module information.
     *
     * @return collection
     */
    public function all()
    {
        return $this->modules;
    }

    /**
     * Scan for all available modules.
     *
     * @return void
     */
    private function scanModules()
    {
        $moduleDirectories = glob($this->basePath.'/*', GLOB_ONLYDIR);
        $modules = collect();

        if(count($moduleDirectories)) {
            foreach ($moduleDirectories as $key => $modulePath) {

                $moduleConfigPath = $modulePath . '/' . config('module.config.name');
                $moduleChangelogPath = $modulePath . '/' . config('module.config.changelog');

                if (file_exists($moduleConfigPath)) {

                    $m = json_decode(file_get_contents($moduleConfigPath), true);

                    $moduleConfig = $m;
                    $moduleConfig = $this->checkInDatabase($moduleConfig, $key);

                    if ($moduleConfig['core']) {
                        $moduleConfig['active'] = true;
                    }

                    $c = json_decode(file_get_contents($moduleChangelogPath), true);
                    $moduleConfig['changelog'] = $c;
                    $moduleConfig['path'] = $modulePath;

                    if (array_has($moduleConfig, 'module')) {
                        $modules[data_get($moduleConfig, 'module')] = collect($moduleConfig);
                    }
                }
            }
        }

        $this->modules = $modules;
    }

    /**
     * Check Module in Database.
     * @param $module
     * @param $key
     * @return
     */
    public function checkInDatabase($module, $key)
    {
        $check = check_table_db(config('module.database.table'))? $this->check($module['name']) ?? $this->insertModule($module) : null;
        $module['id'] = $check ? $check->id : $key + 1;
        $module['str'] = strtolower ( $module['name']) ;
        $module['check'] = $check ? true : false;
        $module['slug'] = $check ? $check ->slug : false;
        $module['icon'] = $check ? $check ->icon : false;
        $module['active'] = $check ? $check ->active : false;

        return $module;
    }

    /**
     * Insert Module to Database.
     *
     * @param $insert
     * @return ModuleModel
     */
    public function insertModule($insert){

        $module = new ModuleModel();
        $module->name = $insert['name'];
        $module->module = $insert['module'];
        $module->active = $insert['active'];
        $module->save();

        return $module;
    }

}
