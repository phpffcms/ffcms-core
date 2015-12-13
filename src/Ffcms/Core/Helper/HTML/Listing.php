<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\Type\Str;
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
                if (!Obj::isArray($item['link']) && !Str::startsWith('http', $item['link']) && !Str::startsWith('#', $item['link'])) {
                    $item['link'] = [$item['link']]; // just controller/action sended, to array
                }

                if (Obj::isArray($item['link'])) {
                    $elementPoint = Url::buildPathway($item['link']);
                    $currentPoint = Url::buildPathwayFromRequest();

                    if (!isset($item['activeClass'])) {
                        $item['activeClass'] = 'active';
                    }

                    $activeItem = false;
                    // use special active element order type: controller, action
                    switch ($orderActiveLink) {
                        case 'controller':
                            $elementPoint = Str::firstIn($elementPoint, '/');
                            $activeItem = Str::startsWith($elementPoint, $currentPoint);
                            break;
                        case 'action':
                            $elementArray = explode('/', $elementPoint);
                            if (!Str::contains('/', $elementPoint) || count($elementArray) < 2) {
                                $activeItem = $elementPoint === $currentPoint;
                            } else {
                                $elementPoint = $elementArray[0] . '/' . $elementArray[1];
                                $activeItem = Str::startsWith($elementPoint, $currentPoint);
                            }
                            break;
                        case 'id':
                            $elementArray = explode('/', $elementPoint);
                            $elementPoint = $elementArray[0] . '/' . $elementArray[1];
                            if (null !== $elementArray[2]) {
                                $elementPoint .= '/' . $elementArray[2];
                            }

                            $activeItem = Str::startsWith($elementPoint, $currentPoint);
                            break;
                        default:
                            $activeItem = $elementPoint === $currentPoint;
                            break;
                    }

                    // if defined active on pathways lets try to find equals
                    if (isset($item['activeOn']) && Obj::isArray($item['activeOn'])) {
                        foreach ($item['activeOn'] as $activeUri) {
                            $activeUri = trim($activeUri, '/');
                            if (Str::endsWith('*', $activeUri)) {
                                $activeUri = rtrim($activeUri, '*');
                                if (Str::startsWith($activeUri, $currentPoint)) {
                                    $activeItem = true;
                                }
                            } else {
                                if ($activeUri === $currentPoint) {
                                    $activeItem = true;
                                }
                            }
                        }
                    }


                    // check if it active link for current pathway
                    if ($activeItem) {
                        $item['property']['class'] = Str::length($item['property']['class']) > 0
                            ? $item['activeClass'] . ' ' . $item['property']['class']
                            : $item['activeClass'];
                    }
                }
            }

            $items .= '<li';
            if (isset($item['property']) && count($item['property']) > 0) {
                foreach ($item['property'] as $attr => $value) {
                    $items .= ' ' . $attr . '="' . $value . '"';
                }
            }
            $items .= '>';

            // sounds like a text, build element
            if ($item['type'] === 'text') {
                if (isset($item['html']) && $item['html'] === true) {
                    if (isset($item['!secure']) && $item['!secure'] === true) {
                        $items .= $item['text'];
                    } else {
                        $items .= self::safe($item['text'], true);
                    }
                } else {
                    $items .= self::nohtml($item['text']);
                }
            } elseif ($item['type'] === 'link') { // sounds like link
                $link = App::$Alias->baseUrl . '/';
                if (Obj::isArray($item['link'])) {
                    $link .= Url::buildPathway($item['link']);
                } elseif (Str::startsWith('http', $item['link'])) {
                    $link = self::nohtml($item['link']);
                } elseif (Str::startsWith('#', $item['link'])) { // allow pass #part
                    $link = self::nohtml($item['link']);
                } else {
                    $link .= self::nohtml(trim($item['link'], '/'));
                }

                $htmlLink = '<a href="' . self::nohtml($link) . '"';
                if (isset($item['linkProperty']) && Obj::isArray($item['linkProperty'])) {
                    $htmlLink .= self::applyProperty($item['linkProperty']);
                }
                $htmlLink .= '>';

                if (isset($item['html']) && $item['html'] === true) {
                    if (isset($item['!secure']) && $item['!secure'] === true) {
                        $htmlLink .= $item['text'];
                    } else {
                        $htmlLink .= self::safe($item['text'], true);
                    }
                } else {
                    $htmlLink .= self::nohtml($item['text']);
                }

                $htmlLink .= '</a>';

                $items .= $htmlLink;
            }
            $items .= '</li>';
        }

        return '<' . $elements['type'] . $ulProperties . '>' . $items . '</' . $elements['type'] .  '>';
    }
}