<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Exception\NativeException;
use Ffcms\Core\Exception\SyntaxException;
use Ffcms\Core\Helper\FileSystem\Directory;
use Ffcms\Core\Helper\FileSystem\File;
use Ffcms\Core\Helper\FileSystem\Normalize;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Template\Variables;
use Ffcms\Core\Traits\DynamicGlobal;

class View
{
    use DynamicGlobal;

    /**
     * Global path for current environment theme
     * @var string
     */
    public $themePath;

    private $path;
    private $params;

    private $sourcePath;

    /**
     * Lets construct viewer
     * @throws NativeException
     */
    public function __construct()
    {
        // get theme config and build full path
        $themeConfig = App::$Properties->get('theme');
        $this->themePath = root . DIRECTORY_SEPARATOR . 'Apps' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . env_name;
        if (isset($themeConfig[env_name]) && Str::length($themeConfig[env_name]) > 0) {
            $this->themePath .=  DIRECTORY_SEPARATOR . $themeConfig[env_name];
        } else {
            $this->themePath .= DIRECTORY_SEPARATOR . 'default';
        }

        // check if theme is available
        if (!Directory::exist($this->themePath)) {
            throw new NativeException('Apps theme is not founded: ' . Str::replace(root, null, $this->themePath));
        }

        // get input args and build class properties
        $args = func_get_args();
        $this->path = array_shift($args);
        $this->params = array_shift($args);
        $this->sourcePath = array_shift($args);
    }

    /**
     * Render viewer based on construct or passed params. render($path, $params, $sourcePath)
     * @return string
     * @throws NativeException
     * @throws SyntaxException
     */
    public function render()
    {
        // get call arguments
        $arguments = func_get_args();

        // get params from constructor
        $path = $this->path;
        $params = $this->params;
        $source = $this->sourcePath;

        // if path is not defined - try to find it in arguments
        if ($path === null) {
            $path = array_shift($arguments);
        }

        // if arguments is not define - try to find in arguments
        if ($params === null) {
            $params = array_shift($arguments);
        }

        // if directory of caller is not defiend - lets find in argument
        if ($source === null) {
            $source = array_shift($arguments);
        }

        // path still not defined?
        if ($path === null) {
            throw new SyntaxException('Viewer is not founded: ' . App::$Security->strip_tags($path));
        }

        // cleanup from slashes on start/end
        $path = trim($path, '/\\');

        // lets find available viewer file
        $path = $this->findViewer($path, $source);

        // return response
        return $this->renderSandbox($path, $params);
    }

    /**
     * Try to find exist viewer full path
     * @param string $path
     * @param string|null $source
     * @return null|string
     * @throws NativeException
     */
    private function findViewer($path, $source = null)
    {
        $tmpPath = null;

        // sounds like a relative path for current view theme
        if (Str::contains('/', $path)) {
            // lets try to get full path for current theme
            $tmpPath = $path;
            if (!Str::startsWith($this->themePath, $path)) {
                $tmpPath = Normalize::diskPath($this->themePath . '/' . $path . '.php');
            }
        } else { // sounds like a object-depended view call from controller or etc
            // get stack trace of callbacks
            $calledLog = debug_backtrace();
            $calledController = null;

            // lets try to find controller in backtrace
            foreach ($calledLog as $caller) {
                if (isset($caller['class']) && Str::startsWith('Apps\Controller\\', $caller['class'])) {
                    $calledController = (string)$caller['class'];
                }
            }

            // depended controller is not founded? Let finish
            if ($calledController === null) {
                throw new NativeException('View render is failed: callback controller not founded! Call with relative path: ' . $path);
            }

            // get controller name
            $controllerName = Str::sub($calledController, Str::length('Apps\\Controller\\' . env_name . '\\'));
            $controllerName = Str::lowerCase($controllerName);
            // get full path
            $tmpPath = $this->themePath . DIRECTORY_SEPARATOR . $controllerName . DIRECTORY_SEPARATOR . $path . '.php';
        }

        // check if builded view full path is exist
        if (File::exist($tmpPath)) {
            return $tmpPath;
        }

        // hmm, not founded. Lets try to find in caller directory (for widgets, apps packages and other)
        if ($source !== null) {
            $tmpPath = Normalize::diskPath($source . DIRECTORY_SEPARATOR . $path . '.php');
            if (File::exist($tmpPath)) {
                // add notify for native views
                if (App::$Debug !== null) {
                    App::$Debug->addMessage('Render native viewer: ' . Str::replace(root, null, $tmpPath), 'info');
                }
                return $tmpPath;
            }
        }

        if (App::$Debug !== null) {
            App::$Debug->addMessage('Viewer not founded on rendering: ' . $path, 'warning');
        }

        return null;
    }

    /**
     * Alias of render
     * @deprecated
     * @return string|null
     */
    public function show()
    {
        return call_user_func_array([$this, 'render'], func_get_args());
    }

    /**
     * Render view in sandbox function
     * @param string $path
     * @param array|null $params
     * @return string
     */
    protected function renderSandbox($path, $params = null)
    {
        if ($path === null || !File::exist($path)) {
            if (App::$Debug !== null) {
                if (Str::likeEmpty($path)) {
                    $path = 'Unknown';
                }
                App::$Debug->addMessage('Viewer is not founded: ' . $path, 'error');
            }
            return null;
        }

        // render defaults params as variables
        if (Obj::isArray($params) && count($params) > 0) {
            foreach ($params as $key => $value) {
                $$key = $value;
            }
        }

        $global = $this->buildGlobal();
        $self = $this;
        // turn on output buffer
        ob_start();
        include($path);
        $response = ob_get_contents();
        // turn off buffer
        ob_end_clean();
        // cleanup init params
        $this->path = null;
        $this->params = null;
        $this->sourcePath = null;
        // return response
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
        if (null === $items || !Obj::isArray($items) || count($items) < 1) {
            return null;
        }

        $output = [];
        foreach ($items as $item) {
            $item = trim($item, '/');
            if (!Str::startsWith(App::$Alias->scriptUrl, $item) && !Str::startsWith('http', $item)) { // is local without proto and domain
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

        // unset used
        App::$Alias->unsetCustomLibrary($type);

        return $output;
    }

    /**
     * Show plain code in template.
     * @param string $type
     * @return null|string
     */
    public function showPlainCode($type)
    {
        if (null === App::$Alias->getPlainCode($type) || !Obj::isArray(App::$Alias->getPlainCode($type))) {
            return null;
        }

        $code = null;
        foreach (App::$Alias->getPlainCode($type) as $row) {
            $code .= $row . "\n";
        }

        return $code;
    }
}