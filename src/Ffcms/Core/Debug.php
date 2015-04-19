<?php

namespace Ffcms\Core;

use \DebugBar\StandardDebugBar;
use \DebugBar\DataCollector\ConfigCollector;
use \Core\App;

class Debug
{

    public $bar;
    public $render;

    public function __construct()
    {
        $this->bar = new StandardDebugBar();
        $this->render = $this->bar->getJavascriptRenderer();

        // add cfg debug info
        //$this->bar->addCollector(new ConfigCollector(['Configs' => App::$Property->getAll()]));
        //$this->printOS();
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