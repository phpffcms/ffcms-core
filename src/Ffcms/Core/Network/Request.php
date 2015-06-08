<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use Ffcms\Core\App;

class Request extends FoundationRequest
{

    protected $language;
    protected $languageInPath = false;

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
            // maybe its a language domain alias?
            if (Object::isArray(App::$Property->get('languageDomainAlias'))) {
                $domainAlias = App::$Property->get('languageDomainAlias');
                if ($domainAlias[$this->getHost()] !== null && String::length($domainAlias[$this->getHost()]) > 0) {
                    $this->language = $domainAlias[$this->getHost()];
                }
            } else {
                // try to find language in pathway
                foreach (App::$Property->get('languages') as $lang) {
                    if (String::startsWith('/' . $lang, $this->getPathInfo())) {
                        $this->language = $lang;
                        $this->languageInPath = true;
                    }
                }

                // try to find in ?lang get
                if ($this->language === null && Arr::in($this->query->get('lang'), App::$Property->get('languages'))) {
                    $this->language = $this->query->get('lang');
                }

                // language still not defined?!
                if ($this->language === null) {
                    $userLang = App::$Property->get('baseLanguage');
                    $browserAccept = $this->getLanguages();
                    if (Object::isArray($browserAccept) && count($browserAccept) > 0) {
                        foreach ($browserAccept as $bLang) {
                            if (Arr::in($bLang, App::$Property->get('languages'))) {
                                $userLang = $bLang;
                                break; // stop calculating, language is founded in priority
                            }
                        }
                    }

                    $response = new Redirect($this->getSchemeAndHttpHost() . $this->basePath . '/' . $userLang . $this->getPathInfo());
                    $response->send();
                    exit();
                }
            }
        }

        $pathway = $this->getPathInfo(); // calculated depend of language
        $pathArray = explode('/', $pathway);

        $this->controller = ucfirst(strtolower($pathArray[1]));
        $this->action = ucfirst(strtolower($pathArray[2]));
        $this->argumentId = strtolower($pathArray[3]);
        $this->argumentAdd = strtolower($pathArray[4]);

        if ($this->action == null) { // can be null or string(0)""
            $this->action = 'Index';
        }

        if ($this->controller == null) {
            $this->controller = 'Main';
        }

        if ($this->controller === 'Main' && env_name === 'Front') { // can be null or string(0)""
            $defaultRoute = App::$Property->get('siteIndex');
            list($this->controller, $this->action) = explode('::', trim($defaultRoute, '/'));
        }
    }

    /**
     * Get pathway as string
     * @return string
     */
    public function getPathInfo()
    {
        return $this->languageInPath ? String::substr(parent::getPathInfo(), String::length($this->language) + 1) : parent::getPathInfo();
    }

    /**
     * Get current language
     * @return string|null
     */
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
     * @return string|null
     */
    public function getID()
    {
        return $this->argumentId;
    }

    /**
     * Get current $add argument for controller action
     * @return string|null
     */
    public function getAdd()
    {
        return $this->argumentAdd;
    }

}