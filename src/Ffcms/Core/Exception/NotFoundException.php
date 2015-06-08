<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class NotFoundException extends \Exception
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
        $load->setGlobalVar('title', '404 Not Found');
        $load->response = App::$Translate->get('Default', 'Unable to find this URL', []);
        App::$Response->setStatusCode(404);
    }
}