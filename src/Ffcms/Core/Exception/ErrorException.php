<?php

namespace Ffcms\Core\Exception;
use \Core\App;

class ErrorException {

    public function __construct($message = null)
    {
        App::$Response->setHeader(404);
        App::$Response->errorString = App::$Security->purifier()->purify($message);
    }
}