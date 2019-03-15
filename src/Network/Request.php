<?php

namespace Ffcms\Core\Network;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Templex\Url\UrlRepository;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;

/**
 * Class Request. Classic implementation of httpfoundation.request with smooth additions and changes which allow
 * working as well as in ffcms.
 * @package Ffcms\Core\Network
 */
class Request extends FoundationRequest
{
    use Request\MvcFeatures, Request\MultiLanguageFeatures, Request\RouteMapFeatures;

    /**
     * Request constructor.
     * @inheritdoc
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->searchRedirect();
        $this->runMultiLanguage();
        $this->processBalancerProxies();
        $this->loadTrustedProxies();
        $this->runRouteBinding();
        $this->setTemplexFeatures();
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

    private function processBalancerProxies()
    {
        // fix cloudflare forwarding
        $protos = $this->headers->get('cf-visitor', false);
        if ($protos) {
            $data = json_decode($protos);
            if ($data && $data->scheme && (string)$data->scheme === 'https') {
                $this->server->set('HTTPS', 'on');
            }
        }
    }

    /**
     * Set trusted proxies from configs
     */
    private function loadTrustedProxies()
    {
        $proxies = App::$Properties->get('trustedProxy');
        if (!$proxies || Str::likeEmpty($proxies)) {
            return;
        }

        $pList = explode(',', $proxies);
        $resultList = [];
        foreach ($pList as $proxy) {
            $resultList[] = trim($proxy);
        }
        self::setTrustedProxies($resultList, self::HEADER_X_FORWARDED_ALL);
    }

    /**
     * Get pathway as string
     * @return string
     */
    public function getPathInfo()
    {
        $route = $this->languageInPath ? Str::sub(parent::getPathInfo(), Str::length($this->language) + 1) : parent::getPathInfo();
        if (!Str::startsWith('/', $route)) {
            $route = '/' . $route;
        }
        return $route;
    }

    /**
     * Get pathway without current controller/action path
     * @return string
     */
    public function getPathWithoutControllerAction(): ?string
    {
        $path = trim($this->getPathInfo(), '/');
        if ($this->aliasPathTarget !== null) {
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

    /**
     * Set templex template engine URL features
     * @return void
     */
    private function setTemplexFeatures(): void
    {
        $url = $this->getSchemeAndHttpHost();
        $sub = null;
        if ($this->getInterfaceSlug() && Str::length($this->getInterfaceSlug()) > 0) {
            $sub = $this->getInterfaceSlug() . '/';
        }

        if ($this->languageInPath()) {
            $sub .= $this->getLanguage();
        }

        UrlRepository::factory()->setUrlAndSubdir($url, $sub);
    }
}
