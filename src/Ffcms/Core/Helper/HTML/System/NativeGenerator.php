<?php

namespace Ffcms\Core\Helper\HTML\System;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Security;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

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
        $purifier = null;
        if (App::$Memory->get('object.purifier.helpers') !== null) {
            $purifier = App::$Memory->get('object.purifier.helpers');
        } else {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', root . '/Private/Cache/HTMLPurifier/');
            $config->set('AutoFormat.AutoParagraph', false);

            // allow use target=_blank for links
            $def = $config->getHTMLDefinition(true);
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

            $purifier = new \HTMLPurifier($config);
            App::$Memory->set('object.purifier.helpers', $purifier);
        }

        $data = $purifier->purify($data);
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
            if ($v === null || $v === false || $v === true) {
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
     * @param array|null $property
     * @param bool $closeSlash
     * @return string
     */
    public static function buildSingleTag($tagName, array $property = null, $closeSlash = true)
    {
        return '<' . self::nohtml($tagName) . self::applyProperty($property) . ($closeSlash ? '/>' : '>');
    }

    /**
     * Fast building container type tag based on property's and value
     * @param string $tagName
     * @param array|null $property
     * @param null|string $value
     * @param bool $valueHtml
     * @return string
     */
    public static function buildContainerTag($tagName, array $property = null, $value = null, $valueHtml = false)
    {
        $tagName = self::nohtml($tagName);
        if ($valueHtml !== true) {
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

    /**
     * Check if uri $source is equal to current uri point with array of $aliases and active $order set
     * @param null $source
     * @param array|null $aliases
     * @param bool $order
     * @return bool
     */
    public static function isCurrentLink($source = null, array $aliases = null, $order = false)
    {
        $elementPoint = Url::buildPathway($source);
        $currentPoint = Url::buildPathwayFromRequest();

        // use special active element order type: controller, action
        switch ($order) {
            case 'controller':
                $elementPoint = Str::firstIn($elementPoint, '/');
                $active = Str::startsWith($elementPoint, $currentPoint);
                break;
            case 'action':
                $elementArray = explode('/', $elementPoint);
                if (!Str::contains('/', $elementPoint) || count($elementArray) < 2) {
                    $active = $elementPoint === $currentPoint;
                } else {
                    $elementPoint = $elementArray[0] . '/' . $elementArray[1];
                    $active = Str::startsWith($elementPoint, $currentPoint);
                }
                break;
            case 'id':
                $elementArray = explode('/', $elementPoint);
                $elementPoint = $elementArray[0] . '/' . $elementArray[1];
                if (null !== $elementArray[2]) {
                    $elementPoint .= '/' . $elementArray[2];
                }

                $active = Str::startsWith($elementPoint, $currentPoint);
                break;
            default:
                $active = $elementPoint === $currentPoint;
                break;
        }

        // check if current uri equals with aliases
        if (Obj::isArray($aliases) && count($aliases) > 0) {
            foreach ($aliases as $activeUri) {
                $activeUri = trim($activeUri, '/');
                if (Str::endsWith('*', $activeUri)) {
                    $activeUri = rtrim($activeUri, '*');
                    if (Str::startsWith($activeUri, $currentPoint)) {
                        $active = true;
                    }
                } else {
                    if ($activeUri === $currentPoint) {
                        $active = true;
                    }
                }
            }
        }

        return $active;
    }

    /**
     * Apply security for string to output as html
     * @param string|null $object
     * @param bool $html
     * @param bool $secure
     * @return null|string
     */
    public static function applyEscape($object = null, $html = false, $secure = false)
    {
        $object = (string)$object;
        if ($html !== true) {
            $object = self::nohtml($object);
        } elseif ($secure !== true) {
            $object = self::safe($object, true);
        }

        return $object;
    }

    /**
     * Convert link-binding type to classic link with security filter
     * @param string|array $uri
     * @return string
     */
    public static function convertLink($uri)
    {
        $link = App::$Alias->baseUrl . '/';
        if (Obj::isArray($uri)) {
            $link .= Url::buildPathway($uri);
        } elseif (Str::startsWith('http', $uri)) {
            $link = self::nohtml($uri);
        } elseif (Str::startsWith('#', $uri)) { // allow pass #part
            $link = self::nohtml($uri);
        } else {
            $link .= self::nohtml(trim($uri, '/'));
        }
        return $link;
    }


}