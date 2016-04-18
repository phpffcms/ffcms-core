<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Exception\JsonException;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Event\EventManager;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\I18n\Translate;
use Ffcms\Core\Network\Request;
use Ffcms\Core\Network\Response;
use Ffcms\Core\Arch\View;
use Ffcms\Core\Debug\Manager as Debug;
use Ffcms\Core\Cache\MemoryObject;

/**
 * Class App. Provide later static callbacks as entry point from any places of ffcms.
 * @package Ffcms\Core
 */
class App
{

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

    /** @var \Ffcms\Core\Interfaces\iUser */
    public static $User;

    /** @var \Symfony\Component\HttpFoundation\Session\Session */
    public static $Session;

    /** @var \Illuminate\Database\Capsule\Manager */
    public static $Database;

    /** @var \Ffcms\Core\Cache\MemoryObject */
    public static $Memory;

    /** @var \Swift_Mailer */
    public static $Mailer;

    /** @var \Ffcms\Core\Interfaces\iCaptcha */
    public static $Captcha;

    /** @var \BasePhpFastCache */
    public static $Cache;

    /** @var EventManager */
    public static $Event;

    /**
     * Prepare entry-point services
     * @param array|null $services
     * @param bool|object $loader
     * @throws NativeException
     */
    public static function init(array $services = null, $loader = false)
    {
        // initialize default services - used in all apps type
        self::$Memory = MemoryObject::instance();
        self::$Properties = new Properties();
        self::$Request = Request::createFromGlobals();
        self::$Security = new Security();
        self::$Response = new Response();
        self::$View = new View();
        self::$Translate = new Translate();
        self::$Alias = new Alias();
        self::$Event = new EventManager();


        // check if debug is enabled and available for current session
        if (isset($services['Debug']) && $services['Debug'] === true && Debug::isEnabled() === true) {
            self::$Debug = new Debug();
        }

        $objects = App::$Properties->getAll('object');
        // pass dynamic initialization
        self::dynamicServicePrepare($services, $objects);

        // initialize autoload, pass composer loader and auto-boot of static boot() methods in controllers
        self::$Event->makeBoot($loader);
    }

    /**
     * Prepare dynamic services from object anonymous functions
     * @param array|null $services
     * @param null $objects
     * @throws NativeException
     */
    private static function dynamicServicePrepare(array $services = null, $objects = null)
    {
        // check if object configuration is passed
        if (!Obj::isArray($objects)) {
            throw new NativeException('Object configurations is not loaded: /Private/Config/Object.php');
        }

        // each all objects as service_name => service_instance()
        foreach ($objects as $name => $instance) {
            // check if definition of object is exist and services list contains it or is null to auto build
            if (property_exists(get_called_class(), $name) && $instance instanceof \Closure && (isset($services[$name]) || $services === null)) {
                if ($services[$name] === true || $services === null) { // initialize from configs
                    self::${$name} = $instance();
                } elseif (is_callable($services[$name])) { // raw initialization from App::run()
                    self::${$name} = $services[$name]();
                }
            }
        }
    }

    /**
     * Run applications and display output
     * @throws \DebugBar\DebugBarException
     */
    public static function run()
    {
        $html = null;
        // lets try to get html full content to page render
        try {
            /** @var \Ffcms\Core\Arch\Controller $callClass */
            $callClass = null;
            $callMethod = 'action' . self::$Request->getAction();

            // founded callback injection alias
            if (self::$Request->getCallbackAlias() !== false) {
                $cName = self::$Request->getCallbackAlias();
                if (class_exists($cName)) {
                    $callClass = new $cName;
                } else {
                    throw new NotFoundException('Callback alias of class "' . App::$Security->strip_tags($cName) . '" is not founded');
                }
            } else { // typical parsing of native apps
                $cName = '\Apps\Controller\\' . env_name . '\\' . self::$Request->getController();

                // try to initialize class object
                if (class_exists($cName)) {
                    $callClass = new $cName;
                } else {
                    throw new NotFoundException('Application can not be runned. Initialized class not founded: ' . App::$Security->strip_tags($cName));
                }
            }

            // try to call method of founded callback class
            if (method_exists($callClass, $callMethod)) {
                $actionQuery = [];
                // prepare action params for callback
                if (!Str::likeEmpty(self::$Request->getID())) {
                    $actionQuery[] = self::$Request->getID();
                    if (!Str::likeEmpty(self::$Request->getAdd())) {
                        $actionQuery[] = self::$Request->getAdd();
                    }
                }

                // get controller method arguments count
                $reflection = new \ReflectionMethod($callClass, $callMethod);
                $argumentCount = 0;
                foreach ($reflection->getParameters() as $arg) {
                    if (!$arg->isOptional()) {
                        $argumentCount++;
                    }
                }

                // check method arguments count and current request count to prevent warnings
                if (count($actionQuery) < $argumentCount) {
                    throw new NotFoundException(__('Arguments for method %method% is not enough. Expected: %required%, got: %current%.', [
                        'method' => $callMethod,
                        'required' => $argumentCount,
                        'current' => count($actionQuery)
                    ]));
                }

                // make callback call to action in controller and get response
                $actionResponse = call_user_func_array([$callClass, $callMethod], $actionQuery);

                if ($actionResponse !== null && !Str::likeEmpty($actionResponse)) {
                    // set response to controller property object
                    $callClass->setResponse($actionResponse);
                }

                // get full compiled response
                $html = $callClass->getOutput();
            } else {
                throw new NotFoundException('Method "' . App::$Security->strip_tags($callMethod) . '()" not founded in "' . get_class($callClass) . '"');
            }
        } catch (NotFoundException $e) { // catch exceptions and set output
            $html = $e->display();
        } catch (ForbiddenException $e) {
            $html = $e->display();
        } catch (SyntaxException $e) {
            $html = $e->display();
        } catch (JsonException $e) {
            $html = $e->display();
        } catch (NativeException $e) {
            $html = $e->display();
        } catch (\Exception $e) { // catch all other exceptions
            $html = (new NativeException($e->getMessage()))->display();
        }

        // set full rendered content to response builder
        self::$Response->setContent($html);
        // echo full response to user via http foundation
        self::$Response->send();
    }

}