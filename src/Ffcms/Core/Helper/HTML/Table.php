<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\Helper\Type\Object;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Table extends NativeGenerator
{

    public static function display($elements)
    {
        if (!Object::isArray($elements) || count($elements) < 1) {
            return null;
        }

        if (!Object::isArray($elements['tbody']['items']) || count($elements['tbody']['items']) < 1) {
            return null;
        }

        $tbody_items = null;

        if (Object::isArray($elements['tbody']) && count($elements['tbody']) > 0) {
            $tbody_items .= '<tbody' . self::applyProperty($elements['tbody']['property']) . '>';
            foreach ($elements['tbody']['items'] as $item) {
                ksort($item); // sort by key
                $tbody_items .= '<tr' . self::applyProperty($item['property']) . '>';
                foreach ($item as $id => $data) {
                    if (Object::isInt($id)) { // td element
                        $tbody_items .= '<td' . self::applyProperty($data['property']) . '>';
                        if ($data['html'] === true) {
                            if ($data['!secure'] === true) {
                                $tbody_items .= $data['text'];
                            } else {
                                $tbody_items .= self::safe($data['text'], true);
                            }
                        } else {
                            $tbody_items .= self::nohtml($data['text']);
                        }
                        $tbody_items .= '</td>';
                    }
                }
                $tbody_items .= '</tr>';
            }
            $tbody_items .= '</tbody>';
        }

        $thead_items = null;
        if (Object::isArray($elements['thead']) && count($elements['thead']) > 0) {
            $thead_items .= '<thead' . self::applyProperty($elements['thead']['property']) . '>';
            $thead_items .= '<tr>';
            if (Object::isArray($elements['thead']['titles']) && count($elements['thead']['titles']) > 0) {
                foreach ($elements['thead']['titles'] as $title) {
                    $thead_items .= '<th>' . ($title['html'] ? self::safe($title['text'], true) : self::nohtml($title['text'])) . '</th>';
                }
            }
            $thead_items .= '</tr></thead>';
        }

        $build = '<table' . self::applyProperty($elements['table']) . '>';
        $build .= $thead_items;
        $build .= $tbody_items;
        $build .= '</table>';
        return $build;
    }

}