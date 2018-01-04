<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\Helper\HTML\System\Dom;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

/**
 * Class Listing - build html listing carcase
 * @package Ffcms\Core\Helper\HTML
 */
class Listing extends NativeGenerator
{

    /**
     * Construct listing elements,property's and future's
     * @param array $elements
     * @return string
     */
    public static function display($elements)
    {
        // check input elements
        if (!Arr::in($elements['type'], ['ul', 'ol']) || count($elements['items']) < 1)
            return null;

        // initialize new DOM model
        $dom = new Dom();
        // return DOM-HTML, build based on closures!
        return $dom->{$elements['type']}(function () use ($dom, $elements) {
            // prepare output avg variable
            $itemHTML = null;
            // get active order level
            $orderActiveLink = false;
            if (isset($elements['activeOrder'])) {
                $orderActiveLink = $elements['activeOrder'];
                unset($elements['activeOrder']);
            }

            foreach ($elements['items'] as $item) {
                // sounds like dropdown array
                if (isset($item['dropdown']) && isset($item['items'])) {
                    $itemHTML .= self::buildDropdown($dom, $item);
                } elseif (isset($item['link'])) { // looks like link item
                    $itemHTML .= self::buildLink($dom, $item, $orderActiveLink);
                } else { // just text item
                    $itemHTML .= self::buildText($dom, $item);
                }
            }
            return $itemHTML;
        }, $elements['property']);
    }

    /**
     * Build dropdown dom element
     * @param Dom $dom
     * @param array $item
     * @return string
     */
    private static function buildDropdown($dom, $item)
    {
        if (!Any::isArray($item['items']))
            return null;

        if (!isset($item['menuProperty']['class']))
            $item['menuProperty']['class'] = 'dropdown-menu';

        return $dom->li(function() use($dom, $item){
            $dropdownLink = $dom->a(function() use ($dom, $item){
                return self::applyEscape($item['text'], $item['html'], $item['!secure']) . ' ' . $dom->span(function(){}, ['class' => 'caret']);
            }, $item['dropdown']);

            $dropdownElements = $dom->ul(function() use ($dom, $item){
                $resp = null;
                foreach ($item['items'] as $obj) {
                    $resp .= self::buildLink($dom, $obj, false);
                }
                return $resp;
            }, $item['menuProperty']);

            return $dropdownLink . $dropdownElements;
        }, $item['property']);

    }

    /**
     * Build text item
     * @param Dom $dom
     * @param array $item
     * @return string
     */
    private static function buildText($dom, $item)
    {
        $text = self::applyEscape($item['text'], $item['html'], $item['!secure']);
        return $dom->li(function() use ($text){
            return $text;
        }, $item['property']);
    }

    /**
     * Build link with active definition in listing
     * @param Dom $dom
     * @param array $item
     * @param bool $orderActiveLink
     * @return string
     */
    private static function buildLink($dom, $item, $orderActiveLink = false)
    {
        // set default link data - text and properties
        $text = self::applyEscape($item['text'], $item['html'], $item['!secure']);
        $properties = $item['property'];

        // try to parse link format for controller/action definition (must be array: 'main/index' to ['main/index'])
        if (!Any::isArray($item['link']) && !Str::startsWith('http', $item['link']) && !Str::startsWith('#', $item['link'])) {
            $item['link'] = [$item['link']];
        }

        // if its a controller/action definition try to work with active class
        if (Any::isArray($item['link'])) {
            // check if css class for active item is defined
            if (!isset($item['activeClass'])) {
                $item['activeClass'] = 'active';
            }

            // check if element is active on current URI
            if (self::isCurrentLink($item['link'], $item['activeOn'], $orderActiveLink) === true) {
                $properties['class'] = Str::concat(' ', $item['activeClass'], $properties['class']);
            }
        }

        // set href source for link
        $item['linkProperty']['href'] = self::convertLink($item['link']);

        // build output <li@params><a @params>@text</li>
        return $dom->li(function () use ($dom, $text, $item) {
            return $dom->a(function() use ($text) {
                return $text;
            }, $item['linkProperty']);
        }, $properties);
    }
}