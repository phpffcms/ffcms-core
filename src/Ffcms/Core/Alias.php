<?php

namespace Ffcms\Core;

use \Core\Exception\NativeException;
use Core\Network\Request;

class Alias {

    /**
     * Absolute path to current view folder
     * @var string
     */
    public $currentViewPath;

    /**
     * Return full URL of current view folder
     * @var string
     */
    public $currentViewUrl;

    /**
     * Current app basic domain address, obtained from request
     * @var string
     */
    public $baseDomain;

    /**
     * Current app basic URL address, obtained from request
     * @var string
     */
    public $baseUrl;

    /**
     * Current app basic URL without any changes in pathway(lang-defined, etc)
     * @var string
     */
    public $scriptUrl;


    /**
     * Vendor library paths. Ex: App::$Alias->vendor['js']['jquery']. Available: jquery, bootstrap, fa, jquery-ui
     * @var array
     */
    public $vendor = [];



    public function __construct()
    {
        // build current viewer's path theme - full dir path
        $this->currentViewPath = root . '/View/' . workground . '/' . App::$Property->get('theme');
        try {
            if(!file_exists($this->currentViewPath))
                throw new \Exception("Could not load app views: " . $this->currentViewPath);
        } catch(\Exception $e) {
            \Core\App::$Debug->bar->getCollector('exceptions')->addException($e);
            new NativeException($e);
        }

        // build baseUrl
        $this->baseDomain = $_SERVER['SERVER_NAME'];
        $this->baseUrl = $this->scriptUrl = Request::getProtocol() . '://' . $this->baseDomain . App::$Property->get('basePath');
        if(\App::$Property->get('multiLanguage'))
            $this->baseUrl .= \App::$Request->getLanguage() . '/';
        // build vendor libs alias
        $this->vendor['js']['jquery'] = $this->scriptUrl . 'vendor/bower/jquery/dist/jquery.min.js';
        $this->vendor['css']['bootstrap'] = $this->scriptUrl . 'vendor/bower/bootstrap/dist/css/bootstrap.min.css';
        $this->vendor['js']['bootstrap'] = $this->scriptUrl . 'vendor/bower/bootstrap/dist/js/bootstrap.min.js';
        $this->vendor['css']['fa'] = $this->scriptUrl . 'vendor/bower/components-font-awesome/css/font-awesome.min.css';
        $this->vendor['js']['jquery-ui'] = $this->scriptUrl . 'vendor/bower/jquery-ui/jquery-ui.min.js';

        $this->currentViewUrl = $this->scriptUrl . 'View/' . workground . '/' . App::$Property->get('theme');
    }


}