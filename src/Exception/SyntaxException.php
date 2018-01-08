<?php

namespace Ffcms\Core\Exception;

class SyntaxException extends TemplateException
{
    public function display()
    {
        $this->title = 'Code syntax exception';
        $this->status = 503;
        $this->tpl = 'syntax';
        $this->text = 'Website code syntax exception: %e%';

        return parent::display();
    }
}
