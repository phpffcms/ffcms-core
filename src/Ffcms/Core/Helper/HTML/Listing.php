<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/**
 * Class HList
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

        $orderActiveLink = false;
        if ($elements['activeOrder'] !== null) {
            $orderActiveLink = $elements['activeOrder'];
        }

        $items = null;
        // foreach elements and build schema
        foreach ($elements['items'] as $item) {
            // type is undefined, skip
            if (!Arr::in($item['type'], ['text', 'link'])) {
                continue;
            }
            // sounds like a link, try detect active element
            if ($item['type'] === 'link') {
                if (!Obj::isArray($item['link']) && !Str::startsWith('http', $item['link']) && !Str::startsWith('#',
                        $item['link'])
                ) {
                    $item['link'] = [$item['link']]; // just controller/action sended, to array
                }

                // check if current element is link and is active
                if (Obj::isArray($item['link'])) {
                    if (!isset($item['activeClass'])) {
                        $item['activeClass'] = 'active';
                    }

                    $activeItem = self::isCurrentLink($item['link'], $item['activeOn'], $orderActiveLink);

                    // check if it active link for current pathway
                    if ($activeItem) {
                        $item['property']['class'] = Str::length($item['property']['class']) > 0
                            ? $item['activeClass'] . ' ' . $item['property']['class']
                            : $item['activeClass'];
                    }
                }
            }

            $innerItem = self::applyEscape($item['text'], $item['html'], $item['!secure']);
            // sounds like a hyperlink?
            if ($item['type'] === 'link') {
                // parse uri-link type to classic link
                $item['linkProperty']['href'] = self::convertLink($item['link']);
                // make classic tag inside with text value
                $innerItem = self::buildContainerTag('a', $item['linkProperty'], $innerItem, $item['html']);
            }
            // store item
            $items .= self::buildContainerTag('li', $item['property'], $innerItem, true);
        }

        // return <ul/> or <ol/> full container
        return self::buildContainerTag($elements['type'], $elements['property'], $items, true);
    }
}