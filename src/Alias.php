<?php

namespace Ffcms\Core;

use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;

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
     * Current app basic URL without language path
     * @var string
     */
    public $baseUrlNoLang;

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
     * Alias constructor. Build alias properties for system data to provide fast-access from apps and other places.
     */
    public function __construct()
    {
        // make alias for view pathway
        $this->currentViewPath = App::$View->getCurrentPath();

        // make alias for baseUrl, script url and domain
        $this->baseDomain = App::$Request->getHttpHost();
        if (Str::likeEmpty($this->baseDomain)) {
            $this->baseDomain = App::$Properties->get('baseDomain');
        }
        // build script url
        $this->scriptUrl = App::$Request->getScheme() . '://' . $this->baseDomain;
        if (App::$Properties->get('basePath') !== '/') {
            $this->scriptUrl .= rtrim(App::$Properties->get('basePath'), '/');
        }
        // build base url (with current used interface path slug)
        $this->baseUrl = $this->scriptUrl;
        if (App::$Request->getInterfaceSlug() !== null) {
            $this->baseUrl .= App::$Request->getInterfaceSlug();
        }

        $this->baseUrlNoLang = $this->baseUrl;
        if (App::$Request->languageInPath() && App::$Request->getLanguage() !== null) {
            $this->baseUrl .= '/' . App::$Request->getLanguage();
        }

        // @todo: add cron initiation from user if enabled -> move to layout
        //if ((bool)App::$Properties->get('userCron') && env_name === 'Front') {
        //    $this->addPlainCode('js', 'if(Math.random() > 0.8) { $.get("' . $this->scriptUrl . '/cron.php?rand=" + Math.random()); }');
        //}

        $themeAll = App::$Properties->get('theme');
        if (!isset($themeAll[env_name]) || Str::length($themeAll[env_name]) < 1) {
            $themeAll[env_name] = 'default';
        }
        $this->currentViewUrl = $this->scriptUrl . '/Apps/View/' . env_name . '/' . $themeAll[env_name];
    }
}
