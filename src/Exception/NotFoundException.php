<?php

namespace Ffcms\Core\Exception;

class NotFoundException extends TemplateException
{
    public function display()
    {
        $this->status = 404;
        $this->title = '404 Not Found';
        $this->text = 'Unable to find this URL: %e%';
        $this->tpl = 'notfound';

        return parent::display();
    }
}
