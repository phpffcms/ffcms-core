<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\ForbiddenException;
use Ffcms\Core\Exception\JsonException;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\NotFoundException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\Helper\Type\Object;
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

    /**
     * Load entry point for another logic
     */
    public static function build()
    {
        // init dynamic classes and make access point
        self::$Memory = MemoryObject::instance();
        self::$Properties = new Properties();
        self::$Request = Request::createFromGlobals();
        self::$Security = new Security();
        self::$Response = new Response();
        self::$View = new View();
        self::$Translate = new Translate();
        self::$Alias = new Alias();

        // init debug
        if (Debug::isEnabled()) {
            self::$Debug = new Debug();
        }

        // build some configurable objects
        self::buildExtendObject();
    }

    /**
     * Build object configuration from config
     * @throws NativeException
     */
    protected static function buildExtendObject()
    {
        $objectConfig = self::$Properties->getAll('object');
        if ($objectConfig === false || !Object::isArray($objectConfig)) {
            throw new NativeException('Object configurations is not loaded: /Private/Config/Object.php');
        }

        foreach ($objectConfig as $object => $instance) {
            if (property_exists('Ffcms\\Core\\App', $object)) {
                self::${$object} = $instance();
            }
        }

        if (self::$Debug !== null) {
            self::$Database->getConnection()->enableQueryLog();
        }
    }

    /**
     * Display content after build
     * @throws \DebugBar\DebugBarException
     */
    public static function display()
    {
        try {
            $controller_path = '/Apps/Controller/' . env_name . '/' . self::$Request->getController() . '.php';
            if (File::exist(root . $controller_path)) {
                include_once(root . $controller_path);
                $cname = 'Apps\\Controller\\' . env_name . '\\' . self::$Request->getController();
                if (class_exists($cname)) {
                    $load = new $cname;
                    $actionName = 'action' . ucfirst(self::$Request->getAction());
                    if (method_exists($cname, $actionName)) {
                        if (self::$Request->getID() !== null) {
                            if (self::$Request->getAdd() !== null) {
                                $load->$actionName(self::$Request->getID(), self::$Request->getAdd());
                            } else {
                                $load->$actionName(self::$Request->getID());
                            }
                        } else {
                            $load->$actionName();
                        }
                    } else {
                        throw new NotFoundException('Method "' . $actionName . '()" not founded in "' . $cname . '" in file {root}' . $controller_path);
                    }
                    unset($load);
                } else {
                    throw new NotFoundException('Namespace\\Class - ' . $cname . ' not founded in {root}' . $controller_path);
                }
            } else {
                throw new NotFoundException('Controller not founded: {root}' . $controller_path);
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
        } catch(\Exception $e) { // catch all other exceptions
            (new NativeException($e->getMessage()))->display();
        }
    }

}