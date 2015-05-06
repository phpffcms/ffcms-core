<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Traits\DynamicGlobal;

class Controller {

    use DynamicGlobal;

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
    public function __destruct()
    {
        //parent::__destruct();
        // allow use and override after() method
        $this->after();
        // prepare Layout for this controller
        $layoutPath = App::$Alias->currentViewPath . '/layout/' . self::$layout;
        try {
            if (is_readable($layoutPath)) {
                $this->build($layoutPath);
            } else {
                throw new \Exception('Layout not founded: {root}' . String::replace(root, '', $layoutPath));
            }
        } catch(\Exception $e) {
            App::$Debug->addException($e);
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
        if (App::$Response->errorString !== null) {
            $body = App::$Response->errorString;
        }
        $global = App::$Response->buildGlobal();
        App::$Debug->bar->getCollector('config')->setData(['Global Vars' => (array)App::$Response->getGlobals()]);
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