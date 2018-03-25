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
use Ffcms\Templex\Template;

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

    /** After action called method */
    public function after() {}
}
