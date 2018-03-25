<?php

namespace Ffcms\Core\Exception;

use Ffcms\Core\App;
use Ffcms\Core\Arch\Controller;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Templex\Engine\Vars;

abstract class TemplateException extends \Exception
{
    public $status = 404;
    public $title = '404 Not Found';
    public $text = 'An unexpected error occurred: %e%';
    public $tpl = 'default';

    /**
     * Display exception template
     * @return string
     */
    public function display()
    {
        // hide path root and stip tags from exception message
        $msg = Str::replace(root, '$DOCUMENT_ROOT', $this->getMessage());
        $msg = App::$Security->strip_tags($msg);
        // get message translation
        $this->text = App::$Translate->get('Default', $this->text, ['e' => $msg]);
        
        // if exception is throwed not from html-based environment
        if (env_type !== 'html') {
            if (env_type === 'json') {
                return (new JsonException($msg))->display();
            } else {
                return $this->text;
            }
        }
        
        // add notification in debug bar if exist
        if (App::$Debug !== null) {
            App::$Debug->addException($this);
        }

        // return rendered result
        return $this->buildFakePage();
    }
    
    /**
     * Build fake page with error based on fake controller initiation
     */
    protected function buildFakePage()
    {
        // initialize fake controller to display page with exception
        $fakeController = new Controller();
        // check if used no layout template
        if (defined('env_no_layout') && env_no_layout === true) {
            $fakeController->layout = null;
        }
        Vars::instance()->setGlobal('title', App::$Translate->get('Default', $this->title));

        // build error text
        $rawResponse = 'error';
        try {
            $rawResponse = App::$View->render('native/errors/' . $this->tpl, ['msg' => $this->text]);
            if (Str::likeEmpty($rawResponse)) {
                $rawResponse = $this->text;
            }
        } catch (SyntaxException $e) {
            $rawResponse = $this->text;
        }
        // set controller body response
        $fakeController->setOutput($rawResponse);
        // set status code for header
        App::$Response->setStatusCode((int)$this->status);
        // return compiled html output
        return $fakeController->buildOutput();
    }
}
