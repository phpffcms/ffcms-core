<?php

namespace Ffcms\Core;

use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Object;


/**
 * Class Alias - fast alias for core property's
 * @package Ffcms\Core
 */
class Alias
{

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
     * Default vendor library's
     * @var array
     */
    protected $vendorNamedLibrary = [
        'js' => null,
        'css' => null
    ];

    /**
     * Custom code library's
     * @var array
     */
    protected $codeCustomLibrary = [
        'js' => null,
        'css' => null
    ];

    /**
     * Custom code storage for templates
     * @var array
     */
    protected $plainCode = [
        'js' => null,
        'css' => null
    ];


    public function __construct()
    {
        // make alias for view pathway
        $this->currentViewPath = App::$View->themePath;

        // make alias for baseUrl, script url and domain
        $this->baseDomain = App::$Request->getHost();
        $this->scriptUrl = App::$Request->getSchemeAndHttpHost();
        $this->baseUrl = $this->scriptUrl;
        if (App::$Request->getBasePath() !== null) {
            $this->baseUrl .= App::$Request->getBasePath();
        }
        if (App::$Request->languageInPath() && App::$Request->getLanguage() !== null) {
            $this->baseUrl .= '/' . App::$Request->getLanguage();
        }

        // build vendor libs alias
        $this->vendorNamedLibrary['js']['jquery'] = $this->scriptUrl . '/vendor/bower/jquery/dist/jquery.min.js';
        $this->vendorNamedLibrary['css']['bootstrap'] = $this->scriptUrl . '/vendor/bower/bootstrap/dist/css/bootstrap.min.css';
        $this->vendorNamedLibrary['js']['bootstrap'] = $this->scriptUrl . '/vendor/bower/bootstrap/dist/js/bootstrap.min.js';
        $this->vendorNamedLibrary['css']['fa'] = $this->scriptUrl . '/vendor/bower/components-font-awesome/css/font-awesome.min.css';
        $this->vendorNamedLibrary['js']['jquery-ui'] = $this->scriptUrl . '/vendor/bower/jquery-ui/jquery-ui.min.js';
        $this->vendorNamedLibrary['css']['jquery-ui'] = $this->scriptUrl . '/vendor/bower/jquery-ui/themes/base/jquery-ui.min.css';

        $themeAll = App::$Properties->get('theme');
        $this->currentViewUrl = $this->scriptUrl . '/Apps/View/' . env_name . '/' . $themeAll[env_name];
    }

    /**
     * @param string $type
     * @param string $name
     * @return string|null
     */
    public function getVendor($type, $name)
    {
        return $this->vendorNamedLibrary[$type][$name];
    }

    /**
     * Set custom library $link by $type
     * @param string $type
     * @param string $link
     */
    public function setCustomLibrary($type, $link)
    {
        $this->codeCustomLibrary[$type][] = $link;
    }

    /**
     * Get custom library array by type
     * @param string $type
     * @return array|null
     */
    public function getCustomLibraryArray($type)
    {
        return $this->codeCustomLibrary[$type];
    }

    /**
     * @param string $type
     * @param string $code
     * @return bool
     */
    public function addPlainCode($type, $code)
    {
        $allowed = ['css', 'js'];
        if (!Arr::in($type, $allowed)) {
            return false;
        }

        $this->plainCode[$type][] = $code;
        return true;
    }

    /**
     * Get plain code build container as string
     * @param string $type
     * @return null|string
     */
    public function getPlainCode($type)
    {
        return $this->plainCode[$type];
    }


}