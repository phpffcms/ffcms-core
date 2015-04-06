<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Core\App;
use Core\Helper\HTML\Listing;
use Core\Helper\String;

class Nav extends \Core\Helper\HTML\NativeGenerator
{

    public static function display($elements)
    {
        if (!is_array($elements) || sizeof($elements['items']) < 1) {
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

                $itemContent = App::$Security->purifier()->purify($item['content']);
                unset($item['content']);
                $items[] = $item;

                $tabContent .= '<div role="tabpanel" class="tab-pane' . ($tabIdx === 1 ? ' active' : null) . '" id="' . $elements['tabAnchor'] . $tabIdx . '">';
                $tabContent .= ($item['htmlContent'] ? self::safe($itemContent) : self::nohtml($itemContent)) . '</div>';
                $tabIdx++;
            }
        }

        $tabContent .= '</div>';

        $listing = Listing::display([
            'type' => 'ul',
            'ul' => ['class' => 'nav ' . $elements['ul'], 'role' => 'tablist'],
            'items' => $items
        ]);


        $output = '<div role="tabpanel">';
        $output .= $listing;
        $output .= $tabContent;
        $output .= '</div>';
        return $output;
    }
}