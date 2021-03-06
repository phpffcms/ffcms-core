<?php

namespace Ffcms\Core;


use Ffcms\Core\Cache\MemoryObject;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\I18n\Translate;
use Ffcms\Core\Network\Request;
use Ffcms\Core\Network\Response;

use Codeception\Module;

/**
 * Class App. This class provide fake main entry-point object (App::$Obj) with faked static variables.
 * @package Ffcms\Core
 */
class App extends Module
{
    public static $Memory;
    public static $Properties;
    public static $Request;
    public static $Debug;
    public static $Security;
    public static $Response;
    public static $View;
    public static $Translate;
    public static $Alias;
    public static $Event;
    public static $Cron;
    public static $Session;

    private static $instance;

    private $services;
    private $loader;

    /**
     * Initialize app construction.
     * @throws Exception\NativeException
     */
    public static function init()
    {
        // define global vars
        $root = realpath(__DIR__ . '/../../../../../');
        // define root ;)
        if (!defined('root')) {
            define('root', $root);
        }
        // define environment
        if (!defined('env_name')) {
            define('env_name', 'test');
        }
        // include bootstrap autoloader
        include $root . '/Loader/Autoload.php';

        // initialize memory and properties controllers, will work fine in test environment
        self::$Memory = MemoryObject::instance();
        self::$Properties = new Properties();
        // emulate fake http GET request to /en/ page
        self::$Request = Request::create('/en/', 'GET');
        // emulate empty 200-header response
        self::$Response = new Response();
        // load i18n translation engine, will work fine in test env
        self::$Translate = new Translate();
        // init security for string escaping and html cleanup functions
        self::$Security = new Security();
        self::$Session = new FakeSession();
    }

    /**
     * Make fake factory method for obj building. Just singleton logic, fake factory
     * @return App
     * @throws Exception\NativeException
     */
    public static function factory()
    {
        if (!self::$instance) {
            self::$instance = self::init();
        }
        return self::$instance;
    }
}

class FakeSession
{
    public function getFlashBag()
    {
        return $this;
    }

    public function set($key, $val)
    {
        return true;
    }

    public function get($key, $def = null)
    {
        return $def;
    }

    public function add($key, $val)
    {
        return true;
    }
}