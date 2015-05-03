<?php

namespace Ffcms\Core;

use \DebugBar\StandardDebugBar;
use \DebugBar\DataCollector\ConfigCollector;

/**
 * Class Debug - display information of debug and collected data in debug bar
 * @package Ffcms\Core
 */
class Debug
{

    public $bar;
    public $render;

    public function __construct()
    {
        $this->bar = new StandardDebugBar();
        $this->render = $this->bar->getJavascriptRenderer();

        $this->bar->addCollector(new ConfigCollector());
    }

    /**
     * Add exception into debug bar
     * @param $e
     * @throws \DebugBar\DebugBarException
     */
    public function addException($e)
    {
        $this->bar->getCollector('exceptions')->addException($e);
    }
}