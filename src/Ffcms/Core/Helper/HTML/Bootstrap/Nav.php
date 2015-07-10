<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\HTML\Listing;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Nav extends NativeGenerator
{

    public static function display($elements)
    {
        if (!Object::isArray($elements) || count($elements['items']) < 1) {
            return null;
        }

        $items = [];
        $tabIdx = 1;

        $tabContent = '<div class="tab-content">';
        if ($elements['tabAnchor'] === null) {
            $elements['tabAnchor'] = String::randomLatin(mt_rand(6, 12));
        }

        $blockProperty = [];
        if ($elements['blockProperty'] !== null) {
            if (Object::isArray($elements['blockProperty'])) {
                $blockProperty = $elements['blockProperty'];
            }
            unset($elements['blockProperty']);
        }

        // check if items have defined active order
        $activeDefined = Arr::in(true, Arr::ploke('active', $elements['items']));

        foreach ($elements['items'] as $item) {
            if ($item['type'] === 'link') {
                $item['property']['role'] = 'presentation';
                $items[] = $item;
            } elseif ($item['type'] === 'tab') {
                $activeObject = false;
                $item['type'] = 'link'; // fix for global Listing builder
                $item['link'] = '#' . $elements['tabAnchor'] . $tabIdx;

                $item['property']['role'] = 'presentation';

                // check if active definition is exist in elements options
                if ($activeDefined) {
                    if ($item['active'] === true) {
                        $activeObject = true;
                    }
                } elseif ($tabIdx === 1) { // if not exist set first as active
                    $activeObject = true;
                }

                // mark active tab
                if ($activeObject === true) {
                    $item['property']['class'] .= (String::length($item['property']['class']) > 0 ? ' ' : null) . 'active';
                }

                // tab special properties for bootstrap
                $item['linkProperty']['aria-controls'] = $elements['tabAnchor'] . $tabIdx;
                $item['linkProperty']['role'] = 'tab';
                $item['linkProperty']['data-toggle'] = 'tab';

                $itemContent = $item['content'];
                unset($item['content']);
                $items[] = $item;

                // draw tab content
                $tabContent .= '<div role="tabpanel" class="tab-pane fade' . ($activeObject === true ? ' in active' : null) . '" id="' . $elements['tabAnchor'] . $tabIdx . '">';
                if ($item['html'] === true) {
                    if ($item['!secure'] === true) {
                        $tabContent .= $itemContent;
                    } else {
                        $tabContent .= self::safe($itemContent);
                    }
                } else {
                    $tabContent .= self::nohtml($itemContent);
                }
                $tabContent .= '</div>';
                $tabIdx++;
            }
        }

        $tabContent .= '</div>';

        if ($elements['property']['class'] !== null) {
            $elements['property']['class'] = 'nav ' . $elements['property']['class'];
        } else {
            $elements['property']['class'] = 'nav';
        }

        $elements['property']['role'] = 'tablist';

        $listing = Listing::display([
            'type' => 'ul',
            'property' => $elements['property'],
            'activeOrder' => $elements['activeOrder'],
            'items' => $items
        ]);


        $blockProperty['role'] = 'tabpanel';

        return self::buildContainerTag('div', $blockProperty, $listing . $tabContent, true);
    }
}