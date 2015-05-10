<?php

namespace Ffcms\Core\Template;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Object;

class Variables
{
    protected static $instance;

    protected $globalVars;
    protected $globalError;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Set global variable for Views
     * @param string $var
     * @param string $value
     * @param bool $html
     */
    public function setGlobal($var, $value, $html = false)
    {
        if (Object::isString($value)) {
            $this->globalVars[$var] = $html ? App::$Security->purifier()->purify($value) : App::$Security->strip_tags($value);
        }
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

    /**
     * Set global error
     * @param string $text
     */
    public function setError($text)
    {
        $this->globalError = $text;
    }

    /**
     * Get global error. Null = undefined.
     * @return string|null
     */
    public function getError()
    {
        return $this->globalError;
    }
}