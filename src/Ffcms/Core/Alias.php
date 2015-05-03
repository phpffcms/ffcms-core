<?php

namespace Ffcms\Core;


/**
 * Class Alias - fast alias for core property's
 * @package Ffcms\Core
 */
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
     * Vendor library paths. Ex: App::$Alias->vendor['js']['jquery']['url']. Available: jquery, bootstrap, fa, jquery-ui
     * @var array
     */
    public $vendor = [];

    /**
     * Additional variable for custom JS scripts
     * @var array
     */
    public $customJS = [];

    /**
     * Additional variable for custom CSS scripts
     * @var array
     */
    public $customCSS = [];

    /**
     * Block with additional code/etc before </body> close tag
     * @var array
     */
    public $afterBody = [];



    public function __construct()
    {
        // make alias for view pathway
        $this->currentViewPath = App::$View->currentViewPath;

        // make alias for baseUrl, script url and domain
        $this->baseDomain = App::$Request->baseDomain;
        $this->baseUrl = App::$Request->baseUrl;
        $this->scriptUrl = App::$Request->scriptUrl;

        // build vendor libs alias
        $this->vendor['js']['jquery']['url'] = $this->scriptUrl . 'vendor/bower/jquery/dist/jquery.min.js';
        $this->vendor['js']['jquery']['path'] = root . '/vendor/bower/jquery/dist/jquery.min.js';
        $this->vendor['css']['bootstrap']['url'] = $this->scriptUrl . 'vendor/bower/bootstrap/dist/css/bootstrap.min.css';
        $this->vendor['css']['bootstrap']['path'] = root . '/vendor/bower/bootstrap/dist/css/bootstrap.min.css';
        $this->vendor['js']['bootstrap']['url'] = $this->scriptUrl . 'vendor/bower/bootstrap/dist/js/bootstrap.min.js';
        $this->vendor['js']['bootstrap']['path'] = root . '/vendor/bower/bootstrap/dist/js/bootstrap.min.js';
        $this->vendor['css']['fa']['url'] = $this->scriptUrl . 'vendor/bower/components-font-awesome/css/font-awesome.min.css';
        $this->vendor['css']['fa']['path'] = root . '/vendor/bower/components-font-awesome/css/font-awesome.min.css';
        $this->vendor['js']['jquery-ui']['url'] = $this->scriptUrl . 'vendor/bower/jquery-ui/jquery-ui.min.js';
        $this->vendor['js']['jquery-ui']['path'] = root . '/vendor/bower/jquery-ui/jquery-ui.min.js';

        $themeAll = App::$Property->get('theme');
        $this->currentViewUrl = $this->scriptUrl . 'Apps/View/' . workground . '/' . $themeAll[workground];
    }


}