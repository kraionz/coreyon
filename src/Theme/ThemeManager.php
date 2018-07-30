<?php

namespace Creatyon\Core\Theme;

use Illuminate\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\View\ViewFinderInterface;
use Creatyon\Core\Theme\Theme as ThemeModel;


class ThemeManager implements ThemeContract
{
    /**
     * Theme Root Path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All Theme Information.
     *
     * @var $themes
     */
    protected $themes;

    /**
     * Blade View Finder.
     *
     * @var \Illuminate\View\ViewFinderInterface
     */
    protected $finder;

    /**
     * Application Container.
     *
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Translator.
     *
     * @var \Illuminate\Contracts\Translation\Translator
     */
    protected $lang;

    /**
     * Current Active Theme.
     *
     * @var string|$activeTheme
     */
    private $activeTheme = null;

    /**
     * Theme constructor.
     *
     * @param Container           $app
     * @param ViewFinderInterface $finder
     * @param Translator          $lang
     */
    public function __construct(Container $app, ViewFinderInterface $finder, Translator $lang)
    {
        $this->app = $app;

        $this->finder = $finder;

        $this->lang = $lang;

        $this->basePath = config('theme.theme_path');

        $this->scanThemes();
    }

    /**
     * Check if exists in dataBase.
     *
     * @param $theme
     * @return bool
     */

    public function check($theme)
    {
        $theme = ThemeModel::where('theme', $theme)->first();
        return $theme;
    }

    /**
     * Set current theme.
     *
     * @param string $theme
     *
     * @return void
     */
    public function set($theme)
    {
        if (!$this->has($theme)) {
            throw new ThemeNotFoundException($theme);
        }
        $this->loadTheme($theme);
        $this->activeTheme = $theme;
    }

    /**
     * Check if theme exists.
     *
     * @param string $theme
     *
     * @return bool
     */
    public function has($theme)
    {
        return count($this->themes) && $this->themes->has($theme);
    }

    /**
     * Get particular theme all information.
     *
     * @param string $themeName
     *
     * @return null|boolean
     */
    public function getThemeInfo($themeName)
    {
        return isset($this->themes[$themeName]) ? $this->themes[$themeName] : null;
    }

    /**
     * Returns current theme or particular theme information.
     *
     * @param string $theme
     * @param bool   $collection
     *
     * @return array|null
     */
    public function get($theme = null, $collection = false)
    {
        if (is_null($theme) || !$this->has($theme)) {
            return !$collection ? $this->themes[$this->activeTheme]->all() : $this->themes[$this->activeTheme];
        }

        return !$collection ? $this->themes[$theme]->all() : $this->themes[$theme];
    }

    /**
     * Get current active theme name only or themeinfo collection.
     *
     * @param bool $collection
     *
     * @return null|string
     */
    public function current($collection = false)
    {
        return !$collection ? $this->activeTheme : $this->getThemeInfo($this->activeTheme);
    }

    /**
     * Get all theme information.
     *
     * @return $this->themes
     */
    public function all()
    {
        return $this->themes;
    }

    /**
     * Find asset file for theme asset.
     *
     * @param string    $path
     * @param null|bool $secure
     *
     * @return string
     */
    public function assets($path, $secure = null)
    {
        $splitThemeAndPath = explode(':', $path);

        if (count($splitThemeAndPath) > 1) {
            if (is_null($splitThemeAndPath[0])) {
                return;
            }
            $themeName = $splitThemeAndPath[0];
            $path = $splitThemeAndPath[1];
        } else {
            $themeName = $this->activeTheme;
            $path = $splitThemeAndPath[0];
        }

        $themeInfo = $this->getThemeInfo($themeName);

        if ( config('theme.symlink') ) {
            $themePath = 'themes/' . $themeName . '/';
        } else {
            $themePath = str_replace(public_path() . '/', '', $themeInfo->get('path')) . '/';
        }

        $assetPath = config('theme.folders.assets').'/';
        $fullPath = $themePath.$assetPath.$path;

        if (!file_exists($fullPath) && $themeInfo->has('parent') && !empty($themeInfo->get('parent'))) {
            $themePath = str_replace(public_path().'/', '', $this->getThemeInfo($themeInfo->get('parent'))->get('path') ).'/';
            $fullPath = $themePath.$assetPath.$path;

            return $this->app['url']->asset($fullPath, $secure);
        }

        return $this->app['url']->asset($fullPath, $secure);
    }

    /**
     * Get lang content from current theme.
     *
     * @param string $fallback
     *
     * @return \Illuminate\Contracts\Translation\Translator|string
     */
    public function lang($fallback)
    {
        $splitLang = explode('::', $fallback);

        if (count($splitLang) > 1) {
            if (is_null($splitLang[0])) {
                $fallback = $splitLang[1];
            } else {
                $fallback = $splitLang[0].'::'.$splitLang[1];
            }
        } else {
            $fallback = $this->current().'::'.$splitLang[0];
            if (!$this->lang->has($fallback)) {
                $fallback = $this->getThemeInfo($this->current())->get('parent').'::'.$splitLang[0];
            }
        }

        return trans($fallback);
    }

    /**
     * Scan for all available themes.
     *
     * @return void
     */
    private function scanThemes()
    {
        $themeDirectories = glob($this->basePath.'/*', GLOB_ONLYDIR);
        $themes = collect();
        if(count($themeDirectories)){
            foreach ($themeDirectories as $key => $themePath) {

                $themeConfigPath = $themePath.'/'.config('theme.config.name');
                $themeChangelogPath = $themePath.'/'.config('theme.config.changelog');

                if (file_exists($themeConfigPath)) {

                    $t = json_decode(file_get_contents($themeConfigPath), true);

                    $themeConfig = $t;
                    $themeConfig =  $this->checkInDatabase($themeConfig, $key);

                    $c = json_decode(file_get_contents($themeChangelogPath), true);
                    $themeConfig['changelog'] = $c;
                    $themeConfig['path'] = $themePath;

                    if (array_has($themeConfig, 'theme')) {
                        $themes[data_get($themeConfig, 'theme')] = collect($themeConfig);
                    }
                }
            }
            $this->themes = $themes;
        }
    }

    /**
     * Check Theme in Database.
     * @param $theme
     * @param $key
     * @return
     */
    public function checkInDatabase($theme, $key)
    {
        $check = check_table_db(config('theme.database.table'))? $this->check($theme['name']) ?? $this->insertModule($theme) : null;
        $theme['id'] = $check ? $check->id : $key + 1;
        $theme['check'] = $check ? true : false;
        $theme['slug'] = $check ? $check->slug : false;
        $theme['admin'] = $check ? $check->admin : $theme['admin'];
        $theme['parent'] = $check ? $check->parent : $theme['parent'];
        $theme['active'] = $check ? $check->active : $theme['active'];

        return $theme;
    }


    /**
     * Insert Theme to Database.
     *
     * @param $insert
     * @return ThemeModel
     */

    public function insertModule($insert)
    {
        $theme = new ThemeModel();
        $theme->name = $insert['name'];
        $theme->theme = $insert['theme'];
        $theme->parent = $insert['parent'];
        $theme->admin = $insert['admin'];
        $theme->active = $insert['active'];
        $theme->save();

        return $theme;
    }

    /**
     * Map view map for particular theme.
     *
     * @param string $theme
     *
     * @return void
     */
    private function loadTheme($theme)
    {
        if (is_null($theme)) {
            return;
        }

        $themeInfo = $this->getThemeInfo($theme);

        if (is_null($themeInfo)) {
            return;
        }

        $this->loadTheme($themeInfo->get('parent'));

        $viewPath = $themeInfo->get('path').'/'.config('theme.folders.views');
        $langPath = $themeInfo->get('path').'/'.config('theme.folders.lang');

        $this->finder->prependLocation($themeInfo->get('path'));
        $this->finder->prependLocation($viewPath);
        $this->finder->prependNamespace($themeInfo->get('theme'), $viewPath);
        if ($themeInfo->has('type') && !empty($themeInfo->get('type'))) {
            $this->finder->prependNamespace($themeInfo->get('type'), $viewPath);
        }
        $this->lang->addNamespace($themeInfo->get('theme'), $langPath);
    }
}
