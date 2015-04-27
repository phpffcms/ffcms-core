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

        $this->bar->addCollector(new ConfigCollector());

    }
}