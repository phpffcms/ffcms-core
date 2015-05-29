<?php

namespace Ffcms\Core\Exception;

class NativeException extends \Exception
{

    public function display()
    {

        if (type === 'web') {
            header('HTTP/1.1 404 Not Found');
            echo $this->rawHTML($this->getMessage());
        } else {
            echo $this->getMessage();
        }

        die();
    }

    protected function rawHTML($message = null)
    {
        return '<!DOCTYPE html><html><head><title>An error has occurred</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">Runtime error</h1><p>' . $message . '</p></div></body></html>';
    }
}