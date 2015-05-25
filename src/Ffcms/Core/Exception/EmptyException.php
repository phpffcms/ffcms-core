<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class EmptyException extends Controller
{
    /**
     * @param string|null $m
     */
    public function __construct($m = null)
    {
        parent::__construct();
        $this->response = App::$Translate->translate($m);
        $this->title = App::$Translate->translate('Not founded: 404');
        App::$Response->setStatusCode(404);
    }
}