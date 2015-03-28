<?php

namespace Ffcms\Core;

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
     * @var \Core\Data
     */
    public static $Data;

    /**
     * @var \Core\View
     */
    public static $View;

    /**
     * @var \Core\Debug
     */
    public static $Debug;


    /**
     * Load entry point for another logic
     */
    public static function build()
    {
        self::$Debug = new \Core\Debug();
        self::$Request = new \Core\Network\Request();
        self::$Property = new \Core\Property();
        self::$Response = new \Core\Network\Response();
        self::$Data = new \Core\Data();
        self::$View = new \Core\View();

    }

    public static function display()
    {
        try {
            $controller_path = '/controller/' . self::$Request->getController() . ".php";
            if(file_exists(root . $controller_path) && is_readable(root . $controller_path)) {
                include_once(root . $controller_path);
                $cname = 'Controller\\' . self::$Request->getController();
                if(class_exists($cname)) {
                    $load = new $cname;
                    $actionName = 'action' . ucfirst(self::$Request->getAction());
                    if(method_exists($cname, $actionName)) {
                        if(self::$Request->getID() != null) {
                            if(self::$Request->getAdd() != null) {
                                @$load->$actionName(self::$Request->getID(), self::$Request->getAdd());
                            } else {
                                @$load->$actionName(self::$Request->getID());
                            }
                        } else {
                            @$load->$actionName();
                        }
                    } else {
                        throw new \Exception("Method " . $actionName . '() not founded in ' . $cname . ' in file {root}' . $controller_path);
                    }
                } else {
                    throw new \Exception("Namespace\\Class - " . $cname . " not founded in {root}" . $controller_path);
                }
            } else {
                throw new \Exception('Controller not founded: {root}' . $controller_path);
            }
        } catch(\Exception $e) {
            self::$Debug->bar->getCollector('exceptions')->addException($e);
            new \Core\Arch\ErrorController('Unable to find this URL');
        }
    }

}