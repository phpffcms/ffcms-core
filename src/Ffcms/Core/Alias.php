<?php

namespace Ffcms\Core;

use \Core\Exception\NativeException;

class Alias {

    public $currentViewPath;

    public function __construct()
    {
        // build current viewer's path theme - full dir path
        $this->currentViewPath = root . '/View/' . workground . '/' . App::$Property->get('theme');
        try {
            if(!file_exists($this->currentViewPath))
                throw new \Exception("Could not load app views: " . $this->currentViewPath);
        } catch(\Exception $e) {
            \Core\App::$Debug->bar->getCollector('exceptions')->addException($e);
            new NativeException($e);
        }
    }


}