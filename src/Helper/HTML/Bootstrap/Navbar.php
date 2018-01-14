<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Ffcms\Core\Helper\HTML\Listing;
use Ffcms\Core\Helper\HTML\System\Dom;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Helper\Url;

/**
 * Class Navbar. Generate bootstrap navbar from array elements in one method ;)
 */
class Navbar extends NativeGenerator
{

    /**
     * Build bootstrap navbar
     * @param array $elements
     * @return string|null
     */
    public static function display(array $elements): ?string
    {
        // check if elements passed well
        if (!isset($elements['items']) || count($elements['items']) < 1) {
            return null;
        }

        // set default bootstrap properties if not defined
        $elements['property']['class'] = Str::concat(' ', 'nav', $elements['property']['class']);
        $elements['nav']['class'] = Str::concat(' ', 'navbar', $elements['nav']['class']);
        if (!$elements['container']) {
            $elements['container'] = 'container-fluid';
        }

        // set mobile collapse id for toggle
        $mobCollapseId = $elements['collapseId'];
        if (Any::isEmpty($mobCollapseId)) {
            $mobCollapseId = Str::randomLatin(mt_rand(6, 12)) . mt_rand(1, 99);
        }

        // set element id for toggle
        $ulId = 1;

        // prepare array's for left, right and static elements
        $itemsLeft = [];
        $itemsRight = [];
        $itemsStatic = null;

        foreach ($elements['items'] as $item) {
            if (Any::isStr($item)) { // sounds like a static object w/o render request
                $itemsStatic .= $item;
            } else {
                if ($item['type'] === 'dropdown') {
                    // build bootstrap dropdown properties
                    $item['dropdown'] = ['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'href' => '#'];
                    $item['property']['class'] = Str::concat(' ', 'dropdown', $item['property']['class']);
                } else {
                    $item['type'] = 'link';
                }
                // set item with position
                if ($item['position'] !== null && $item['position'] === 'right') { // right position item
                    $itemsRight[] = $item;
                } else { // left pos item
                    $itemsLeft[] = $item;
                }
            }
        }

        // build html dom for left and right elements
        $leftBuild = null;
        $rightBuild = null;
        if (count($itemsLeft) > 0) {
            $mainElemLeft = $elements['property']; // todo: fix me!!
            $mainElemLeft['id'] .= $ulId;
            $ulId++;
            $leftBuild = Listing::display([
                'type' => 'ul',
                'property' => $mainElemLeft,
                'activeOrder' => $elements['activeOrder'],
                'items' => $itemsLeft
            ]);
        }

        if (count($itemsRight) > 0) {
            $mainElemRight = $elements['property']; // todo: fix me!!
            $mainElemRight['class'] .= ' navbar-right';
            $mainElemRight['id'] .= $ulId;
            $ulId++;
            $rightBuild = Listing::display([
                'type' => 'ul',
                'property' => $mainElemRight,
                'activeOrder' => $elements['activeOrder'],
                'items' => $itemsRight
            ]);
        }

        // generate output dom of bootstrap navbar
        $dom = new Dom();
        $body = $dom->div(function () use ($leftBuild, $rightBuild, $itemsStatic) {
            return $leftBuild . $itemsStatic . $rightBuild;
        }, ['class' => 'collapse navbar-collapse', 'id' => $mobCollapseId]);

        // drow <nav @properties>@next</nav>
        return $dom->nav(function () use ($dom, $elements, $mobCollapseId, $body) {
            // drow <div @container>@next</div>
            return $dom->div(function () use ($dom, $elements, $mobCollapseId, $body) {
                // drow <div @navbar-header>@next</div>
                $header = $dom->div(function () use ($dom, $elements, $mobCollapseId) {
                    // drow <button @collapse>@next</button>
                    $collapseButton = $dom->button(function () use ($dom) {
                        $toggleItem = $dom->span(function () {
                            return 'Toggle menu';
                        }, ['class' => 'sr-only']);
                        $toggleIcon = null;
                        for ($i = 0; $i < 3; $i++) {
                            $toggleIcon .= $dom->span(function () {
                                return null;
                            }, ['class' => 'icon-bar']);
                        }
                        return $toggleItem . $toggleIcon;
                    }, ['type' => 'button', 'class' => 'navbar-toggle collapsed', 'data-toggle' => 'collapse', 'data-target' => '#' . $mobCollapseId]);
                    // drow <div @brand>@brandtext<?div>
                    $brand = null;
                    if (isset($elements['brand'])) {
                        if (!isset($elements['brand']['property']['class'])) {
                            $elements['brand']['property']['class'] = 'navbar-brand';
                        }
                        if (isset($elements['brand']['link'])) {
                            $brand = Url::link($elements['brand']['link'], $elements['brand']['text'], $elements['brand']['property']);
                        } else {
                            $brand = (new Dom())->span(function () use ($elements) {
                                return $elements['brand']['text'];
                            }, $elements['brand']['property']);
                        }
                    }
                    return $collapseButton . $brand;
                }, ['class' => 'navbar-header']);
                // return header and body concat
                return $header . $body;
            }, $elements['container']);
        }, $elements['nav']);
    }
}