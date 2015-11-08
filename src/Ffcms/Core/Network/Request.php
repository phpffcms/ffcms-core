<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Helper\Type\Str;
use Predis\Command\StringIncrement;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use Ffcms\Core\App;

class Request extends FoundationRequest
{
    protected $language;
    protected $languageInPath = false;

    // special variable for route aliasing
    protected $aliasPathTarget = false;
    // special variable for route callback binding
    protected $callbackClass = false;

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

        $basePath = trim(App::$Properties->get('basePath'), '/');
        if ($basePath !== null && Str::length($basePath) > 0) {
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
        // multi-language is enabled?
        if (App::$Properties->get('multiLanguage') === true) {
            // maybe its a language domain alias?
            if (Object::isArray(App::$Properties->get('languageDomainAlias'))) {
                /** @var array $domainAlias */
                $domainAlias = App::$Properties->get('languageDomainAlias');
                if (Object::isArray($domainAlias) && $domainAlias[$this->getHost()] !== null && Str::length($domainAlias[$this->getHost()]) > 0) {
                    $this->language = $domainAlias[$this->getHost()];
                }
            } else {
                // try to find language in pathway
                foreach (App::$Properties->get('languages') as $lang) {
                    if (Str::startsWith('/' . $lang, $this->getPathInfo())) {
                        $this->language = $lang;
                        $this->languageInPath = true;
                    }
                }

                // try to find in ?lang get
                if ($this->language === null && Arr::in($this->query->get('lang'), App::$Properties->get('languages'))) {
                    $this->language = $this->query->get('lang');
                }

                // language still not defined?!
                if ($this->language === null) {
                    $userLang = App::$Properties->get('singleLanguage');
                    $browserAccept = $this->getLanguages();
                    if (Object::isArray($browserAccept) && count($browserAccept) > 0) {
                        foreach ($browserAccept as $bLang) {
                            if (Arr::in($bLang, App::$Properties->get('languages'))) {
                                $userLang = $bLang;
                                break; // stop calculating, language is founded in priority
                            }
                        }
                    }

                    $queryString = null;
                    if (count($this->query->all()) > 0) {
                        $firstQ = true;
                        foreach ($this->query->all() as $qKey => $qVal) {
                            $queryString .= $firstQ ? '?' : '&';
                            $queryString .= $qKey . '=' . $qVal;
                            $firstQ = false;
                        }

                    }

                    $response = new Redirect($this->getSchemeAndHttpHost() . $this->basePath . '/' . $userLang . $this->getPathInfo() . $queryString);
                    $response->send();
                    exit();
                }
            }
        } else { // multi-language is disabled? Use default language
            $this->language = App::$Properties->get('singleLanguage');
        }

        // calculated depend of language
        $pathway = $this->getPathInfo();
        $routing = App::$Properties->getAll('Routing');

        // try to find static routing alias
        /** @var array $aliasMap */
        $aliasMap = $routing['Alias'][env_name];
        if (Object::isArray($aliasMap) && array_key_exists($pathway, $aliasMap)) {
            $pathway = $aliasMap[$pathway];
            $this->aliasPathTarget = $pathway;
        }

        // check if pathway is the same with target and redirect to source from static routing
        if (Object::isArray($aliasMap) && Arr::in($this->getPathInfo(), $aliasMap)) {
            $source = array_search($this->getPathInfo(), $aliasMap);
            $targetUri = $this->getSchemeAndHttpHost() . $this->getBasePath() . '/';
            if (App::$Properties->get('multiLanguage') === true) {
                $targetUri .= $this->language . '/';
            }
            $targetUri .= ltrim($source, '/');
            $response = new Redirect($targetUri);
            $response->send();
            exit();
        }

        // define data from pathway
        $this->setPathdata(explode('/', trim($pathway, '/')));

        if ($this->action == null) { // can be null or string(0)""
            $this->action = 'Index';
        }

        // empty or contains backslashes? set to main
        if ($this->controller == null || Str::contains('\\', $this->controller)) {
            $this->controller = 'Main';
        }

        // get callback routing map
        $callbackMap = $routing['Callback'][env_name];
        if (isset($callbackMap[$this->controller])) {
            $callbackClass = $callbackMap[$this->controller];
            // check if rule for current controller is exist
            if ($callbackClass !== null) {
                $this->callbackClass = $callbackClass;
            }
        }
    }

    /**
     * Working with path array data
     * @param array|null $pathArray
     */
    private function setPathdata(array $pathArray = null)
    {
        if (!Object::isArray($pathArray) || count($pathArray) < 1) {
            return;
        }

        /**
         * Switch path array as reverse without break point! Caution: drugs inside!
         */
        switch (count($pathArray)) {
            case 4:
                $this->argumentAdd = Str::lowerCase($pathArray[3]);
            case 3:
                $this->argumentId = Str::lowerCase($pathArray[2]);
            case 2:
                $this->action = ucfirst(Str::lowerCase($pathArray[1]));
            case 1:
                $this->controller = ucfirst(Str::lowerCase($pathArray[0]));
                break;
        }
        return;
    }

    /**
     * Get pathway as string
     * @return string
     */
    public function getPathInfo()
    {
        $route = $this->languageInPath ? Str::substr(parent::getPathInfo(), Str::length($this->language) + 1) : parent::getPathInfo();
        if (!Str::startsWith('/', $route)) {
            $route = '/' . $route;
        }
        return $route;
    }

    public function languageInPath()
    {
        return $this->languageInPath;
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
        return urldecode($this->argumentId);
    }

    /**
     * Get current $add argument for controller action
     * @return string|null
     */
    public function getAdd()
    {
        return urldecode($this->argumentAdd);
    }

    /**
     * Get callback class alias if exist
     * @return bool|string
     */
    public function getCallbackAlias()
    {
        return $this->callbackClass;
    }

    /**
     * Get static alias of pathway if exist
     * @return bool
     */
    public function getStaticAlias()
    {
        return $this->aliasPathTarget;
    }

    /**
     * Check if current request is aliased by dynamic or static rule
     * @return bool
     */
    public function isPathInjected()
    {
        return $this->callbackClass !== false || $this->aliasPathTarget !== false;
    }

    /**
     * Get pathway without current controller/action path
     * @return string
     */
    public function getPathWithoutControllerAction()
    {
        $path = trim($this->getPathInfo(), '/');
        if ($this->aliasPathTarget !== false) {
            $path = trim($this->aliasPathTarget, '/');
        }
        $pathArray = explode('/', $path);
        if ($pathArray[0] === Str::lowerCase($this->getController())) {
            // unset controller
            array_shift($pathArray);
            if ($pathArray[0] === Str::lowerCase($this->getAction())) {
                // unset action
                array_shift($pathArray);
            }
        }
        return trim(implode('/', $pathArray), '/');
    }

    /**
     * Get current full request URI
     * @return string
     */
    public function getFullUrl()
    {
        return $this->getSchemeAndHttpHost() . $this->getRequestUri();
    }

}