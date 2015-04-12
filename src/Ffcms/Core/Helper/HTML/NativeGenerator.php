<?php

namespace Ffcms\Core\Helper\HTML;

use \Core\App;

abstract class NativeGenerator {

    /**
     * Make data "safe" - all dangerous html/js/etc will be removed
     * @param string $data
     * @param bool $quotes
     * @return string
     */
    public static function safe($data, $quotes = false)
    {
        return $quotes ?
            App::$Security->purifier()->purify($data) :
            App::$Security->escapeQuotes(App::$Security->purifier()->purify($data));
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
     * Build property for html element from array
     * @param array $property
     * @return null|string
     */
    public static function applyProperty($property = null)
    {
        if (!is_array($property) || count($property) < 1) {
            return null;
        }

        $build = null;
        foreach($property as $p => $v) {
            if($v === null) {
                $build .= ' ' . self::nohtml($p);
            } else {
                $build .= ' ' . self::nohtml($p) . '="' . self::nohtml($v) . '"';
            }
        }
        return $build;
    }


}