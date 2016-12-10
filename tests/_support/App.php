<?php

namespace Ffcms\Core;

use Ffcms\Core\Arch\View;
use Ffcms\Core\Cache\MemoryObject;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\I18n\Translate;
use Ffcms\Core\Managers\CronManager;
use Ffcms\Core\Managers\EventManager;
use Ffcms\Core\Network\Request;
use Ffcms\Core\Network\Response;

/**
 * Class App. This class provide fake main entry-point object (App::$Obj) with faked static variables.
 * @package Ffcms\Core
 */
class App extends \Codeception\Module
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


    private static $instance;

    private $services;
    private $loader;

    public function __construct($services = [], $loader = null)
    {
        $this->services = $services;
        $this->loader = $loader;

        $this->init();
    }

    private function init()
    {
        // initialize memory and properties controllers
        self::$Memory = MemoryObject::instance();
        /**self::$Properties = new Properties();
        self::$Request = Request::create('/en/', 'GET'); // make empty fake request instance object
        // initialize response, securty translate and other workers
        self::$Security = new Security();
        self::$Response = new Response();
        self::$View = new View();
        self::$Translate = new Translate();
        self::$Alias = new Alias();
        self::$Event = new EventManager();
        self::$Cron = new CronManager();*/
    }

    /**
     * Make fake factory method for obj building.
     * @param array $services
     * @param object|null $loader
     * @return App
     */
    public static function factory($services = [], $loader = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($services, $loader);
        }
        return self::$instance;
    }
}