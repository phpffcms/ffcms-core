<?php

namespace Core\Network;

use Core\App;
use Core\Exception\SystemException;
use Core\Helper\String;


class Response {

    protected $layout = 'main.php';

    /**
     * Set application response header. Default - html
     * @param string $type ['html', 'js', 'json']
     */
    public function setHeader($type = 'html')
    {
        switch($type) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'js':
                header("Content-Type: text/javascript");
                break;
            default:
                header("Content-Type: text/html");
                break;
        }
    }

    /**
     * Set custom layout for this response
     * @param string $file
     */
    public function setLayout($file = 'main.php')
    {
        $this->layout = $file;
    }

    /**
     * Get layout for current response
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }


    public function render($view, $params = [])
    {
        $call_log = debug_backtrace();
        $call_controller = null;
        foreach($call_log as $caller) {
            if(String::startsWith('Controller\\', $caller['class'])) {
                $call_controller = (string)$caller['class'];
            }
        }

        if($call_controller === null) {
            new SystemException("Response->render() not found controller!");
        }

        $controller_name = mb_substr($call_controller, String::length('Controller\\'), null, "UTF-8");
        $view_path = App::$Data->viewPath . '/' . strtolower($controller_name) . "/" . strtolower($view) . '.php';

        if(!file_exists($view_path) || !is_readable($view_path))
        {
            new SystemException("Viewer " . $view . " is not founded!");
        }
        return $this->renderSandbox($view_path, $params);
    }

    protected function renderSandbox($path, $params = [])
    {
        foreach($params as $key=>$value)
        {
            $$key = $value;
        }
        // turn on output buffer
        ob_start();
        include_once($path);
        $response = ob_get_contents();
        // turn off buffer
        ob_end_clean();
        return $response;
    }

}