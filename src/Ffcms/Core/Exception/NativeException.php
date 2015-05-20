<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;


class NativeException
{

    public function __construct($message = null)
    {
        header('HTTP/1.1 404 Not Found');
        if (type === 'web') {
            echo $this->rawHTML($message);
        } else {
            echo $message;
        }
        die();
    }

    protected function rawHTML($message = null)
    {
        return '<!DOCTYPE html><html><head><title>An error has occurred</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">Runtime error</h1><p>' . $message . '</p></div></body></html>';
    }
}