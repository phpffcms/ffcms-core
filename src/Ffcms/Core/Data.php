<?php

namespace Ffcms\Core;

use Ffcms\Core\Exception\SystemException;

class Data {

    // full path to directory View
    public $viewPath;

    function __construct()
    {
        $this->viewPath = root . '/View/interface_user/' . App::$Property->get('theme');
        if(!file_exists($this->viewPath))
            new SystemException("Could not load app views: " . $this->viewPath);
    }
}