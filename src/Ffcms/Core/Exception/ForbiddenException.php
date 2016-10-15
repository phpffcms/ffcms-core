<?php

namespace Ffcms\Core\Exception;

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