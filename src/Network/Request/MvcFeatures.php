<?php

namespace Ffcms\Core\Network\Request;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Trait MvcFeatures. Mvc features for Request networking
 * @package Ffcms\Core\Network\Request
 */
trait MvcFeatures
{
    protected $controller;
    protected $action;
    protected $args;

    /**
     * Check if current url in redirect map
     * @return void
     */
    private function searchRedirect(): void
    {
        // calculated depend of language
        $pathway = $this->getPathInfo();
        /** @var array $routing */
        $routing = App::$Properties->getAll('Routing');

        if (!Any::isArray($routing) || !isset($routing['Redirect']) || !Any::isArray($routing['Redirect'])) {
            return;
        }

        // check if source uri is key in redirect target map
        if (array_key_exists($pathway, $routing['Redirect'])) {
            $target = $this->getSchemeAndHttpHost(); // . $this->getBasePath() . '/' . rtrim($routing['Redirect'][$pathway], '/');
            if ($this->getBasePath() !== null && !Str::likeEmpty($this->getBasePath())) {
                $target .= '/' . $this->getBasePath();
            }
            $target .= rtrim($routing['Redirect'][$pathway], '/');
            $redirect = new RedirectResponse($target);
            $redirect->send();
            exit();
        }
    }

    /**
     * Working with path array data
     * @param array|null $pathArray
     * @return void
     */
    private function setPathdata(?array $pathArray = null): void
    {
        if (!Any::isArray($pathArray) || count($pathArray) < 1) {
            return;
        }

        // extract controller info from full path array
        $this->controller = ucfirst(Str::lowerCase(array_shift($pathArray)));
        if (count($pathArray) > 0) {
            // extract action
            $this->action = ucfirst(Str::lowerCase(array_shift($pathArray)));

            // safe other parts to arguments if exist
            if (count($pathArray) > 0) {
                $this->args = array_map(function($in){
                    return Any::isStr($in) ? urldecode($in) : $in;
                }, $pathArray);
            }
        }
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
     * Get arguments from pathway
     * @return array
     */
    public function getArguments(): array
    {
        return (array)$this->args;
    }
}
