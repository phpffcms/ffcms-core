<?php

namespace Ffcms\Core\Arch;

class ErrorController extends Controller
{

    public function __construct($message = null)
    {
        parent::__construct();
        $this->response = $message;
    }
}