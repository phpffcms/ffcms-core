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
        return '<html><head>' . App::$Debug->render->renderHead() . '</head><body><div style="display: table;margin: 0 auto;width: 30%;border: 1px solid #e2342b;"><h1 style="font-size: 16px;">Native error exception</h1><p>' . $message . '</p></div>' . \App::$Debug->render->render() . '</body></html>';
    }
}