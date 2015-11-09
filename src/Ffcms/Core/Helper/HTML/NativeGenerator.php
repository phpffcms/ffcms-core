<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

abstract class NativeGenerator
{

    /**
     * Make data "safe" - all dangerous html/js/etc will be removed
     * @param string $data
     * @param bool $quotes
     * @return string
     */
    public static function safe($data, $quotes = false)
    {
        $data = App::$Security->secureHtml($data);
        return $quotes ? $data : App::$Security->escapeQuotes($data);
    }

    /**
     * Remove all html tags from data
     * @param string $data
     * @return string
     */
    public static function nohtml($data)
    {
        return App::$Security->escapeQuotes(App::$Security->strip_tags($data));
    }

    /**
     * Build property for html element from array.
     * IMPORTANT: $property can be null-string (some times this happend's) - do not remove NULL!!
     * @param array $property
     * @return null|string
     */
    public static function applyProperty(array $property = null)
    {
        if (!Obj::isArray($property) || count($property) < 1) {
            return null;
        }

        $build = null;
        foreach ($property as $p => $v) {
            if ($v === null) {
                $build .= ' ' . self::nohtml($p);
            } else {
                $build .= ' ' . self::nohtml($p) . '="' . self::nohtml($v) . '"';
            }
        }
        return $build;
    }

    /**
     * Fast building single tag based on property's array
     * @param string $tagName
     * @param array $property
     * @return string
     */
    public static function buildSingleTag($tagName, array $property)
    {
        return '<' . self::nohtml($tagName) . self::applyProperty($property) . '/>';
    }

    /**
     * Fast building container type tag based on property's and value
     * @param string $tagName
     * @param array $property
     * @param null|string $value
     * @param bool $valueHtml
     * @return string
     */
    public static function buildContainerTag($tagName, array $property, $value = null, $valueHtml = false)
    {
        $tagName = self::nohtml($tagName);
        if (false === $valueHtml) {
            $value = self::nohtml($value);
        }
        return '<' . $tagName . self::applyProperty($property) . '>' . $value . '</' . $tagName . '>';
    }

    /**
     * Make parts of URI safe and usable
     * @param string $string
     * @param bool $encode
     * @return string
     */
    public static function safeUri($string, $encode = true)
    {
        $string = Str::lowerCase($string);
        $string = self::nohtml($string);
        if ($encode === true) {
            $string = urlencode($string);
        }
        return $string;
    }


}