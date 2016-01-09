<?php

namespace Ffcms\Core\Exception;


class JsonException extends \Exception
{

    /**
     * Display message as json response
     */
    public function display()
    {
        header('Content-Type: application/json');
        return json_encode([
            'status' => 0,
            'message' => $this->getMessage()
        ]);
    }
}