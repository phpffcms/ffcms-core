<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Ffcms\Core\Helper\HTML\Listing;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
use Ffcms\Core\Helper\Url;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Navbar extends NativeGenerator
{

    public static function display($elements)
    {
        if (!Object::isArray($elements) || count($elements['items']) < 1) {
            return null;
        }

        $elements['property']['class'] .= ' nav';
        $elements['nav']['class'] .= ' navbar';

        if ($elements['container'] === null) {
            $elements['container'] = 'container-fluid';
        }

        $mobileCollapse = self::nohtml($elements['collapseId']);
        if (null === $mobileCollapse) {
            $mobileCollapse = String::randomLatin(rand(6, 12)) . rand(1, 99);
        }
        $ulId = 1;

        $itemsLeft = [];
        $itemsRight = [];
        $itemsStatic = null;

        foreach ($elements['items'] as $item) {
            if (is_string($item)) { // sounds like a static object w/o render request
                $itemsStatic .= $item;
            } elseif (Object::isArray($item)) {
                $item['type'] = 'link';
                if ($item['position'] !== null && $item['position'] === 'right') { // right position item
                    $itemsRight[] = $item;
                } else { // left pos item
                    $itemsLeft[] = $item;
                }
            }
        }

        $leftBuild = null;
        $rightBuild = null;
        if (count($itemsLeft) > 0) {
            $mainElemLeft = $elements['property']; // todo: fix me!!
            $mainElemLeft['id'] .= $ulId;
            $ulId++;
            $leftBuild = Listing::display([
                'type' => 'ul',
                'property' => $mainElemLeft,
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
                'items' => $itemsRight
            ]);
        }


        $build = '<nav' . self::applyProperty($elements['nav']) . '>';
        $build .= '<div class="' . self::nohtml($elements['container']) . '">';

        $build .= '<div class="navbar-header">';
        $build .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="' . $mobileCollapse . '">';
        $build .= '<span class="sr-only">Toggle navigation</span>';
        for ($i = 0; $i < 3; $i++) {
            $build .= '<span class="icon-bar"></span>';
        }
        $build .= '</button>';
        $build .= Url::link($elements['brand']['link'], $elements['brand']['text'], ['class' => 'navbar-brand']);
        $build .= '</div>';

        $build .= '<div class="collapse navbar-collapse" id="' . $mobileCollapse . '">';
        $build .= $leftBuild;
        $build .= $itemsStatic;
        $build .= $rightBuild;

        $build .= '</div></div></nav>';


        return $build;
    }
}