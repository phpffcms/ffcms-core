<?php

namespace Ffcms\Core\Arch;

use \Core\App;
use \Core\Exception\NativeException;

abstract class Controller {

    /**
     * @var string $layout
     */
    public static $layout = 'main.php';

    /**
     * @var string $response
     */
    public $response;

    public function __construct()
    {
        $this->before();
    }

    public function before() {}

    /**
     * Compile output
     */
    public final function __destruct()
    {
        $this->after();
        $layoutPath = App::$Data->viewPath . '/layout/' . self::$layout;
        try {
            if (file_exists($layoutPath) && is_readable($layoutPath)) {
                $this->build($layoutPath);
            } else {
                throw new \Exception('Layout not founded: {root}' . str_replace(root, '', $layoutPath));
            }
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new NativeException($e);
        }
    }

    /**
     * Build variables and display output html
     * @param string $layout
     */
    protected final function build($layout)
    {
        $body = $this->response;
        $global = new \stdClass();
        if(is_array(App::$Response->getGlobal())) {
            foreach(App::$Response->getGlobal() as $var => $value) {
                $global->{$var} = $value;
            }
        }
        App::$Debug->bar->getCollector('messages')->info("============== Template global variables ==============");
        App::$Debug->bar->getCollector('messages')->info(sizeof((array)$global) > 0 ? $global : 'empty');
        @include_once($layout);
    }

    public function after() {}

    /**
     * Set single global variable
     * @param $var
     * @param $value
     */
    public function setGlobalVar($var, $value)
    {
        App::$Response->setGlobal($var, $value);
    }

    /**
     * Set global variables as array key=>value
     * @param $array
     */
    public function setGlobalVarArray($array)
    {
        App::$Response->setGlobalArray($array);
    }
}