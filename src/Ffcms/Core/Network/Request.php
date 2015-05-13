<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use Ffcms\Core\App;

class Request extends FoundationRequest
{

    protected $language;

    // fast access for controller building
    protected $controller;
    protected $action;
    protected $argumentId;
    protected $argumentAdd;

    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->afterInitialize();
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array $query The GET parameters
     * @param array $request The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies The COOKIE parameters
     * @param array $files The FILES parameters
     * @param array $server The SERVER parameters
     * @param string $content The raw body data
     *
     * @api
     */
    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);

        $basePath = trim(App::$Property->get('basePath'), '/');
        if ($basePath !== null && String::length($basePath) > 0) {
            $basePath = '/' . $basePath;
        }

        if (!defined('env_no_uri') || env_no_uri === false) {
            $basePath .= '/' . strtolower(env_name);
        }

        // we never try to use path's without friendly url's
        $this->basePath = $this->baseUrl = $basePath;
    }

    protected function afterInitialize()
    {
        if (App::$Property->get('multiLanguage')) { // multi-language is enabled?
            foreach (App::$Property->get('languages') as $lang) {
                if (String::startsWith('/' . $lang, $this->getPathInfo())) {
                    $this->language = $lang;
                }
            }
            // language still not defined?!
            if ($this->language === null) {
                $userLang = App::$Property->get('baseLanguage');
                if (Object::isArray($this->getLanguages()) && count($this->getLanguages()) > 0) {
                    $userLang = array_shift($this->getLanguages());
                }

                $response = new Redirect($this->getSchemeAndHttpHost() . $this->basePath . '/' . $userLang . $this->getPathInfo());
                $response->send();
                exit();
            }
        }

        $pathway = $this->getPathInfo(); // calculated depend of language
        $pathArray = explode('/', $pathway);

        $this->controller = ucfirst(strtolower($pathArray[1]));
        $this->action = ucfirst(strtolower($pathArray[2]));
        $this->argumentId = strtolower($pathArray[3]);
        $this->argumentAdd = strtolower($pathArray[4]);

        if ($this->action == null) { // can be null or string(0)""
            $this->action = 'index';
        }

        if ($this->controller == null) { // can be null or string(0)""
            $defaultRoute = App::$Property->get('siteIndex');
            list($this->controller, $this->action) = explode('::', trim($defaultRoute, '/'));
        }
    }

    public function getPathInfo()
    {
        return $this->language === null ? parent::getPathInfo() : String::substr(parent::getPathInfo(), String::length($this->language) + 1);
    }

    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get current controller name
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get current controller action() name
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get current $id argument for controller action
     * @return string
     */
    public function getID()
    {
        return $this->argumentId;
    }

    /**
     * Get current $add argument for controller action
     * @return string
     */
    public function getAdd()
    {
        return $this->argumentAdd;
    }

}