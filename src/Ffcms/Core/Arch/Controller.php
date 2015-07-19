<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\String;
use Ffcms\Core\Traits\DynamicGlobal;
use Ffcms\Core\Template\Variables;

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
        // allow use and override after() method
        $this->after();
        $this->make();
    }

    /**
     * Build variables and display output html
     */
    protected function make()
    {
        // if layout is not required and this is just standalone app
        if ($this->layout === null) {
            $content = $this->response;
        } else {
            $layoutPath = App::$Alias->currentViewPath . '/layout/' . $this->layout . '.php';
            if (!File::exist($layoutPath)) {
                throw new NativeException('Layout not founded: {root}' . String::replace(root, '', $layoutPath));
            }

            $body = $this->response;
            // pass global data to config viewer
            if (App::$Debug !== null) {
                App::$Debug->bar->getCollector('config')->setData(['Global Vars' => Variables::instance()->getGlobalsArray()]);
            }

            ob_start();
            include_once($layoutPath);
            $content = ob_get_contents();
            ob_end_clean();

            // add debug bar
            if (App::$Debug !== null) {
                $content = str_replace(
                    ['</body>', '</head>'],
                    [App::$Debug->renderOut() . '</body>', App::$Debug->renderHead() . '</head>'],
                    $content);
            }

        }

        // display content and layout if exist
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