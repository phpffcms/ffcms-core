<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Traits\DynamicGlobal;
use Ffcms\Core\Template\Variables;

/**
 * Class Controller. Classic carcase of controller in MVC architecture.
 * @package Ffcms\Core\Arch
 */
class Controller
{

    use DynamicGlobal;

    /**
     * @var string $layout
     */
    public $layout = 'main';

    /**
     * @var string $response
     */
    protected $response;

    public function __construct()
    {
        $this->before();
    }

    public function before() {}
    
    /** Global bootable method */
    public static function boot() {}

    /**
     * Build variables and display output html
     */
    public function getOutput()
    {
        $this->after();

        // if layout is not required and this is just standalone app
        if ($this->layout === null) {
            $content = $this->response;
        } else {
            $layoutPath = App::$Alias->currentViewPath . '/layout/' . $this->layout . '.php';
            if (!File::exist($layoutPath)) {
                throw new NativeException('Layout not founded: ' . $layoutPath);
            }

            $body = $this->response;
            // pass global data to config viewer
            if (App::$Debug !== null) {
                App::$Debug->bar->getCollector('config')->setData(['Global Vars' => Variables::instance()->getGlobalsArray()]);
            }

            // cleanup buffer from random shits after exception throw'd
            ob_clean();
            // start buffering to render layout
            ob_start();
            include($layoutPath);
            $content = ob_get_clean(); // read buffer content & stop buffering

            // set custom css library's not included on static call
            $cssIncludeCode = App::$View->showCodeLink('css');
            if (!Str::likeEmpty($cssIncludeCode)) {
                $content = Str::replace('</head>', $cssIncludeCode . '</head>', $content);
            }

            // add debug bar
            if (App::$Debug !== null) {
                $content = Str::replace(
                    ['</body>', '</head>'],
                    [App::$Debug->renderOut() . '</body>', App::$Debug->renderHead() . '</head>'],
                    $content);
            }

        }

        return $content;
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

    /**
     * Special method to set response of action execution
     * @param string $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Get response of action rendering
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

}