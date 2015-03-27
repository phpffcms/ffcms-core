<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;

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

    public final function __construct()
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
        if(file_exists($layoutPath) && is_readable($layoutPath)) {
            $this->build($layoutPath);
        }
    }

    protected final function build($layout)
    {
        $body = $this->response;
        $global = new \stdClass();
        if(is_array(App::$View->getGlobal())) {
            foreach(App::$View->getGlobal() as $var => $value) {
                $global->{$var} = $value;
            }
        }
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