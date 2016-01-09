<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\Helper\Type\Str;

class NativeException extends \Exception
{
    protected $message;

    public function __construct($message = null) {
        if ($message !== null) {
            $this->message = $message;
        }
    }

    public function display($message = null)
    {
        if ($message === null) {
            $message = $this->message;
        }

        $message = Str::replace(root, '$DOCUMENT_ROOT', $message);
        $message = htmlentities($message);

        if (type === 'web') {
            header('HTTP/1.1 404 Not Found');
            return $this->rawHTML($message);
        }

        return $message;
    }

    protected function rawHTML($message = null)
    {
        return '<!DOCTYPE html><html><head><title>An error has occurred</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">Runtime error</h1><p>' . $message . '</p></div></body></html>';
    }
}