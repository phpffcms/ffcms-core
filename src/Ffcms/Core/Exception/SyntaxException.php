<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class SyntaxException extends \Exception
{

    public function display()
    {
        if (App::$Debug !== null) {
            App::$Debug->addException($this);
        }

        $load = new Controller();
        if (defined('env_no_layout') && env_no_layout === true) {
            $load->layout = null;
        }
        $load->setGlobalVar('title', 'Syntax exception');
        $load->response = '[SyntaxException]: ' . $this->getMessage();
        App::$Response->setStatusCode(403);
    }
}