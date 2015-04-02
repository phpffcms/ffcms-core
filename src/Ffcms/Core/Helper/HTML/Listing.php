<?php

namespace Ffcms\Core\Helper\HTML;
use Core\App;
use Core\Helper\Integer;
use Core\Helper\String;

/**
 * Class HList
 * @package Ffcms\Core\Helper\HTML
 */
class Listing {


    /**
     * Construct listing elements,property's and future's
     * @param array $elements
     * @return string
     */
    public static function display($elements)
    {
        if(!in_array($elements['type'], ['ul', 'ol']) || sizeof($elements['items']) < 1)
            return null;

        $ulProperties = null;
        if(sizeof($elements['ul'])) {
            foreach($elements['ul'] as $p => $v) {
                $ulProperties .= ' ' . $p . '="' . $v . '"';
            }
        }

        $items = null;
        foreach($elements['items'] as $item) {
            if(!in_array($item['type'], ['text', 'link']))
                continue;
            if($item['type'] == 'link') {
                $controllerAction = trim(is_array($item['link']) ? $item['link'][0] : $item['link'], '/');
                $currentCA = strtolower(App::$Request->getController() . '/' . App::$Request->getAction());
                if($item['activeClass'] == null)
                    $item['activeClass'] = 'active';
                if($currentCA == $controllerAction) {
                    if(is_array($item['link']) && !is_null($item['link'][1])) {
                        if($item['link'][1] == App::$Request->getID())
                            $item['property']['class'] = String::length($item['property']['class']) > 0
                                ? $item['activeClass'] . ' ' . $item['property']['class']
                                : $item['activeClass'];
                    } else {
                        $item['property']['class'] = String::length($item['property']['class']) > 0
                            ? $item['activeClass'] . ' ' . $item['property']['class']
                            : $item['activeClass'];
                    }
                }
            }
            //$items .= '<li' . (sizeof($item['property']) > 0 ? ' class="' . implode(' ', $item['property']) . '"' : null) . '>';
            $items .= '<li';
            if(sizeof($item['property']) > 0) {
                foreach($item['property'] as $attr => $value) {
                    $items .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $items .= '>';

            if($item['type'] == 'text') {
                $items .= ($item['html'] ? App::$Security->purifier()->purify($item['text']) : App::$Security->strip_tags($item['text']));
            } elseif($item['type'] == 'link') {
                $link = App::$Alias->baseUrl;
                if(is_array($item['link'])) {
                    $link .= trim($item['link'][0], '/'); // controller/action
                    if(!is_null($item['link'][1]))
                        $link .= '/' . $item['link'][1]; // param id
                    if(!is_null($item['link'][2]))
                        $link .= '/' . $item['link'][2]; // param id
                    if(is_array($item['link'][3])) { // dynamic params ?a=b&v=c etc
                        $firstParam = true;
                        foreach($item['link'][3] as $p => $v) {
                            if($firstParam)
                                $link .= "?" . $p . '=' . $v;
                            else
                                $link .= "&" . $p . '=' . $v;
                            $firstParam = false;
                        }
                    } else {
                        $link .= '/';
                    }
                } elseif(String::startsWith('#', $item['link'])) { // allow pass #part
                    $link = $item['link'];
                } else {
                    $link .= trim($item['link'], '/');
                }
                $htmlLink = '<a href="' . App::$Security->escapeQuotes(App::$Security->strip_tags($link)) . '"';
                if(is_array($item['linkProperty'])) {
                    foreach($item['linkProperty'] as $p => $v) {
                        $htmlLink .= ' ' . $p . '="' . $v . '"';
                    }
                }
                $htmlLink .= '>' . ($item['html'] ? App::$Security->purifier()->purify($item['text']) : App::$Security->strip_tags($item['text'])) . '</a>';
                $items .= $htmlLink;
            }
            $items .= '</li>';
        }

        return '<ul' . $ulProperties . '>' . $items . '</ul>';
    }
}