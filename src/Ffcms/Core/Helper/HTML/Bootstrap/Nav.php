<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Ffcms\Core\Helper\HTML\Listing;
use Ffcms\Core\Helper\HTML\System\Dom;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;

class Nav extends NativeGenerator
{
    /**
     * Display nav listing block
     * @param array $elements
     */
    public static function display($elements)
    {
        // check if elements isn't empty and contains rows
        if (!Obj::isArray($elements) || count($elements['items']) < 1) {
            return null;
        }

        // prepare tab order
        if ($elements['tabAnchor'] === null) {
            $elements['tabAnchor'] = Str::randomLatin(mt_rand(6, 12));
        }

        // set global element properties
        $blockProperty = [];
        if ($elements['blockProperty'] !== null) {
            if (Obj::isArray($elements['blockProperty'])) {
                $blockProperty = $elements['blockProperty'];
            }
            unset($elements['blockProperty']);
        }

        // check if items have defined active order
        $activeDefined = Arr::in(true, Arr::ploke('active', $elements['items']));

        // prepare tab content
        $tabContent = null;
        $tabIdx = 1;

        // initialize dom model
        $dom = new Dom();

        // prepare items to drow listing
        $items = [];
        foreach ($elements['items'] as $item) {
            // its just a link, drow it as is
            if ($item['type'] === 'link') {
                $items[] = $item;
            } elseif ($item['type'] === 'dropdown') {
                // build bootstrap dropdown properties
                $item['dropdown'] = ['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'];
                $item['property']['class'] = Str::concat(' ', 'dropdown', $item['property']['class']);
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
                    $item['property']['class'] .= (Str::length($item['property']['class']) > 0 ? ' ' : null) . 'active';
                }

                // tab special properties for bootstrap
                $item['linkProperty']['aria-controls'] = $elements['tabAnchor'] . $tabIdx;
                $item['linkProperty']['role'] = 'tab';
                $item['linkProperty']['data-toggle'] = 'tab';

                $itemContent = $item['content'];
                unset($item['content']);
                $items[] = $item;

                // draw tab content
                $tabContent .= $dom->div(function() use ($item, $itemContent) {
                    if ($item['html'] === true) {
                        if ($item['!secure'] === true) {
                            return $itemContent;
                        } else {
                            return self::safe($itemContent);
                        }
                    } else {
                        return self::nohtml($itemContent);
                    }
                }, ['role' => 'tabpanel', 'class' => 'tab-pane fade' . ($activeObject === true ? ' in active' : null), 'id' => $elements['tabAnchor'] . $tabIdx]);
                $tabIdx++;
            }
        }

        // check if global class "nav" isset
        if ($elements['property']['class'] !== null) {
            if (!Str::contains('nav ', $elements['property']['class'])) {
                $elements['property']['class'] = 'nav ' . $elements['property']['class'];
            }
        } else {
            $elements['property']['class'] = 'nav';
        }

        // render final output
        return $dom->div(function() use ($elements, $items, $tabContent, $dom){
            // drow listing
            $listing = Listing::display([
                'type' => 'ul',
                'property' => $elements['property'],
                'activeOrder' => $elements['activeOrder'],
                'items' => $items]);
            // drow tabs if isset
            if ($tabContent !== null) {
                $tabContent = $dom->div(function() use ($tabContent){
                    return $tabContent;
                }, ['class' => 'tab-content']);
            }
            return $listing . $tabContent;
        }, $blockProperty);
    }
}