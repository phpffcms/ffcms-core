<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\Helper\Type\Arr;
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

        $selectOptions = false;
        if (isset($elements['selectableBox'])) {
            $selectOptions = $elements['selectableBox'];
            unset($elements['selectableBox']);
        }

        $tbodyItems = null;

        if (Object::isArray($elements['tbody']) && count($elements['tbody']) > 0) {
            $tbodyItems .= '<tbody' . self::applyProperty($elements['tbody']['property']) . '>';
            foreach ($elements['tbody']['items'] as $item) {
                ksort($item); // sort by key
                $tbodyItems .= '<tr' . self::applyProperty($item['property']) . '>';
                foreach ($item as $id => $data) {
                    if (Object::isInt($id)) { // td element
                        $itemText = null;
                        if ($data['html'] === true) {
                            if ($data['!secure'] === true) {
                                $itemText = $data['text'];
                            } else {
                                $itemText = self::safe($data['text'], true);
                            }
                        } else {
                            $itemText = self::nohtml($data['text']);
                        }
                        if ($selectOptions !== false && $id === $selectOptions['attachOrder']) {
                            $itemText = self::buildSingleTag('input', Arr::merge($selectOptions['input'], ['value' => $itemText])) . ' ' . $itemText;
                        }
                        $tbodyItems .= '<td' . self::applyProperty($data['property']) . '>' . $itemText . '</td>';
                    }
                }
                $tbodyItems .= '</tr>';
            }
            $tbodyItems .= '</tbody>';
        }

        $theadItems = null;
        if (Object::isArray($elements['thead']) && count($elements['thead']) > 0) {
            $theadItems .= '<thead' . self::applyProperty($elements['thead']['property']) . '>';
            $theadItems .= '<tr>';
            if (Object::isArray($elements['thead']['titles']) && count($elements['thead']['titles']) > 0) {
                foreach ($elements['thead']['titles'] as $title) {
                    $theadItems .= '<th>' . ($title['html'] ? self::safe($title['text'], true) : self::nohtml($title['text'])) . '</th>';
                }
            }
            $theadItems .= '</tr></thead>';
        }

        $build = null;
        if ($selectOptions !== false) {
            $build .= '<form ' . self::applyProperty($selectOptions['form']) . '>';
        }

        $build .= '<table' . self::applyProperty($elements['table']) . '>';
        $build .= $theadItems;
        $build .= $tbodyItems;
        $build .= '</table>';

        if ($selectOptions !== false) {
            $build .= self::buildSingleTag('input', $selectOptions['button']);
            $build .= '</form>';
        }

        return $build;
    }

}