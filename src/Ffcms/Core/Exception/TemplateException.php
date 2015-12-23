<?php

namespace Ffcms\Core\Exception;


use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

abstract class TemplateException extends \Exception
{
    public $status = 404;
    public $title = '404 Not Found';
    public $text = 'An unexpected error occurred: %e%';
    public $tpl = 'default';

    public function display()
    {
        $this->text = App::$Translate->get('Default', $this->text, ['e' => $this->getMessage()]);

        if (App::$Debug !== null) {
            App::$Debug->addException($this);
        }

        $fakeController = new Controller();
        if (defined('env_no_layout') && env_no_layout === true) {
            $fakeController->layout = null;
        }

        $fakeController->setGlobalVar('title', App::$Translate->get('Default', $this->title));
        $rawResponse = 'error';
        try {
            $rawResponse = App::$View->render('errors/' . $this->tpl, ['msg' => $this->text]);
            if (Str::likeEmpty($rawResponse)) {
                $rawResponse = $this->text;
            }
        } catch (SyntaxException $e) {
            $rawResponse = $this->text;
        }
        $fakeController->setResponse($rawResponse);

        App::$Response->setStatusCode((int)$this->status);
    }

}