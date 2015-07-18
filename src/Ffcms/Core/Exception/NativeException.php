<?php

namespace Ffcms\Core\Exception;

class NativeException extends \Exception
{
    protected $message;

    public function __construct($message = null) {
        if ($message !== null) {
            $this->message = $message;
        }
    }

    public function display()
    {
        if (type === 'web') {
            header('HTTP/1.1 404 Not Found');
            echo $this->rawHTML($this->message);
        } else {
            echo $this->message;
        }

        die();
    }

    protected function rawHTML($message = null)
    {
        return '<!DOCTYPE html><html><head><title>An error has occurred</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">Runtime error</h1><p>' . $message . '</p></div></body></html>';
    }
}