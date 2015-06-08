<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\File;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\App;
use Ffcms\Core\Template\Variables;
use Ffcms\Core\Traits\DynamicGlobal;

class View
{

    use DynamicGlobal;

    protected $view_object;

    /**
     * Current theme full pathway
     * @var string
     */
    public $currentViewPath;


    /**
     * Construct object viewer from new View()
     * @param null|string $controller_name
     * @param null|string $view_file
     * @throws \DebugBar\DebugBarException
     */
    public function __construct($controller_name = null, $view_file = null)
    {
        // build current viewer's path theme - full dir path
        $themeAll = App::$Property->get('theme');
        $this->currentViewPath = root . '/Apps/View/' . env_name . '/' . $themeAll[env_name];
        try {
            if (!File::exist($this->currentViewPath)) {
                throw new NativeException('Could not load app views: ' . $this->currentViewPath);
            }
        } catch (NativeException $e) {
            $e->display();
        }

        // built on $view = new View('main', 'index');
        if (null !== $view_file && null !== $controller_name) {
            if (String::startsWith('Apps\\Controller\\', $controller_name)) {
                $controller_name = String::substr($controller_name, String::length('Apps\\Controller\\'));
            }
            if (String::endsWith('.php', $view_file)) {
                $view_file = String::substr($view_file, 0, String::length($view_file) - 4);
            }

            $view_path = $this->currentViewPath . '/' . strtolower($controller_name) . '/' . strtolower($view_file) . '.php';
            try {
                if (File::exist($view_path)) {
                    $this->view_object = $view_path;
                } else {
                    throw new SyntaxException('New view object not founded: ' . String::replace(root, null, $view_path));
                }
            } catch (SyntaxException $e) {
                $e->display();
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
    public function render($view, $params = [])
    {
        $call_log = debug_backtrace();
        $call_controller = null;
        foreach ($call_log as $caller) {
            if (String::startsWith('Apps\\Controller\\', $caller['class'])) {
                $call_controller = (string)$caller['class'];
            }
        }

        try {
            if (null === $call_controller) {
                throw new SyntaxException('On call View->render() not founded caller controller to define viewer name');
            }
        } catch (SyntaxException $e) {
            $e->display();
            return null;
        }

        $controller_name = String::substr($call_controller, String::length('Apps\\Controller\\' . env_name . '\\'));
        $view_path = App::$Alias->currentViewPath . '/' . strtolower($controller_name) . '/' . strtolower($view) . '.php';

        try {
            if (!File::exist($view_path)) {
                throw new SyntaxException('Viewer is not founded: ' . str_replace(root, null, $view_path));
            }
        } catch (SyntaxException $e) {
            $e->display();
            return null;
        }
        return self::renderSandbox($view_path, $params);
    }

    /**
     * Render viewer anywhere
     * @param string $viewPath
     * @param array $params
     * @return string
     */
    public function show($viewPath, array $params = null)
    {
        $viewPath = $this->currentViewPath . '/' . ltrim($viewPath, '/') . '.php';
        return $this->renderSandbox($viewPath, $params);
    }

    /**
     * @param string $path
     * @param array $params
     * @return string
     */
    protected function renderSandbox($path, array $params = null)
    {
        // render defaults params
        if (Object::isArray($params) && count($params) > 0) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }

        $global = self::buildGlobal();
        $self = $this;
        // turn on output buffer
        ob_start();
        include($path);
        $response = ob_get_contents();
        // turn off buffer
        ob_end_clean();
        return $response;
    }

    /**
     * Build global variables in stdObject
     * @return \stdClass
     */
    public function buildGlobal()
    {
        $global = new \stdClass();
        foreach (Variables::instance()->getGlobalsObject() as $var => $value) {
            $global->$var = $value;
        }
        return $global;
    }

    /**
     * Show custom code library link
     * @param string $type - js or css allowed
     * @return array|null|string
     */
    public function showCodeLink($type)
    {
        $items = App::$Alias->getCustomLibraryArray($type);
        // check if custom library available
        if (null === $items || !Object::isArray($items) || count($items) < 1) {
            return null;
        }

        $output = [];
        foreach ($items as $item) {
            $item = trim($item, '/');
            if (!String::startsWith(App::$Alias->scriptUrl, $item) && !String::startsWith('http', $item)) { // is local without proto and domain
                $item = App::$Alias->scriptUrl . '/' . $item;
            }
            $output[] = $item;
        }

        $clear = array_unique($output);
        $output = null;
        foreach ($clear as $row) {
            if ($type === 'css') {
                $output .= '<link rel="stylesheet" type="text/css" href="' . $row . '">' . "\n";
            } elseif ($type === 'js') {
                $output .= '<script src="' . $row . '"></script>' . "\n";
            }
        }

        return $output;
    }

    /**
     * Show plain code in template.
     * @param string $type
     * @return null|string
     */
    public function showPlainCode($type)
    {
        if (null === App::$Alias->getPlainCode($type) || !Object::isArray(App::$Alias->getPlainCode($type))) {
            return null;
        }

        $code = null;
        foreach (App::$Alias->getPlainCode($type) as $row) {
            $code .= $row . "\n";
        }

        return $code;
    }
}