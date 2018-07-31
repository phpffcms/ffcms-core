<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Debug\DebugMeasure;
use Ffcms\Core\Interfaces\iController;

/**
 * Class Controller. Classic carcase of controller in MVC architecture.
 * @package Ffcms\Core\Arch
 */
class Controller implements iController
{
    use DebugMeasure;

    /** @var string */
    public $lang = 'en';

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
        if (App::$Debug) {
            App::$Debug->startMeasure(get_class($this));
            App::$Debug->bar->getCollector('messages')->info('Use controller: ' . get_class($this));
        }
        $this->lang = App::$Request->getLanguage();
        $this->request = App::$Request;
        $this->response = App::$Response;
        $this->view = App::$View;
        $this->before();
    }

    /** Before action call method */
    public function before()
    {
    }
    
    /** Global bootable method */
    public static function boot(): void
    {
    }

    /** After action called method */
    public function after()
    {
    }

    public function __destruct()
    {
        if (App::$Debug) {
            App::$Debug->stopMeasure(get_class($this));
        }
    }
}
