<?php

namespace Ffcms\Core\Arch;

use Core\App;

class ErrorController extends Controller
{

    public function __construct($message = null)
    {
        parent::__construct();
        App::$Response->setHeader(404);
        $this->response = $message;
    }
}