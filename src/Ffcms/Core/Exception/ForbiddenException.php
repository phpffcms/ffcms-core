<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;

class ForbiddenException extends TemplateException
{

    public function display()
    {
        $this->status = 403;
        $this->title = '403 Forbidden';
        $this->text = 'Access to this page is forbidden: %e%';
        $this->tpl = 'forbidden';

        return parent::display();
    }
}