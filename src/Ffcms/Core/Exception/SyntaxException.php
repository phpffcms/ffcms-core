<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Helper\Type\Str;

class SyntaxException extends TemplateException
{

    public function display()
    {
        $this->title = 'Code syntax exception';
        $this->status = 503;
        $this->tpl = 'syntax';
        $this->text = 'Website code syntax exception: %e%';

        parent::display();
    }
}