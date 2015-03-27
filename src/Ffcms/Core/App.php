<?php

namespace Ffcms\Core;

use Ffcms\Core\Network\Request;
use Ffcms\Core\Exception\RequestException;
use Ffcms\Core\Network\Response;

class App {

    /**
     * @var Request
     */
    public static $Request;

    /**
     * @var Property
     */
    public static $Property;

    /**
     * @var Response
     */
    public static $Response;

    /**
     * @var Data
     */
    public static $Data;

    /**
     * @var View
     */
    public static $View;



    public static function build()
    {
        self::$Request = new Request();
        self::$Property = new Property();
        self::$Response = new Response();
        self::$Data = new Data();
        self::$View = new View();
    }

    public static function display()
    {
        $controller = root . '/controller/' . self::$Request->getController() . ".php";
        $exception = false;
        if(file_exists($controller) && is_readable($controller)) {
            include_once($controller);
            if(class_exists('controller\\' . self::$Request->getController())) {
                $cname = 'controller\\' . self::$Request->getController();
                $load = @new $cname;
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
                    $exception = true;
                }
            } else {
                $exception = true;
            }
        } else {
            $exception = true;
        }
        if($exception) {
            new RequestException('Page not founded');
        }
    }

}