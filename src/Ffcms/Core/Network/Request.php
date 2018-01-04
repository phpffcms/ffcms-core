<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

/**
 * Class Request. Classic implementation of httpfoundation.request with smooth additions and changes which allow
 * working as well as in ffcms.
 * @package Ffcms\Core\Network
 */
class Request extends FoundationRequest
{
    protected $language;
    protected $languageInPath = false;

    // special variable for route aliasing
    protected $aliasPathTarget;
    // special variable for route callback binding
    protected $callbackClass;

    // fast access for controller building
    protected $controller;
    protected $action;
    protected $argumentId;
    protected $argumentAdd;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->findRedirect();
        $this->runMultiLanguage();
        $this->runPathBinding();
        $this->loadTrustedProxies();
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

    /**
     * Build multi language pathway binding.
     */
    private function runMultiLanguage()
    {
        // multi-language is disabled, use default language
        if (App::$Properties->get('multiLanguage') !== true) {
            $this->language = App::$Properties->get('singleLanguage');
        } else {
            // maybe its a language domain alias?
            if (Any::isArray(App::$Properties->get('languageDomainAlias'))) {
                /** @var array $domainAlias */
                $domainAlias = App::$Properties->get('languageDomainAlias');
                if (Any::isArray($domainAlias) && !Str::likeEmpty($domainAlias[$this->getHost()])) {
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
                    if (Any::isArray($browserAccept) && count($browserAccept) > 0) {
                        foreach ($browserAccept as $bLang) {
                            if (Arr::in($bLang, App::$Properties->get('languages'))) {
                                $userLang = $bLang;
                                break; // stop calculating, language is founded in priority
                            }
                        }
                    }

                    // parse query string
                    $queryString = null;
                    if (count($this->query->all()) > 0) {
                        $queryString = '?' . http_build_query($this->query->all());
                    }

                    // build response with redirect to language-based path
                    $response = new Redirect($this->getSchemeAndHttpHost() . $this->basePath . '/' . $userLang . $this->getPathInfo() . $queryString);
                    $response->send();
                    exit();
                }
            }
        }
    }

    /**
     * Build static and dynamic path aliases for working set
     */
    private function runPathBinding()
    {
        // calculated depend of language
        $pathway = $this->getPathInfo();
        /** @var array $routing */
        $routing = App::$Properties->getAll('Routing');

        // try to work with static aliases
        if (Any::isArray($routing) && isset($routing['Alias'], $routing['Alias'][env_name]))
            $pathway = $this->findStaticAliases($routing['Alias'][env_name], $pathway);

        $this->setPathdata(explode('/', trim($pathway, '/')));

        // set default controller and action for undefined data
        if (!$this->action)
            $this->action = 'Index';

        // empty or contains backslashes? set to main
        if (!$this->controller || Str::contains('\\', $this->controller))
            $this->controller = 'Main';

        // find callback injection in routing configs (calculated in App::run())
        if (Any::isArray($routing) && isset($routing['Callback'], $routing['Callback'][env_name])) {
            $this->findDynamicCallbacks($routing['Callback'][env_name], $this->controller);
        }
    }

    /**
     * Check if current url in redirect map
     */
    private function findRedirect()
    {
        // calculated depend of language
        $pathway = $this->getPathInfo();
        /** @var array $routing */
        $routing = App::$Properties->getAll('Routing');

        if (!Any::isArray($routing) || !isset($routing['Redirect']) || !Any::isArray($routing['Redirect']))
            return;

        // check if source uri is key in redirect target map
        if (array_key_exists($pathway, $routing['Redirect'])) {
            $target = $this->getSchemeAndHttpHost(); // . $this->getBasePath() . '/' . rtrim($routing['Redirect'][$pathway], '/');
            if ($this->getBasePath() !== null && !Str::likeEmpty($this->getBasePath())) {
                $target .= '/' . $this->getBasePath();
            }
            $target .= rtrim($routing['Redirect'][$pathway], '/');
            $redirect = new Redirect($target);
            $redirect->send();
            exit();
        }
    }

    /**
     * Prepare static pathway aliasing for routing
     * @param array|null $map
     * @param string|null $pathway
     * @return string
     */
    private function findStaticAliases(array $map = null, $pathway = null)
    {
        if ($map === null) {
            return $pathway;
        }

        // current pathway is found as "old path" (or alias target). Make redirect to new pathway.
        if (Arr::in($pathway, $map)) {
            // find "new path" as binding uri slug
            $binding = array_search($pathway, $map, true);
            // build url to redirection
            $url = $this->getSchemeAndHttpHost() . $this->getBasePath() . '/';
            if (App::$Properties->get('multiLanguage')) {
                $url .= $this->language . '/';
            }
            $url .= ltrim($binding, '/');

            $redirect = new Redirect($url);
            $redirect->send();
            exit();
        }

        // current pathway request is equal to path alias. Set alias to property.
        if (array_key_exists($pathway, $map)) {
            $pathway = $map[$pathway];
            $this->aliasPathTarget = $pathway;
        }

        return $pathway;
    }

    /**
     * Prepare dynamic callback data for routing
     * @param array|null $map
     * @param string|null $controller
     */
    private function findDynamicCallbacks(array $map = null, ?string $controller = null)
    {
        if ($map === null)
            return;

        // try to find global callback for this controller slug
        if (array_key_exists($controller, $map)) {
            $class = (string)$map[$controller];
            if (!Str::likeEmpty($class)) {
                $this->callbackClass = $class;
            }
        }
    }

    /**
     * Set trusted proxies from configs
     */
    private function loadTrustedProxies()
    {
        $proxies = App::$Properties->get('trustedProxy');
        if ($proxies === null || Str::likeEmpty($proxies))
            return;

        $pList = explode(',', $proxies);
        $resultList = [];
        foreach ($pList as $proxy) {
            $resultList[] = trim($proxy);
        }
        self::setTrustedProxies($resultList);
    }

    /**
     * Working with path array data
     * @param array|null $pathArray
     */
    private function setPathdata(?array $pathArray = null)
    {
        if (!Any::isArray($pathArray) || count($pathArray) < 1)
            return;

        // check if array length is more then 4 basic elements and slice it recursive
        if (count($pathArray) > 4) {
            $this->setPathdata(array_slice($pathArray, 0, 4));
            return;
        }

        // Switch path array as reverse without break point! Caution: drugs inside!
        switch (count($pathArray)) {
            case 4:
                $this->argumentAdd = $pathArray[3];
            case 3:
                $this->argumentId = $pathArray[2];
            case 2:
                $this->action = ucfirst(Str::lowerCase($pathArray[1]));
            case 1:
                $this->controller = ucfirst(Str::lowerCase($pathArray[0]));
                break;
        }
    }

    /**
     * Get pathway as string
     * @return string
     */
    public function getPathInfo()
    {
        $route = $this->languageInPath ? Str::sub(parent::getPathInfo(), Str::length($this->language) + 1) : parent::getPathInfo();
        if (!Str::startsWith('/', $route))
            $route = '/' . $route;
        return $route;
    }

    /**
     * Check if language used in path
     * @return bool
     */
    public function languageInPath()
    {
        return $this->languageInPath;
    }

    /**
     * Get current language
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Set current language
     * @param string $lang
     * @return bool
     */
    public function setLanguage($lang): bool
    {
        if (Arr::in($lang, App::$Properties->get('languages'))) {
            $this->language = $lang;
            return true;
        }

        return false;
    }

    /**
     * Get current controller name
     * @return string
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * Get current controller action() name
     * @return string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Get current $id argument for controller action
     * @return string|null
     */
    public function getID(): ?string
    {
        return urldecode($this->argumentId);
    }

    /**
     * Set current controller name
     * @param string $name
     */
    public function setController($name): void
    {
        $this->controller = $name;
    }

    /**
     * Set current action value
     * @param string $name
     */
    public function setAction($name): void
    {
        $this->action = $name;
    }

    /**
     * Set current id argument value
     * @param mixed $name
     */
    public function setId($name): void
    {
        $this->argumentId = $name;
    }

    /**
     * Set current add argument value
     * @param mixed $name
     */
    public function setAdd($name): void
    {
        $this->argumentAdd = $name;
    }

    /**
     * Get current $add argument for controller action
     * @return string|null
     */
    public function getAdd(): ?string
    {
        return urldecode($this->argumentAdd);
    }

    /**
     * Get callback class alias if exist
     * @return null|string
     */
    public function getCallbackAlias(): ?string
    {
        return $this->callbackClass;
    }

    /**
     * Get static alias of pathway if exist
     * @return null|string
     */
    public function getStaticAlias(): ?string
    {
        return $this->aliasPathTarget;
    }

    /**
     * Check if current request is aliased by dynamic or static rule
     * @return bool
     */
    public function isPathInjected(): bool
    {
        return $this->callbackClass !== null || $this->aliasPathTarget !== null;
    }

    /**
     * Get pathway without current controller/action path
     * @return string
     */
    public function getPathWithoutControllerAction(): ?string
    {
        $path = trim($this->getPathInfo(), '/');
        if ($this->aliasPathTarget !== null)
            $path = trim($this->aliasPathTarget, '/');

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
    public function getFullUrl(): string
    {
        return $this->getSchemeAndHttpHost() . $this->getRequestUri();
    }

    /**
     * Get base path from current environment without basePath of subdirectories
     * @return string
     */
    public function getInterfaceSlug(): string
    {
        $path = $this->getBasePath();
        $subDir = App::$Properties->get('basePath');
        if ($subDir !== '/') {
            $offset = (int)Str::length($subDir);
            $path = Str::sub($path, --$offset);
        }
        return $path;
    }

}