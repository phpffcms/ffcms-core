<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Arch\ActiveModel;

class SimplePagination
{
    protected $url;
    protected $page;
    protected $step;
    protected $total;

    public function __construct(array $elements)
    {
        $this->url = $elements['url'];
        $this->page = (int)$elements['page'];
        $this->step = (int)$elements['step'] > 0 ? (int)$elements['step'] : 5;
        $this->total = (int)$elements['total'];
    }


    public function display()
    {
        // total items is less to pagination requirement
        if ($this->page * $this->step <= $this->total) {
            return null;
        }

        $lastPage = ceil($this->total/$this->step); // example: 6/5 ~> 2

    }
}