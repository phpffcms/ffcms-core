<?php

namespace Ffcms\Core;

use DebugBar\StandardDebugBar;

class Debug {

    public $bar;
    public $render;

    public function __construct()
    {
        $this->bar = new StandardDebugBar();
        $this->render = $this->bar->getJavascriptRenderer();

        $this->printOS();
    }

    protected function printOS()
    {
        $this->bar->getCollector('messages')->info("============== Native information ==============");
        $this->bar->getCollector('messages')->info(php_uname('a'));
        $this->bar->getCollector('messages')->info("PHP Version: " . phpversion());
        $this->bar->getCollector('messages')->info("PHP Memory limit: " . ini_get('memory_limit'));
        $this->bar->getCollector('messages')->info("PHP Max execute time: " . ini_get('max_execution_time') . 's');
    }
}