<?php

namespace Ffcms\Core\Helper\HTML;

use Core\Helper\Object;

class Table extends \Core\Helper\HTML\NativeGenerator {

    public static function display($elements)
    {
        if(!is_array($elements) || sizeof($elements) < 1)
            return null;

        if(!is_array($elements['tbody']['items']) || sizeof($elements['tbody']['items']) < 1)
            return null;

        $tbody_items = null;

        if(is_array($elements['tbody']) && sizeof($elements['tbody']) > 0) {
            $tbody_items .= '<tbody' . self::applyProperty($elements['tbody']['property']) . '>';
            foreach($elements['tbody']['items'] as $item) {
                ksort($item); // sort by key
                $tbody_items .= '<tr' . self::applyProperty($item['property']) . '>';
                foreach($item as $id => $data) {
                    if(Object::isInt($id)) { // td element
                        $tbody_items .= '<td' . self::applyProperty($data['property']) . '>';
                        $tbody_items .= $data['html'] ? self::safe($data['text'], true) : self::nohtml($data['text']);
                        $tbody_items .= '</td>';
                    }
                }
                $tbody_items .= '</tr>';
            }
            $tbody_items .= '</tbody>';
        }

        $thead_items = null;
        if(is_array($elements['thead']) && sizeof($elements['thead']) > 0) {
            $thead_items .= '<thead' . self::applyProperty($elements['thead']['property']) . '>';
            $thead_items .= '<tr>';
            if(is_array($elements['thead']['titles']) && sizeof($elements['thead']['titles']) > 0) {
                foreach($elements['thead']['titles'] as $title) {
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