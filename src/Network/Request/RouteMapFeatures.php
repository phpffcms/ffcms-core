<?php

namespace Ffcms\Core\Network\Request;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Trait RouteMapFeatures. Process alias and callback route address bindings
 * @package Ffcms\Core\Network\Request
 */
trait RouteMapFeatures
{
    // special variable for route aliasing
    protected $aliasPathTarget;
    // special variable for route callback binding
    protected $callbackClass;

    /**
     * Build static and dynamic path aliases for working set
     * @return void
     */
    private function runRouteBinding(): void
    {
        // calculated depend of language
        $pathway = $this->getPathInfo();
        /** @var array $routing */
        $routing = App::$Properties->getAll('Routing');

        // try to work with static aliases
        if (Any::isArray($routing) && isset($routing['Alias'], $routing['Alias'][env_name])) {
            $pathway = $this->findStaticAliases($routing['Alias'][env_name], $pathway);
        }

        $this->setPathdata(explode('/', trim($pathway, '/')));

        // set default controller and action for undefined data
        if (!$this->action) {
            $this->action = 'Index';
        }

        // empty or contains backslashes? set to main
        if (!$this->controller || Str::contains('\\', $this->controller)) {
            $this->controller = 'Main';
        }

        // find callback injection in routing configs (calculated in App::run())
        if (Any::isArray($routing) && isset($routing['Callback'], $routing['Callback'][env_name])) {
            $this->findDynamicCallbacks($routing['Callback'][env_name], $this->controller);
        }
    }

    /**
     * Prepare static pathway aliasing for routing
     * @param array|null $map
     * @param string|null $pathway
     * @return string|null
     */
    private function findStaticAliases(?array $map = null, ?string $pathway = null): ?string
    {
        if (!$map) {
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

            $redirect = new RedirectResponse($url);
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
     * @return void
     */
    private function findDynamicCallbacks(array $map = null, ?string $controller = null): void
    {
        if (!$map) {
            return;
        }

        // try to find global callback for this controller slug
        if (array_key_exists($controller, $map)) {
            $class = (string)$map[$controller];
            if (!Str::likeEmpty($class)) {
                $this->callbackClass = $class;
            }
        }
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
}
