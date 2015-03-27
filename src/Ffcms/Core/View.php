<?php

namespace Ffcms\Core;

use Ffcms\Core\Helper\String;
use Ffcms\Core\Exception\SystemException;

class View {

    protected $globalVars;

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
            new SystemException("View->render() not found controller!");
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

    /**
     * Set global variable for Views
     * @param string $var
     * @param string $value
     */
    public function setGlobal($var, $value)
    {
        $this->globalVars[$var] = $value;
    }

    /**
     * Set global variable from key=>value array (key = varname)
     * @param array $array
     */
    public function setGlobalArray($array)
    {
        if(!is_array($array))
            return;
        foreach($array as $var => $value)
        {
            $this->globalVars[$var] = $value;
        }
    }

    /**
     * Get all global variables
     * @return array|null
     */
    public function getGlobal()
    {
        return $this->globalVars;
    }

}