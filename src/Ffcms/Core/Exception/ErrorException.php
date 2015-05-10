<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Template\Variables;

class ErrorException {

    public function __construct($message = null)
    {
        App::$Response->setStatusCode(404);
        Variables::instance()->setError(App::$Security->purifier()->purify($message));
    }
}