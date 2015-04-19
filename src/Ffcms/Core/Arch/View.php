<?php

namespace Ffcms\Core\Arch;

use Core\Helper\Object;
use Core\Helper\String;
use Core\Exception\ErrorException;
use Core\Exception\NativeException;
use Core\App;

abstract class View extends \Core\Arch\Constructors\Magic {

    protected $view_object;

    /**
     * Current theme full pathway
     * @var string
     */
    public $currentViewPath;


    /**
     * @param null|string $view_file
     * @param null|string $controller_name
     * @throws \DebugBar\DebugBarException
     */
    public function __construct($view_file = null, $controller_name = null)
    {
        // build current viewer's path theme - full dir path
        $themeAll = App::$Property->get('theme');
        $this->currentViewPath = root . '/View/' . workground . '/' . $themeAll[workground];
        try {
            if(!file_exists($this->currentViewPath)) {
                throw new \Exception('Could not load app views: ' . $this->currentViewPath);
            }
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new NativeException($e);
        }

        // built on $view = new View('index', 'main');
        if(!is_null($view_file) && !is_null($controller_name)) {
            if (String::startsWith('Controller\\', $controller_name)) {
                $controller_name = String::substr($controller_name, String::length('Controller\\'));
            }
            if (String::endsWith('.php', $view_file)) {
                $view_file = String::substr($view_file, 0, String::length($view_file) - 4);
            }

            $view_path = $this->currentViewPath . '/' . strtolower($controller_name) . '/' . strtolower($view_file) . '.php';
            try {
                if (file_exists($view_path)) {
                    $this->view_object = $view_path;
                } else {
                    throw new \Exception('New view object not founded: ' . str_replace(root, null, $view_path));
                }
            } catch(\Exception $e) {
                App::$Debug->bar->getCollector('exceptions')->addException($e);
                new ErrorException($e);
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
        if ($this->view_object === null || !Object::isArray($params)) {
            return null;
        }
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
           if(is_null($call_controller)) {
               throw new \Exception('On call View->render() not founded caller controller' . $call_log);
           }
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new ErrorException($e);
        }

        $controller_name = String::substr($call_controller, String::length('Controller\\'));
        $view_path = App::$Alias->currentViewPath . '/' . strtolower($controller_name) . '/' . strtolower($view) . '.php';

        try {
            if(!file_exists($view_path) || !is_readable($view_path)) {
                throw new \Exception('Viewer "' . $view . '" is not founded!');
            }
        } catch(\Exception $e) {
            App::$Debug->bar->getCollector('exceptions')->addException($e);
            new ErrorException($e);
        }
        return self::renderSandbox($view_path, $params);
    }

    /**
     * Render viewer anywhere
     * @param string $viewPath
     * @param array $params
     * @return string
     */
    public function show($viewPath, $params = [])
    {
        $viewPath = $this->currentViewPath . '/' . ltrim($viewPath, '/') . '.php';
        return $this->renderSandbox($viewPath, $params);
    }

    protected function renderSandbox($path, $params = [])
    {
        // render defaults params
        foreach($params as $key=>$value)
        {
            $$key = $value;
        }
        $global = self::buildGlobal();
        $self = $this;
        // turn on output buffer
        ob_start();
        include_once($path);
        $response = ob_get_contents();
        // turn off buffer
        ob_end_clean();
        return $response;
    }

    public function buildGlobal()
    {
        $global = new \stdClass();
        foreach(App::$Response->getGlobal() as $var => $value) {
            $global->$var = $value;
        }
        return $global;
    }

    /**
     * Display custom JS libs
     * @return null|string
     */
    public function showCustomJS()
    {
        $js = App::$Alias->customJS;
        $output = null;
        if (count($js) < 1) {
            return null;
        }

        foreach ($js as $item) {
            $item = trim($item, '/');
            if(!String::endsWith('.js', $item)) {
                continue;
            }
            if (!String::startsWith(App::$Alias->scriptUrl, $item) && !String::startsWith('http', $item)) { // is local without proto and domain
                $item = App::$Alias->scriptUrl . $item;
            }
            $output[] = $item;
        }

        $clear = array_unique($output);
        $output = null;
        foreach ($clear as $row) {
            $output .= '<script src="' . $row . '"></script>' . "\n";
        }

        return $output;
    }

    /**
     * Display custom CSS libs
     * @return null|string
     */
    public function showCustomCSS()
    {
        $css = App::$Alias->customCSS;
        $output = null;
        if (count($css) < 1) {
            return null;
        }

        foreach ($css as $item) {
            $item = trim($item, '/');
            if(!String::endsWith('.css', $item)) {
                continue;
            }
            if (!String::startsWith(App::$Alias->scriptUrl, $item) && !String::startsWith('http', $item)) { // is local without proto and domain
                $item = App::$Alias->scriptUrl . $item;
            }
            $output[] = $item;
        }

        $clear = array_unique($output);
        $output = null;
        foreach ($clear as $row) {
            $output .= '<link rel="stylesheet" type="text/css" href="' . $row . '">' . "\n";
        }

        return $output;
    }

    /**
     * Display custom code after body main (on before </body> close tag)
     * @return null|string
     */
    public function showAfterBody()
    {
        $code = null;
        if (Object::isArray(App::$Alias->afterBody) && count(App::$Alias->afterBody) > 0) {
            foreach(App::$Alias->afterBody as $row) {
                $code .= $row . "\n";
            }
        }
        return $code;
    }


}