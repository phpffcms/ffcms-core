<?php

namespace Ffcms\Core\Helper\HTML;
use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;

/**
 * Class HList
 * @package Ffcms\Core\Helper\HTML
 */
class Listing extends NativeGenerator {

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

        $ulProperties = self::applyProperty($elements['ul']);

        $items = null;
        foreach($elements['items'] as $item) {
            if (!Arr::in($item['type'], ['text', 'link'])) {
                continue;
            }
            if($item['type'] === 'link') {
                $controllerAction = trim(Object::isArray($item['link']) ? $item['link'][0] : $item['link'], '/');
                $currentCA = strtolower(App::$Request->getController() . '/' . App::$Request->getAction());
                if ($item['activeClass'] === null) {
                    $item['activeClass'] = 'active';
                }
                if($currentCA === $controllerAction) {
                    if(null !== $item['link'][1] && Object::isArray($item['link'])) {
                        if ($item['link'][1] === App::$Request->getID()) {
                            $item['property']['class'] = String::length($item['property']['class']) > 0
                                ? $item['activeClass'] . ' ' . $item['property']['class']
                                : $item['activeClass'];
                        }
                    } else {
                        $item['property']['class'] = String::length($item['property']['class']) > 0
                            ? $item['activeClass'] . ' ' . $item['property']['class']
                            : $item['activeClass'];
                    }
                }
            }
            //$items .= '<li' . (sizeof($item['property']) > 0 ? ' class="' . implode(' ', $item['property']) . '"' : null) . '>';
            $items .= '<li';
            if(count($item['property']) > 0) {
                foreach($item['property'] as $attr => $value) {
                    $items .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $items .= '>';

            if ($item['type'] === 'text') {
                $items .= ($item['html'] ? self::safe($item['text']) : self::nohtml($item['text']));
            } elseif ($item['type'] === 'link') {
                $link = App::$Alias->baseUrl;
                if (Object::isArray($item['link'])) {
                    $link .= trim($item['link'][0], '/'); // controller/action
                    if (null !== $item['link'][1]) {
                        $link .= '/' . self::nohtml($item['link'][1]); // param id
                    }
                    if (null !== $item['link'][2]) {
                        $link .= '/' . self::nohtml($item['link'][2]); // param id
                    }
                    if (Object::isArray($item['link'][3])) { // dynamic params ?a=b&v=c etc
                        $firstParam = true;
                        foreach($item['link'][3] as $p => $v) {
                            if($firstParam) {
                                $link .= '?' . self::nohtml($p) . '=' . self::nohtml($v);
                            } else {
                                $link .= '&' . self::nohtml($p) . '=' . self::nohtml($v);
                            }
                            $firstParam = false;
                        }
                    } else {
                        $link .= '/';
                    }
                } elseif(String::startsWith('http', $item['link'])) {
                    $link = self::nohtml($item['link']);
                } elseif(String::startsWith('#', $item['link'])) { // allow pass #part
                    $link = self::nohtml($item['link']);
                } else {
                    $link .= self::nohtml(trim($item['link'], '/'));
                }
                $htmlLink = '<a href="' . self::nohtml($link) . '"';
                $htmlLink .= self::applyProperty($item['linkProperty']);
                $htmlLink .= '>' . ($item['html'] ? App::$Security->purifier()->purify($item['text']) : App::$Security->strip_tags($item['text'])) . '</a>';
                $items .= $htmlLink;
            }
            $items .= '</li>';
        }

        return '<ul' . $ulProperties . '>' . $items . '</ul>';
    }
}