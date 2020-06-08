<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\Helper\Type\Str;

/**
 * Class BanException. Work around throwed exception to display it
 */
class BanException extends \Exception
{
    protected $message;
    protected $title;

    /**
     * BanException constructor
     * @param string|null $message
     */
    public function __construct($message = null, $title = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }

        if ($title !== null) {
            $this->title = $title;
        }
        parent::__construct();
    }

    /**
     * Display native exception
     * @param string $message
     * @return string|null
     */
    public function display($message = null)
    {
        // if passed message is null get exception msg
        if (!$message) {
            $message = $this->message;
        }
        
        // hide root path from exception
        $message = Str::replace(root, '$DOCUMENT_ROOT', $message);
        $message = strip_tags($message);

        // generate response based on environment type
        switch (env_type) {
            case 'html':
                return $this->sendHTML($message, $this->title ?? 'Error');
            case 'json':
                return $this->sendJSON($message, $this->title ?? 'Error');
        }
        
        return $message;
    }

    /**
     * Build html response
     * @param string|null $message
     * @return string
     */
    protected function sendHTML($message = null, $title = null)
    {
        //header('HTTP/1.1 404 Not Found');
        return '<!DOCTYPE html><html><head><title>'. $this->title . '</title></head><body><div style="width:60%; margin: auto; background-color: #fcc;border: 1px solid #faa; padding: 0.5em 1em;"><h1 style="font-size: 120%">' . $title . '</h1><p>' . $message . '</p></div></body></html>';
    }
    
    /**
     * Build json response
     * @param string|null $message
     * @return string
     */
    protected function sendJSON($message = null)
    {
        header('Content-Type: application/json');
        return json_encode(['status' => 0, 'message' => $message]);
    }
}
