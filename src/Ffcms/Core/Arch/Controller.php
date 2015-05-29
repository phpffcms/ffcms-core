<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\File;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Traits\DynamicGlobal;
use Ffcms\Core\Template\Variables;

class Controller
{

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

    public function before()
    {
    }

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
            if (File::exist($layoutPath)) {
                $this->build($layoutPath);
            } else {
                throw new NativeException('Layout not founded: {root}' . String::replace(root, '', $layoutPath));
            }
        } catch (NativeException $e) {
            $e->display();
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

        // pass global data to config viewer
        if (App::$Debug !== null) {
            App::$Debug->bar->getCollector('config')->setData(['Global Vars' => Variables::instance()->getGlobalsArray()]);
        }

        ob_start();
        include_once($layout);
        $content = ob_get_contents();
        ob_end_clean();

        // add debug bar
        if (App::$Debug !== null) {
            $content = str_replace(
                ['</body>', '</head>'],
                [App::$Debug->renderOut() . '</body>', App::$Debug->renderHead() . '</head>'],
                $content);
        }

        App::$Response->setContent($content);
        App::$Response->send();
    }

    public function after()
    {
    }

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