<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Exception\SystemException;
use Ffcms\Core\Helper\String;


class Response {
    /**
     * Set application response header. Default - html
     * @param string $type ['html', 'js', 'json']
     */
    public function setHeader($type = 'html')
    {
        switch($type) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'js':
                header("Content-Type: text/javascript");
                break;
            default:
                header("Content-Type: text/html");
                break;
        }
    }
}