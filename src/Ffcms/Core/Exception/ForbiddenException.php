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
        if (defined('env_no_layout') && env_no_layout === true) {
            $load->layout = null;
        }
        $load->setGlobalVar('title', '403 Forbidden');
        $message = App::$Translate->get('Default', 'Access to this page is forbidden', []);
        if ($this->getMessage() !== null) {
            $message = $this->getMessage();
        }
        $load->response = $message;
        App::$Response->setStatusCode(403);
    }
}