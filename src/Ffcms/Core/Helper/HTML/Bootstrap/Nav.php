<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Ffcms\Core\App;
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
            $elements['tabAnchor'] = String::randomLatin(rand(6, 12));
        }


        foreach ($elements['items'] as $item) {
            if ($item['type'] === 'link') {
                $item['property']['role'] = 'presentation';
                $items[] = $item;
            } elseif ($item['type'] === 'tab') {
                $item['type'] = 'link'; // fix for global Listing builder
                $item['link'] = '#' . $elements['tabAnchor'] . $tabIdx;

                $item['property']['role'] = 'presentation';

                if ($tabIdx === 1) {
                    $item['property']['class'] .= (String::length($item['property']['class']) > 0 ? ' ' : null) . 'active';
                }

                $item['linkProperty']['aria-controls'] = $elements['tabAnchor'] . $tabIdx;
                $item['linkProperty']['role'] = 'tab';
                $item['linkProperty']['data-toggle'] = 'tab';

                $itemContent = $item['content'];
                unset($item['content']);
                $items[] = $item;

                $tabContent .= '<div role="tabpanel" class="tab-pane' . ($tabIdx === 1 ? ' active' : null) . '" id="' . $elements['tabAnchor'] . $tabIdx . '">';
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
            'items' => $items
        ]);


        $output = '<div role="tabpanel">';
        $output .= $listing;
        $output .= $tabContent;
        $output .= '</div>';
        return $output;
    }
}