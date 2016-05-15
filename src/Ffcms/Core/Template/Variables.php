<?php

namespace Ffcms\Core\Template;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Traits\Singleton;

/**
 * Class Variables. Support singleton-based class to store data from any place as a variables for view's.
 * @package Ffcms\Core\Template
 */
class Variables
{
    use Singleton;

    protected $globalVars = [];

    /**
     * Set global variable for Views
     * @param string $var
     * @param string|array $value
     * @param bool $html
     */
    public function setGlobal($var, $value, $html = false)
    {
        $this->globalVars[$var] = $html ? App::$Security->secureHtml($value) : App::$Security->strip_tags($value);
    }

    /**
     * Set global variable from key=>value array (key = varname)
     * @param array $array
     */
    public function setGlobalArray(array $array)
    {
        if (!Obj::isArray($array)) {
            return;
        }
        foreach ($array as $var => $value) {
            $this->globalVars[$var] = Obj::isString($value) ? App::$Security->strip_tags($value) : $value;
        }
    }

    /**
     * Get all global variables as array
     * @return array|null
     */
    public function getGlobalsArray()
    {
        return $this->globalVars;
    }

    /**
     * Get all global variables as stdObject
     * @return object
     */
    public function getGlobalsObject()
    {
        return (object)$this->globalVars;
    }

    /**
     * Check if global variable isset
     * @param string $var
     * @return bool
     */
    public function issetGlobal($var)
    {
        return array_key_exists($var, $this->globalVars);
    }
}