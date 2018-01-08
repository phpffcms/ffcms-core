<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Debug\DebugMeasure;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Interfaces\iController;
use Ffcms\Core\Template\Variables;
use Ffcms\Core\Traits\DynamicGlobal;

/**
 * Class Controller. Classic carcase of controller in MVC architecture.
 * @package Ffcms\Core\Arch
 */
class Controller implements iController
{
    use DynamicGlobal, DebugMeasure;

    /** @var string */
    public $layout = 'main';
    public $lang = 'en';

    /** @var string */
    protected $output;

    /** @var \Ffcms\Core\Network\Request */
    public $request;
    /** @var \Ffcms\Core\Network\Response */
    public $response;
    /** @var View */
    public $view;

    /**
     * Controller constructor. Set controller access data - request, response, view
     */
    public function __construct()
    {
        $this->lang = App::$Request->getLanguage();
        $this->request = App::$Request;
        $this->response = App::$Response;
        $this->view = App::$View;
        $this->before();
    }

    /** Before action call method */
    public function before() {}
    
    /** Global bootable method */
    public static function boot(): void {}

    /**
     * Build variables and display output html
     * @return string
     * @throws NativeException
     * @throws \DebugBar\DebugBarException
     */
    public function buildOutput(): ?string
    {
        $this->after();

        // if layout is not required and this is just standalone app
        if ($this->layout === null) {
            $content = $this->output;
        } else {
            $this->startMeasure(__METHOD__);

            $layoutPath = App::$Alias->currentViewPath . '/layout/' . $this->layout . '.php';
            if (!File::exist($layoutPath)) {
                throw new NativeException('Layout not founded: ' . $layoutPath);
            }

            $body = $this->output;
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


            $this->stopMeasure(__METHOD__);

            // add debug bar
            if (App::$Debug) {
                $content = Str::replace(
                    ['</body>', '</head>'],
                    [App::$Debug->renderOut() . '</body>', App::$Debug->renderHead() . '</head>'],
                    $content);
            }

        }

        return $content;
    }

    /** After action called method */
    public function after() {}

    /**
     * Set single global variable
     * @param string $var
     * @param string $value
     * @param bool $html
     * @return void
     */
    public function setGlobalVar(string $var, string $value, bool $html = false): void
    {
        Variables::instance()->setGlobal($var, $value, $html);
    }

    /**
     * Set global variables as array key=>value
     * @param array $array
     * @return void
     */
    public function setGlobalVarArray(array $array): void
    {
        Variables::instance()->setGlobalArray($array);
    }

    /**
     * Special method to set response of action execution
     * @param string $output
     * @return void
     */
    public function setOutput($output): void
    {
        $this->output = $output;
    }

    /**
     * Get response of action rendering
     * @return string
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

}