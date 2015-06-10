<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Arr;
use Ffcms\Core\Helper\Object;
use Ffcms\Core\Helper\String;
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

        $ulProperties = self::applyProperty($elements['property']);
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
                if (!Object::isArray($item['link']) && !String::startsWith('http', $item['link']) && !String::startsWith('#', $item['link'])) {
                    $item['link'] = [$item['link']]; // just controller/action sended, to array
                }

                if (Object::isArray($item['link'])) {
                    $elementPoint = Url::buildPathway($item['link']);
                    $currentPoint = Url::buildPathwayFromRequest();

                    if ($item['activeClass'] === null) {
                        $item['activeClass'] = 'active';
                    }

                    $activeItem = false;
                    // use special active element order type: controller, action - todo
                    switch ($orderActiveLink) {
                        case 'controller':
                            $elementPoint = String::firstIn($elementPoint, '/');
                            $activeItem = String::startsWith($elementPoint, $currentPoint);
                            break;
                        case 'action':
                            $elementArray = explode('/', $elementPoint);
                            $elementPoint = $elementArray[0] . '/' . $elementArray[1];
                            $activeItem = String::startsWith($elementPoint, $currentPoint);
                            break;
                        default:
                            $activeItem = $elementPoint === $currentPoint;
                            break;
                    }


                    // check if it active link for current pathway
                    if ($activeItem) {
                        $item['property']['class'] = String::length($item['property']['class']) > 0
                            ? $item['activeClass'] . ' ' . $item['property']['class']
                            : $item['activeClass'];
                    }
                }
            }

            $items .= '<li';
            if (count($item['property']) > 0) {
                foreach ($item['property'] as $attr => $value) {
                    $items .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $items .= '>';

            // sounds like a text, build element
            if ($item['type'] === 'text') {
                $items .= ($item['html'] ? self::safe($item['text']) : self::nohtml($item['text']));
            } elseif ($item['type'] === 'link') { // sounds like link
                $link = App::$Alias->baseUrl . '/';
                if (Object::isArray($item['link'])) {
                    $link .= Url::buildPathway($item['link']);
                } elseif (String::startsWith('http', $item['link'])) {
                    $link = self::nohtml($item['link']);
                } elseif (String::startsWith('#', $item['link'])) { // allow pass #part
                    $link = self::nohtml($item['link']);
                } else {
                    $link .= self::nohtml(trim($item['link'], '/'));
                }
                $htmlLink = '<a href="' . self::nohtml($link) . '"';
                $htmlLink .= self::applyProperty($item['linkProperty']);
                $htmlLink .= '>' . ($item['html'] ? App::$Security->secureHtml($item['text']) : App::$Security->strip_tags($item['text'])) . '</a>';
                $items .= $htmlLink;
            }
            $items .= '</li>';
        }

        return '<' . $elements['type'] . $ulProperties . '>' . $items . '</' . $elements['type'] .  '>';
    }
}