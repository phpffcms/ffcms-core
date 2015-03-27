<?php

namespace Core\Arch;

use Core\App;

abstract class Controller {

    public static $layout;
    public $response;

    final function __construct()
    {
        $this->before();
    }

    public function before() {}

    /**
     * Compile output
     */
    final function __destruct()
    {
        $this->after();
        $layout = (isset(self::$layout) && !is_null(self::$layout)) ? self::$layout : App::$Response->getLayout();
        $layoutPath = App::$Data->viewPath . '/layout/' . $layout;
        if(file_exists($layoutPath) && is_readable($layoutPath)) {
            $body = $this->response;
            @include_once($layoutPath);
        }
    }

    public function after() {}



}