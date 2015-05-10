<?php

namespace Ffcms\Core;

use Ffcms\Core\Helper\File;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\I18n\Translate;
use Ffcms\Core\Network\Request;
use Ffcms\Core\Network\Response;
use Ffcms\Core\Arch\View;
use Ffcms\Core\Notify\Message;
use Ffcms\Core\Exception\EmptyException;

/**
 * Class App - entry point for applications
 * @package Ffcms\Core
 */
class App {

    /**
     * @var \Ffcms\Core\Network\Request
     */
    public static $Request;

    /**
     * @var \Ffcms\Core\Property
     */
    public static $Property;

    /**
     * @var \Ffcms\Core\Network\Response
     */
    public static $Response;

    /**
     * @var \Ffcms\Core\Alias
     */
    public static $Alias;

    /**
     * @var \Ffcms\Core\Arch\View
     */
    public static $View;

    /**
     * @var \Ffcms\Core\Debug
     */
    public static $Debug;

    /**
     * @var \Ffcms\Core\Helper\Security
     */
    public static $Security;

    /**
     * @var \Ffcms\Core\I18n\Translate
     */
    public static $Translate;

    /**
     * @var \Ffcms\Core\Notify\Message
     */
    public static $Message;


    /**
     * Load entry point for another logic
     */
    public static function build()
    {
        // init dynamic classes and make access point
        self::$Property = new Property();
        self::$Debug = new Debug();
        self::$Request = Request::createFromGlobals();
        self::$Security = new Security();
        self::$Response = new Response();
        self::$View = new View();
        self::$Translate = new Translate();
        self::$Message = new Message();
        self::$Alias = new Alias();
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
                        throw new \Exception('Method ' . $actionName . '() not founded in ' . $cname . ' in file {root}' . $controller_path);
                    }
                    unset($load);
                } else {
                    throw new \Exception('Namespace\\Class - ' . $cname . ' not founded in {root}' . $controller_path);
                }
            } else {
                throw new \Exception('Controller not founded: {root}' . $controller_path);
            }
        } catch (\Exception $e) {
            self::$Debug->addException($e);
            new EmptyException('Unable to find this URL');
        }
    }

}