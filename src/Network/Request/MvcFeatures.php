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
    protected $argumentId;
    protected $argumentAdd;


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

        // check if array length is more then 4 basic elements and slice it recursive
        if (count($pathArray) > 4) {
            $this->setPathdata(array_slice($pathArray, 0, 4));
            return;
        }

        // Switch path array as reverse without break point! Caution: drugs inside!
        switch (count($pathArray)) {
            case 4:
                $this->argumentAdd = $pathArray[3];
            // no break
            case 3:
                $this->argumentId = $pathArray[2];
            // no break
            case 2:
                $this->action = ucfirst(Str::lowerCase($pathArray[1]));
            // no break
            case 1:
                $this->controller = ucfirst(Str::lowerCase($pathArray[0]));
                break;
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
}
