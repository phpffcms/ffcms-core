<?php

namespace Ffcms\Core;

use \Illuminate\Database\Capsule\Manager as Capsule;

class App {

    /**
     * @var \Core\Network\Request
     */
    public static $Request;

    /**
     * @var \Core\Property
     */
    public static $Property;

    /**
     * @var \Core\Network\Response
     */
    public static $Response;

    /**
     * @var \Core\Alias
     */
    public static $Alias;

    /**
     * @var \Core\Arch\View
     */
    public static $View;

    /**
     * @var \Core\Debug
     */
    public static $Debug;

    /**
     * @var \Core\Helper\Security
     */
    public static $Security;

    /**
     * @var \Core\I18n\Translate
     */
    public static $Translate;

    /**
     * @var \Core\Notify\Message
     */
    public static $Message;

    /**
     * @var \Core\Identify\User
     */
    public static $User;


    /**
     * Load entry point for another logic
     */
    public static function build()
    {
        // init dynamic classes and make access point
        self::$Property = new \Core\Property();
        self::$Debug = new \Core\Debug();
        self::$Request = new \Core\Network\Request();
        self::$Security = new \Core\Helper\Security();
        self::$Response = new \Core\Network\Response();
        self::$View = new \Core\Arch\View();
        self::$Translate = new \Core\I18n\Translate();
        self::$Message = new \Core\Notify\Message();
        self::$Alias = new \Core\Alias();
        self::$User = new \Core\Identify\User();

        // establish database link
        $capsule = new Capsule;
        $capsule->addConnection(self::$Property->get('database'));

        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
    }

    /**
     * Display content after build
     * @throws \DebugBar\DebugBarException
     */
    public static function display()
    {
        try {
            $controller_path = '/controller/' . workground . '/' . self::$Request->getController() . '.php';
            if(file_exists(root . $controller_path) && is_readable(root . $controller_path)) {
                include_once(root . $controller_path);
                $cname = 'Controller\\' . workground . '\\' . self::$Request->getController();
                if(class_exists($cname)) {
                    $load = new $cname;
                    $actionName = 'action' . ucfirst(self::$Request->getAction());
                    if(method_exists($cname, $actionName)) {
                        if(self::$Request->getID() !== null) {
                            if(self::$Request->getAdd() !== null) {
                                @$load->$actionName(self::$Request->getID(), self::$Request->getAdd());
                            } else {
                                @$load->$actionName(self::$Request->getID());
                            }
                        } else {
                            @$load->$actionName();
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
        } catch(\Exception $e) {
            self::$Debug->bar->getCollector('exceptions')->addException($e);
            new \Core\Exception\EmptyException('Unable to find this URL');
        }
    }

}