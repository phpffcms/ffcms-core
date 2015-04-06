<?php

namespace Ffcms\Core\Helper\HTML\Bootstrap;

use Core\Helper\HTML\Listing;
use Core\Helper\String;
use Core\Helper\Url;


class Navbar extends \Core\Helper\HTML\NativeGenerator {


    public static function display($elements)
    {
        if(!is_array($elements) || sizeof($elements['items']) < 1)
            return null;

        $elements['ul']['class'] .= ' nav';
        $elements['nav']['class'] .= ' navbar';

        if($elements['container'] == null)
            $elements['container'] = 'container-fluid';

        $mobileCollapse = self::nohtml($elements['collapseId']);
        if(is_null($mobileCollapse))
            $mobileCollapse = String::randomLatin(rand(6,12)) . rand(1,99);

        $ulId = 1;

        $itemsLeft = [];
        $itemsRight = [];
        $itemsStatic = null;

        foreach($elements['items'] as $item) {
            if(is_string($item)) { // sounds like a static object w/o render request
                $itemsStatic .= $item;
            } elseif(is_array($item)) {
                $item['type'] = 'link';
                if($item['position'] != null && $item['position'] == 'right') { // right position item
                    $itemsRight[] = $item;
                } else { // left pos item
                    $itemsLeft[] = $item;
                }
            }
        }

        $leftBuild = null;
        $rightBuild = null;
        if(sizeof($itemsLeft) > 0) {
            $mainElemLeft = $elements['ul'];
            $mainElemLeft['id'] .= $ulId;
            $ulId++;
            $leftBuild = Listing::display([
                'type' => 'ul',
                'ul' => $mainElemLeft,
                'items' => $itemsLeft
            ]);
        }

        if(sizeof($itemsRight) > 0) {
            $mainElemRight = $elements['ul'];
            $mainElemRight['class'] .= ' navbar-right';
            $mainElemRight['id'] .= $ulId;
            $ulId++;
            $rightBuild = Listing::display([
                'type' => 'ul',
                'ul' => $mainElemRight,
                'items' => $itemsRight
            ]);
        }



        $build = '<nav' . self::applyProperty($elements['nav']) . '>';
        $build .= '<div class="' . self::nohtml($elements['container']) . '">';

        $build .= '<div class="navbar-header">';
        $build .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="' . $mobileCollapse . '">';
        $build .= '<span class="sr-only">Toggle navigation</span>';
        for($i=0;$i<3;$i++) {
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