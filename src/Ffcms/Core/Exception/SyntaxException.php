<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Helper\Type\Str;

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

        $message = $this->getMessage();
        if (Str::likeEmpty($message)) {
            $message = 'Unknown syntax exception';
        }

        $load->response = '[SyntaxException]: ' . $message;
        App::$Response->setStatusCode(403);
    }
}