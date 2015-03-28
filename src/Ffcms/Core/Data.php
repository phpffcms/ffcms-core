<?php

namespace Ffcms\Core;

use Core\Exception\NativeException;

class Data {

    // full path to directory View
    public $viewPath;

    function __construct()
    {
        $this->viewPath = root . '/View/' . workground . '/' . App::$Property->get('theme');
        try {
            if(!file_exists($this->viewPath))
                throw new \Exception("Could not load app views: " . $this->viewPath);
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new NativeException($e);
        }
    }
}