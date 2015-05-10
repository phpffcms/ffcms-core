<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Traits\DynamicGlobal;
use Ffcms\Core\Template\Variables;

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
        if (Variables::instance()->getError() !== null) {
            $body = Variables::instance()->getError();
        }
        $global = Variables::instance()->getGlobalsObject();
        App::$Debug->bar->getCollector('config')->setData(['Global Vars' => Variables::instance()->getGlobalsArray()]);
        //App::$Response->send();
        ob_start();
        include_once($layout);
        $content = ob_get_contents();
        ob_end_clean();
        App::$Response->setContent($content);
        App::$Response->send();
    }

    public function after() {}

    /**
     * Set single global variable
     * @param string $var
     * @param string $value
     * @param bool $html
     */
    public function setGlobalVar($var, $value, $html = false)
    {
        Variables::instance()->setGlobal($var, $value, $html);
    }

    /**
     * Set global variables as array key=>value
     * @param $array
     */
    public function setGlobalVarArray(array $array)
    {
        Variables::instance()->setGlobalArray($array);
    }

}