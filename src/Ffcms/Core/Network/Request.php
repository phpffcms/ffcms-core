<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\String;

/**
 * Class Request
 * Class to work with input website data - request params and headers.
 * @package Core\Network
 */
class Request
{

    protected static $pathway;
    protected static $controller;
    protected static $action;
    protected static $id;
    protected static $add;

    protected static $language;


    public $baseDomain;
    public $baseUrl;
    public $scriptUrl;

    public function __construct()
    {
        // preparing url
        $raw_uri = urldecode($_SERVER['REQUEST_URI']);
        if ($get_pos = strpos($raw_uri, '?')) {
            $raw_uri = substr($raw_uri, 0, $get_pos);
        }
        $pathway = ltrim($raw_uri, '/');

        // set default url's
        $this->baseDomain = $_SERVER['SERVER_NAME'];
        $this->baseUrl = $this->scriptUrl = static::getProtocol() . '://' . $this->baseDomain . App::$Property->get('basePath');
        // interface additional for base url
        if (defined('workground') && workground !== 'Front') {
            $this->baseUrl .= strtolower(workground) . '/';
        }

        $language_undefined = false;
        $mean_pathway = App::$Property->get('basePath');
        if (workground !== 'Front') {
            $mean_pathway .= '/' . strtolower(workground);
        }
        if (App::$Property->get('multiLanguage')) { // does multilang enabled?
            foreach (App::$Property->get('languages') as $lang) { // extract current language from pathway
                $build_pathway = ltrim($mean_pathway . '/' . $lang, '/');
                if (String::startsWith($build_pathway, $pathway)) { // compare "required" and real paths to detect language
                    self::$language = $lang;
                    $mean_pathway = $build_pathway; // is "ok" test, let use this pathway
                }
            }
            if (self::$language === null) { // language is not defined? mark to redirect
                $language_undefined = true;
            } else {
                // add language to baseUrl
                $this->baseUrl .= self::$language . '/';
            }
        } else { // set current language from configs
            self::$language = App::$Property->get('singleLanguage');
        }
        // set worker pathway
        self::$pathway = ltrim(String::substr($pathway, String::length(ltrim($mean_pathway, '/'))), '/');

        // if language is required and undefined - redirect to basic lang version
        if ($language_undefined === true) {
            Response::redirect($this->baseUrl . App::$Property->get('baseLanguage') . '/', true);
        }

        $uri_split = explode('/', self::$pathway);

        // write mvc request data
        self::$controller = strtolower($uri_split[0]);
        self::$action = strtolower($uri_split[1]);
        self::$id = strtolower($uri_split[2]);
        self::$add = strtolower($uri_split[3]);

        if (self::$action == null) {
            self::$action = 'index';
        }

        if (self::$controller == null || self::$pathway == null) {
            $defaultRoute = App::$Property->get('siteIndex');
            list(self::$controller, self::$action) = explode('::', trim($defaultRoute, '/'));
        }
    }

    /**
     * Get request based protocol type (http/https)
     * @return string
     */
    public static function getProtocol()
    {
        $proto = 'http';
        $cf_proxy = json_decode($_SERVER['HTTP_CF_VISITOR']);
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
            || isset($_SERVER['HTTP_CF_VISITOR']) && $cf_proxy->{'scheme'} == 'https') {
            $proto = 'https';
        }
        return $proto;
    }

    /**
     * Get current pathway as string
     * @return string
     */
    public static function getPathway()
    {
        return self::$pathway;
    }

    /**
     * Get current language
     * @return string|null
     */
    public static function getLanguage()
    {
        return self::$language;
    }

    /**
     * Get current controller name
     * @return string
     */
    public function getController()
    {
        return ucfirst(strtolower(self::$controller));
    }

    /**
     * Get current controller action() name
     * @return string
     */
    public function getAction()
    {
        return ucfirst(strtolower(self::$action));
    }

    /**
     * Get current $id argument for controller action
     * @return string
     */
    public function getID()
    {
        return strtolower(self::$id);
    }

    /**
     * Get current $add argument for controller action
     * @return string
     */
    public function getAdd()
    {
        return strtolower(self::$add);
    }

    /**
     * Get data from global $_POST with $key. Like $_POST[$key]
     * @param string|null $key
     * @return string|null
     */
    public function post($key = null)
    {
        return $key === null ? $_POST : $_POST[$key];
    }

    /**
     * Get data from global $_GET with $key according urldecode(). Like urldecode($_GET[$key])
     * @param string $key
     * @return string|null
     */
    public function get($key = null)
    {
        return $key === null ? $_GET : urldecode($_GET[$key]);
    }
}