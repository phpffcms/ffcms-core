<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class EmptyException extends Controller
{
    /**
     * @param string $m
     */
    public function __construct($m = null)
    {
        parent::__construct();
        $this->response = $m;
        $this->title = App::$Translate->translate('Not founded: 404');
        App::$Response->setStatusCode(404);
    }
}