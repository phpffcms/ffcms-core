<?php

namespace Ffcms\Core;

use DebugBar\StandardDebugBar;

class Debug
{

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
        /**$info = [
         * '============== Native information ==============',
         * 'OS uname' => php_uname('a'),
         * 'PHP Version' => phpversion(),
         * 'PHP Memory limit' => ini_get('memory_limit'),
         * 'PHP Max execute time' => ini_get('max_execution_time') . 's'
         * ];
         * $this->bar->getCollector('messages')->info($info);*/
    }
}