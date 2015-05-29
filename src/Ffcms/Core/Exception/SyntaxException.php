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
        $load->setGlobalVar('title', 'Syntax exception');
        $load->response = '[SyntaxException]: ' . $this->getMessage();
        App::$Response->setStatusCode(403);
    }
}