<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\System\Dom;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

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
        if (!Arr::in($elements['type'], ['ul', 'ol']) || count($elements['items']) < 1) {
            return null;
        }

        // initialize new DOM model
        $dom = new Dom();
        // return DOM-HTML, build based on closures!
        return $dom->{$elements['type']}(function () use ($dom, $elements) {
            // prepare output avg variable
            $liHtml = null;
            // get active order level
            $orderActiveLink = false;
            if (isset($elements['activeOrder'])) {
                $orderActiveLink = $elements['activeOrder'];
            }

            // each all items as single item
            foreach ($elements['items'] as $item) {
                $itemContent = self::applyEscape($item['text'], $item['html'], $item['!secure']);
                $itemProperty = $item['property'];
                // check if element looks like link and can be active
                if (isset($item['link'])) {
                    // try to parse link format for controller/action definition (must be array: 'main/index' to ['main/index'])
                    if (!Obj::isArray($item['link']) && !Str::startsWith('http', $item['link']) && !Str::startsWith('#', $item['link'])) {
                        $item['link'] = [$item['link']];
                    }

                    // if its a controller/action definition try to work with active class
                    if (Obj::isArray($item['link'])) {
                        // check if css class for active item is defined
                        if (!isset($item['activeClass'])) {
                            $item['activeClass'] = 'active';
                        }

                        // check if element is active on current URI
                        if (self::isCurrentLink($item['link'], $item['activeOn'], $orderActiveLink) === true) {
                            $itemProperty['class'] = !Str::likeEmpty($itemProperty['class'])
                                ? $item['activeClass'] . ' ' . $itemProperty['class']
                                : $item['activeClass'];
                        }
                    }

                    // set href source for link
                    $item['linkProperty']['href'] = self::convertLink($item['link']);

                    // build a href link inside li element
                    $itemContent = $dom->a(function () use ($itemContent) {
                        return $itemContent;
                    }, $item['linkProperty']);
                }

                // build output li tag
                $liHtml .= $dom->li(function () use ($elements, $itemContent) {
                    return $itemContent;
                }, $itemProperty);
            }
            // return done html dom syntax from closure
            return $liHtml;
        }, $elements['property']);
    }
}