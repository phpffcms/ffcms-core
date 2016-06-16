<?php

namespace Ffcms\Core\Managers;


use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Helper\FileSystem\Directory;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class BootManager. Manage auto executed boot methods in widgets and applications.
 * @package Ffcms\Core\Managers
 */
class BootManager
{
    CONST CACHE_TREE_TIME = 120;

    private $loader;
    private $appRoots = [];
    private $widgetRoots = [];

    private $objects = [];

    /**
     * BootManager constructor. Pass composer loader inside
     * @param bool|object $loader
     */
    public function __construct($loader = false)
    {
        // pass loader inside
        $this->loader = $loader;
        if ($this->loader !== false) {
            $this->parseComposerLoader();
        }

        // check if cache is enabled
        if (App::$Cache !== null) {
            // try to get bootable class map from cache, or initialize parsing
            if (App::$Cache->get('boot.' . env_name . '.class.map') !== null) {
                $this->objects = App::$Cache->get('boot.' . env_name . '.class.map');
            } else {
                $this->compileBootableClasses();
                App::$Cache->set('boot.' . env_name . '.class.map', $this->objects, static::CACHE_TREE_TIME);
            }
        }
    }

    /**
     * Find app's and widgets root directories over composer psr loader
     */
    private function parseComposerLoader()
    {
        // get composer autoload map
        $map = $this->loader->getPrefixes();
        if (Obj::isArray($map)) {
            // get all available apps root dirs by psr loader for apps
            if (array_key_exists('Apps\\', $map)) {
                foreach ($map['Apps\\'] as $appPath) {
                    $this->appRoots[] = $appPath;
                }
            }

            // get Widgets map
            if (array_key_exists('Widgets\\', $map)) {
                // get all available root dirs by psr loader for widgets
                foreach ($map['Widgets\\'] as $widgetPath) {
                    $this->widgetRoots[] = $widgetPath;
                }
            }
        }

        // set default root path if not found anything else
        if (count($this->appRoots) < 1) {
            $this->appRoots = [root];
        }

        if (count($this->widgetRoots) < 1) {
            $this->widgetRoots = [root];
        }
    }

    /**
     * Find all bootatble instances and set it to object map
     */
    public function compileBootableClasses()
    {
        // list app root's
        foreach ($this->appRoots as $app) {
            $app .= '/Apps/Controller/' . env_name;
            $files = File::listFiles($app, ['.php'], true);
            foreach ($files as $file) {
                // define full class name with namespace
                $class = 'Apps\Controller\\' . env_name . '\\' . Str::cleanExtension($file);
                // check if class exists (must be loaded over autoloader), boot method exist and this is controller instanceof
                if (class_exists($class) && method_exists($class, 'boot') && is_a($class, 'Ffcms\Core\Arch\Controller', true)) {
                    $this->objects[] = $class;
                }
            }
        }

        // list widget root's
        foreach ($this->widgetRoots as $widget) {
            $widget .= '/Widgets/' . env_name;
            // widgets are packed in directory, classname should be the same with root directory name
            $dirs = Directory::scan($widget, GLOB_ONLYDIR, true);
            if (!Obj::isArray($dirs)) {
                continue;
            }
            foreach ($dirs as $instance) {
                $class = 'Widgets\\' . env_name . '\\' . $instance . '\\' . $instance;
                if (class_exists($class) && method_exists($class, 'boot') && is_a($class, 'Ffcms\Core\Arch\Widget', true)) {
                    $this->objects[] = $class;
                }
            }
        }
    }

    /**
     * Call bootable methods in apps and widgets
     * @return bool
     */
    public function run()
    {
        if (!Obj::isArray($this->objects)) {
            return false;
        }

        foreach ($this->objects as $class) {
            forward_static_call([$class, 'boot']);
        }
        return true;
    }
}