<?php

namespace Ffcms\Core;

use Core\Helper\String;
use Core\Arch\ErrorController;

class View {

    protected $view_object;

    public function __construct($view_file = null, $controller_name = null)
    {
        if(!is_null($view_file) && !is_null($controller_name)) {
            if(String::startsWith('Controller\\', $controller_name))
                $controller_name = mb_substr($controller_name, String::length('Controller\\'), null, "UTF-8");
            if(String::endsWith('.php', $view_file))
                $view_file = mb_substr($view_file, 0, String::length($view_file)-4, "UTF-8");

            $view_path = App::$Data->viewPath . '/' . strtolower($controller_name) . "/" . strtolower($view_file) . '.php';
            try {
                if(file_exists($view_path))
                    $this->view_object = $view_path;
                else
                    throw new \Exception("New view object not founded: " . str_replace(root, '', $view_path));
            } catch(\Exception $e) {
                App::$Debug->bar->getCollector('exceptions')->addException($e);
                new ErrorController($e);
            }
        }
    }

    /**
     * Return out result of object viewer rendering. Using only with $view = new View('name', 'controller'); $view->out(['a' => 'b']);
     * @param array $params
     * @return string
     */
    public function out($params)
    {
        if(is_null($this->view_object) || !is_array($params))
            return null;
        return self::renderSandbox($this->view_object, $params);
    }

    /**
     * Render view ONLY from controller interface
     * @param string $view
     * @param array $params
     * @return string
     * @throws \DebugBar\DebugBarException
     */
    public static function render($view, $params = [])
    {
        $call_log = debug_backtrace();
        $call_controller = null;
        foreach($call_log as $caller) {
            if(String::startsWith('Controller\\', $caller['class'])) {
                $call_controller = (string)$caller['class'];
            }
        }

        try {
           if(is_null($call_controller))
               throw new \Exception("On call View->render() not founded caller controller" . $call_log);
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new ErrorController($e);
        }

        $controller_name = mb_substr($call_controller, String::length('Controller\\'), null, "UTF-8");
        $view_path = App::$Data->viewPath . '/' . strtolower($controller_name) . "/" . strtolower($view) . '.php';

        try {
            if(!file_exists($view_path) || !is_readable($view_path))
                throw new \Exception("Viewer '" . $view . "' is not founded!");
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new ErrorController($e);
        }
        return self::renderSandbox($view_path, $params);
    }

    protected static function renderSandbox($path, $params = [])
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