<?php

namespace Ffcms\Core\Exception;

use \Core\App;

class EmptyException extends \Core\Arch\Controller
{
    public function __construct($m = null)
    {
        parent::__construct();
        $this->response = $m;
        $this->title = App::$Translate->translate('Not founded: 404');
        App::$Response->setHeader(404);
    }
}