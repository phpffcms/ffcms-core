<?php

namespace Ffcms\Core\Template;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Traits\Singleton;

class Variables
{
    use Singleton;

    protected $globalVars;

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
        if (!Object::isArray($array)) {
            return;
        }
        foreach ($array as $var => $value) {
            $this->globalVars[$var] = Object::isString($value) ? App::$Security->strip_tags($value) : $value;
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
}