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

    protected $globalVars;

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
        if(is_array(App::$View->getGlobal())) {
            foreach(App::$View->getGlobal() as $var => $value) {
                $global->{$var} = $value;
            }
        }
        App::$Debug->bar->getCollector('messages')->info("============== Template global variables ==============");
        App::$Debug->bar->getCollector('messages')->info(sizeof((array)$global) > 0 ? $global : 'empty');
        @include_once($layout);
    }

    public function after() {}

    public function setGlobalVar($var, $value)
    {
        App::$View->setGlobal($var, $value);
    }

    public function setGlobalVarArray($array)
    {
        App::$View->setGlobalArray($array);
    }
}