<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\Helper\Type\Str;

/**
 * Class NativeException. Work around throwed exception to display it
 */
class NativeException extends \Exception
{
    protected $message;

    /**
     * NativeException constructor. Pass message insde the exception
     * @param string|null $message
     */
    public function __construct($message = null) {
        if ($message !== null) {
            $this->message = $message;
        }
    }

    /**
     * Display native exception
     * @param string $message
     * @return string|unknown
     */
    public function display($message = null)
    {
        // if passed message is null get exception msg
        if ($message === null) {
            $message = $this->message;
        }

        // hide root path from exception
        $message = Str::replace(root, '$DOCUMENT_ROOT', $message);
        $message = htmlentities($message);
        
        // generate response based on environment type
        switch (env_type) {
            case 'html':
                return $this->sendHTML($message);
            case 'json':
                return $this->sendJSON($message);
        }
        
        return $message;
    }

    /**
     * Build html response
     * @param unknown $message
     */
    protected function sendHTML($message = null)
    {
        header('HTTP/1.1 404 Not Found');
        return '<!DOCTYPE html><html><head><title>An error has occurred</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">Runtime error</h1><p>' . $message . '</p></div></body></html>';
    }
    
    /**
     * Build json response
     * @param string $message
     */
    protected function sendJSON($message = NULL) 
    {
        header('Content-Type: application/json');
        return json_encode(['status' => 0, 'message' => $message]);
    }
}