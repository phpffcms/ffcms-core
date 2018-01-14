<?php

namespace Ffcms\Core;

use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Arch\View;
use Ffcms\Core\Cache\MemoryObject;
use Ffcms\Core\Debug\DebugMeasure;
use Ffcms\Core\Debug\Manager as Debug;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Exception\TemplateException;
use Ffcms\Core\Helper\Mailer;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\I18n\Translate;
use Ffcms\Core\Managers\BootManager;
use Ffcms\Core\Managers\CronManager;
use Ffcms\Core\Managers\EventManager;
use Ffcms\Core\Network\Request;
use Ffcms\Core\Network\Response;
use Ffcms\Core\Traits\ClassTools;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class App. Provide later static callbacks as entry point from any places of ffcms.
 * @package Ffcms\Core
 */
class App
{
    use DebugMeasure, ClassTools;

    /** @var \Ffcms\Core\Network\Request */
    public static $Request;

    /** @var \Ffcms\Core\Properties */
    public static $Properties;

    /** @var \Ffcms\Core\Network\Response */
    public static $Response;

    /** @var \Ffcms\Core\Alias */
    public static $Alias;

    /** @var \Ffcms\Core\Arch\View */
    public static $View;

    /** @var \Ffcms\Core\Debug\Manager|null */
    public static $Debug;

    /** @var \Ffcms\Core\Helper\Security */
    public static $Security;

    /** @var \Ffcms\Core\I18n\Translate */
    public static $Translate;

    /** @var \Ffcms\Core\Interfaces\iUser|\Apps\ActiveRecord\User */
    public static $User;

    /** @var \Symfony\Component\HttpFoundation\Session\Session */
    public static $Session;

    /** @var \Illuminate\Database\Capsule\Manager */
    public static $Database;

    /** @var \Ffcms\Core\Cache\MemoryObject */
    public static $Memory;

    /** @var Mailer */
    public static $Mailer;

    /** @var \Ffcms\Core\Interfaces\iCaptcha */
    public static $Captcha;

    /** @var FilesystemAdapter */
    public static $Cache;

    /** @var EventManager */
    public static $Event;

    /** @var CronManager */
    public static $Cron;

    private $_services;
    private $_loader;

    /**
     * App constructor. Build App entry-point instance
     * @param array|null $services
     * @param bool $loader
     * @throws \Ffcms\Core\Exception\NativeException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $services = null, $loader = false)
    {
        // pass initialization data inside
        $this->_services = $services;
        $this->_loader = $loader;
        // initialize service links
        $this->loadNativeServices();
        $this->loadDynamicServices();
        // Initialize boot manager. This manager allow to auto-execute 'static boot()' methods in apps and widgets
        $bootManager = new BootManager($this->_loader);
        $bootManager->run();
    }

    /**
     * Factory method builder for app entry point
     * @param array|null $services
     * @param bool $loader
     * @return App
     * @throws \InvalidArgumentException
     */
    public static function factory(array $services = null, $loader = false): self
    {
        return new self($services, $loader);
    }

    /**
     * Prepare native static symbolic links for app services
     * @throws \InvalidArgumentException
     */
    private function loadNativeServices(): void
    {
        // initialize memory and properties controllers
        self::$Memory = MemoryObject::instance();
        self::$Properties = new Properties();
        // initialize debugger
        if (isset($this->_services['Debug']) && $this->_services['Debug'] === true && Debug::isEnabled()) {
            self::$Debug = new Debug();
            $this->startMeasure(__METHOD__);
        }
        // prepare request data
        self::$Request = Request::createFromGlobals();
        // initialize response, securty translate and other workers
        self::$Security = new Security();
        self::$Response = new Response();
        self::$View = new View();
        self::$Translate = new Translate();
        self::$Alias = new Alias();
        self::$Event = new EventManager();
        self::$Cron = new CronManager();
        // stop debug timeline
        $this->stopMeasure(__METHOD__);
    }

    /**
     * Prepare dynamic static links from object configurations as anonymous functions
     * @throws NativeException
     */
    private function loadDynamicServices(): void
    {
        $this->startMeasure(__METHOD__);

        /** @var array $objects */
        $objects = App::$Properties->getAll('object');
        if (!Any::isArray($objects)) {
            throw new NativeException('Object configurations is not loaded: /Private/Config/Object.php');
        }

        // each all objects as service_name => service_instance()
        foreach ($objects as $name => $instance) {
            // check if definition of object is exist and services list contains it or is null to auto build
            if (property_exists(get_called_class(), $name) && $instance instanceof \Closure && (isset($this->_services[$name]) || $this->_services === null)) {
                if ($this->_services[$name] === true || $this->_services === null) { // initialize from configs
                    self::${$name} = $instance();
                } elseif (is_callable($this->_services[$name])) { // raw initialization from App::run()
                    self::${$name} = $this->_services[$name]();
                }
            } elseif (Str::startsWith('_', $name)) { // just anonymous callback without entry-point
                @call_user_func($instance);
            }
        }

        $this->stopMeasure(__METHOD__);
    }

    /**
     * Run applications and display output. Main entry point of system.
     * @return void
     */
    public function run(): void
    {
        try {
            /** @var \Ffcms\Core\Arch\Controller $callClass */
            $callClass = $this->getCallbackClass();
            $callMethod = 'action' . self::$Request->getAction();
            $arguments = $this->getArguments();

            // check if callback method (action) is exist in class object
            if (!method_exists($callClass, $callMethod)) {
                throw new NotFoundException('Method "' . App::$Security->strip_tags($callMethod) . '()" not founded in "' . get_class($callClass) . '"');
            }

            // check if method arguments counts equals passed count
            $requiredArgCount = $this->getMethodRequiredArgCount($callClass, $callMethod);

            // compare method arg count with passed
            if (count($arguments) < $requiredArgCount) {
                throw new NotFoundException(__('Arguments for method %method% is not enough. Expected: %required%, got: %current%.', [
                    'method' => $callMethod,
                    'required' => $requiredArgCount,
                    'current' => count($arguments)
                ]));
            }

            $this->startMeasure(get_class($callClass) . '::' . $callMethod);
            // make callback call to action in controller and get response
            $actionResponse = call_user_func_array([$callClass, $callMethod], $arguments);
            $this->stopMeasure(get_class($callClass) . '::' . $callMethod);

            // set response to controller attribute
            if (!Str::likeEmpty($actionResponse)) {
                $callClass->setOutput($actionResponse);
            }

            // build full compiled output html data with default layout and widgets
            $html = $callClass->buildOutput();
        } catch (\Exception $e) {
            // check if exception is system-based throw
            if ($e instanceof TemplateException) {
                $html = $e->display();
            } else { // or hook exception to system based :)))
                if (App::$Debug) {
                    $msg = $e->getMessage() . $e->getTraceAsString();
                    $html = (new NativeException($msg))->display();
                } else {
                    $html = (new NativeException($e->getMessage()))->display();
                }
            }
        }

        // set full rendered content to response builder
        self::$Response->setContent($html);
        // echo full response to user via symfony http foundation
        self::$Response->send();
    }

    /**
     * Get callback class instance
     * @return Controller
     * @throws NotFoundException
     */
    private function getCallbackClass()
    {
        // define callback class namespace/name full path
        $cName = (self::$Request->getCallbackAlias() ?? '\Apps\Controller\\' . env_name . '\\' . self::$Request->getController());
        if (!class_exists($cName)) {
            throw new NotFoundException('Callback class not found: ' . App::$Security->strip_tags($cName));
        }

        return new $cName;
    }

    /**
     * Get method arguments from request
     * @return array
     */
    private function getArguments(): array
    {
        $args = [];
        if (!Str::likeEmpty(self::$Request->getID())) {
            $args[] = self::$Request->getID();
            if (!Str::likeEmpty(self::$Request->getAdd())) {
                $args[] = self::$Request->getAdd();
            }
        }

        return $args;
    }
}
