<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class ForbiddenException extends \Exception
{

    public function display()
    {
        if (App::$Debug !== null) {
            App::$Debug->addException($this);
        }

        $load = new Controller();
        $load->setGlobalVar('title', '403 Forbidden');
        $load->response = App::$Translate->get('Default', 'Access to this page is forbidden', []);
        App::$Response->setStatusCode(403);
    }
}