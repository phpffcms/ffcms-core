<?php

namespace Ffcms\Core\Exception;
use \Core\App;

class ErrorException extends \Core\Arch\Controller {

    public function __construct($message = null)
    {
        parent::__construct();
        App::$Response->setHeader(404);
        $this->response = $message;
    }
}