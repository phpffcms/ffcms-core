<?php

namespace Ffcms\Core\Arch;

use \Core\App;
use \Core\Exception\NativeException;
use \DebugBar\DataCollector\ConfigCollector;

abstract class Controller extends \Core\Arch\Constructors\Magic {

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
        parent::__destruct();
        // allow use and override after() method
        $this->after();
        // prepare Layout for this controller
        $layoutPath = App::$Alias->currentViewPath . '/layout/' . self::$layout;
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
        $global = App::$Response->buildGlobal();
        App::$Debug->bar->addCollector(new ConfigCollector(['Global Vars' => (array)$global]));
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