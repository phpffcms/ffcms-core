<?php

namespace Ffcms\Core\Helper\HTML\System;

use Ffcms\Core\Helper\Type\Arr;

/**
 * Class Dom - build html DOM structure based on anonymous callbacks
 * @package Ffcms\Core\Helper\HTML
 * @method string hr(\Closure $obj, array $properties = null)
 * @method string br(\Closure $obj, array $properties = null)
 * @method string img(\Closure $obj, array $properties = null)
 * @method string input(\Closure $obj, array $properties = null)
 * @method string article(\Closure $obj, array $properties = null)
 * @method string nav(\Closure $obj, array $properties = null)
 * @method string div(\Closure $obj, array $properties = null)
 * @method string p(\Closure $obj, array $properties = null)
 * @method string a(\Closure $obj, array $properties = null)
 * @method string b(\Closure $obj, array $properties = null)
 * @method string s(\Closure $obj, array $properties = null)
 * @method string strong(\Closure $obj, array $properties = null)
 * @method string strike(\Closure $obj, array $properties = null)
 * @method string u(\Closure $obj, array $properties = null)
 * @method string span(\Closure $obj, array $properties = null)
 * @method string ul(\Closure $obj, array $properties = null)
 * @method string ol(\Closure $obj, array $properties = null)
 * @method string li(\Closure $obj, array $properties = null)
 * @method string table(\Closure $obj, array $properties = null)
 * @method string thead(\Closure $obj, array $properties = null)
 * @method string tbody(\Closure $obj, array $properties = null)
 * @method string tr(\Closure $obj, array $properties = null)
 * @method string td(\Closure $obj, array $properties = null)
 * @method string th(\Closure $obj, array $properties = null)
 * @method string dt(\Closure $obj, array $properties = null)
 * @method string dd(\Closure $obj, array $properties = null)
 * @method string form(\Closure $obj, array $properties = null)
 * @method string label(\Closure $obj, array $properties = null)
 * @method string button(\Closure $obj, array $properties = null)
 */
class Dom
{
    // single standalone tags
    public static $singleTags = [
        'hr', 'br', 'img', 'input'
    ];

    // container tags
    public static $containerTags = [
        'article', 'nav',
        'div', 'p', 'a',
        'b', 's', 'strong', 'strike', 'u', 'span',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'td', 'th', 'dt', 'dd',
        'form', 'label',
        'button'
    ];

    // private variables storage
    private $_vars = [];

    /**
     * Catch all callbacks
     * @param $name
     * @param $arguments
     * @return null|string
     */
    public function __call($name, $arguments)
    {
        $content = null;
        $properties = null;
        // get closure anonymous function and call it
        if (isset($arguments[0]) && $arguments[0] instanceof \Closure) {
            $closure = array_shift($arguments);
            $content = call_user_func_array($closure, $arguments);
        }
        // get properties for current lvl
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $properties = $arguments[0];
        }

        // build tag output html
        return $this->buildTag($name, $content, $properties);
    }

    /**
     * Build output content by tag name, tag content and properties
     * @param string $name
     * @param string|null $content
     * @param array|null $properties
     * @return null|string
     */
    private function buildTag($name, $content = null, array $properties = null)
    {
        // looks like a single tag, <img src="" class="" />, <hr class="" />
        if (Arr::in($name, self::$singleTags)) {
            return '<' . $name . self::applyProperties($properties) . ' />';
        } elseif(Arr::in($name, self::$containerTags)) { // looks like a container tag, <div class=""></div>
            return '<' . $name . self::applyProperties($properties) . '>' . $content . '</' . $name . '>';
        }

        // empty response
        return null;
    }

    /**
     * Parse properties from array to html string
     * @param array|null $properties
     * @return null|string
     */
    public static function applyProperties(array $properties = null)
    {
        // if looks like nothing - return
        if ($properties === null || count($properties) < 1) {
            return null;
        }

        // build output string
        $build = null;
        foreach ($properties as $property => $value) {
            if (!is_string($property)) {
                continue;
            }
            // sounds like single standalone property, ex required, selected etc
            if ($value === null || $value === false) {
                $build .= ' ' . htmlentities($property, ENT_QUOTES);
            } else { // sounds like a classic key="value" property
                $build .= ' ' . htmlentities($property, ENT_QUOTES) . '="' . htmlentities($value, ENT_QUOTES) . '"';
            }
        }
        return $build;
    }

    /**
     * Variable magic set
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->_vars[$name] = $value;
    }

    /**
     * Variable magic get
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_vars[$name];
    }

    /**
     * Check dynamic binded variable isset. Required for php 7.0.6 and highter
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_vars);
    }
}