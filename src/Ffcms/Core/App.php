<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Exception\JsonException;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\FileSystem\File;
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
 * Class App - entry point for applications
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

    private $services;


    /**
     * Prepare entry-point services
     * @param array|null $services
     * @throws NativeException
     */
    public static function init(array $services = null)
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

        // check if debug is enabled and available for current session
        if (isset($services['Debug']) && $services['Debug'] === true && Debug::isEnabled() === true) {
            self::$Debug = new Debug();
        }

        $objects = App::$Properties->getAll('object');
        // pass dynamic initialization
        self::dynamicServicePrepare($services, $objects);
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
        try {
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
                $cPath = Str::replace('\\', '/', $cName) . '.php';

                // try to load controller
                if (File::exist($cPath)) {
                    File::inc($cPath, false, true);
                } else {
                    throw new NotFoundException('Controller not founded: {root}' . $cPath);
                }
                // try to initialize class object
                if (class_exists($cName)) {
                    $callClass = new $cName;
                } else {
                    throw new NotFoundException('App is not founded: "' . $cName . '. Pathway: {root}' . $cPath);
                }
            }

            // try to call method of founded callback class
            if (method_exists($callClass, $callMethod)) {
                $response = null;
                // param "id" is passed
                if (!Str::likeEmpty(self::$Request->getID())) {
                    // param "add" is passed
                    if (!Str::likeEmpty(self::$Request->getAdd())) {
                        $response = $callClass->$callMethod(self::$Request->getID(), self::$Request->getAdd());
                    } else {
                        $response = $callClass->$callMethod(self::$Request->getID());
                    }
                } else {
                    // no passed params is founded
                    $response = $callClass->$callMethod();
                }

                // work with returned response data
                if ($response !== null && Obj::isString($response) && method_exists($callClass, 'setResponse')) {
                    $callClass->setResponse($response);
                }
            } else {
                throw new NotFoundException('Method "' . $callMethod . '()" not founded in "' . get_class($callClass) . '"');
            }
        } catch (NotFoundException $e) {
            $e->display();
        } catch (ForbiddenException $e) {
            $e->display();
        } catch (SyntaxException $e) {
            $e->display();
        } catch (JsonException $e) {
            $e->display();
        } catch (NativeException $e) {
            $e->display();
        } catch (\Exception $e) { // catch all other exceptions
            (new NativeException($e->getMessage()))->display();
        }
    }

}