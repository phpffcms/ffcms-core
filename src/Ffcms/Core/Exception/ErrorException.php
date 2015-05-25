<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Template\Variables;

class ErrorException
{

    /**
     * @param null|string $message
     */
    public function __construct($message = null) {
        App::$Response->setStatusCode(404);
        $message = App::$Translate->translate($message);
        Variables::instance()->setError(App::$Security->secureHtml($message));
    }
}