<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Table extends NativeGenerator
{

    public static function display($elements)
    {
        if (!Obj::isArray($elements) || count($elements) < 1) {
            return null;
        }

        if (!Obj::isArray($elements['tbody']['items']) || count($elements['tbody']['items']) < 1) {
            return null;
        }

        $selectOptions = false;
        if (isset($elements['selectableBox'])) {
            $selectOptions = $elements['selectableBox'];
            unset($elements['selectableBox']);
        }

        $tbodyItems = null;

        if (Obj::isArray($elements['tbody']) && count($elements['tbody']) > 0) {
            $tbodyItems .= '<tbody' . self::applyProperty($elements['tbody']['property']) . '>';
            foreach ($elements['tbody']['items'] as $item) {
                ksort($item); // sort by key
                $tbodyItems .= '<tr' . self::applyProperty($item['property']) . '>';
                foreach ($item as $id => $data) {
                    if (Obj::isInt($id)) { // td element
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
        if (Obj::isArray($elements['thead']) && count($elements['thead']) > 0) {
            $theadItems .= '<thead' . self::applyProperty($elements['thead']['property']) . '>';
            $theadItems .= '<tr>';
            if (Obj::isArray($elements['thead']['titles']) && count($elements['thead']['titles']) > 0) {
                foreach ($elements['thead']['titles'] as $order => $title) {
                    if ($selectOptions !== false && $order + 1 === $selectOptions['attachOrder']) {
                        $title['text'] = self::buildSingleTag('input', ['type' => 'checkbox', 'name' => 'selectAll']) . ' ' . $title['text'];
                        $title['html'] = true;
                    }
                    $theadItems .= self::buildContainerTag('th', [], $title['text'], $title['html']);
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

        if ($selectOptions !== false && Obj::isArray($selectOptions)) {
            self::buildSelectorHtml($selectOptions);
        }

        return $build;
    }

    /**
     * Build special js code for select boxes for "select all" button in header
     * @param array $opt
     */
    private static function buildSelectorHtml(array $opt)
    {
        $js = '$(function () {
            var targetSwitchbox = $(\'input[name="' . $opt['input']['name'] . '"]\');
            $(\'input[name="selectAll"]\').change(function() {
                $(targetSwitchbox).each(function () {
                    $(this).prop(\'checked\', !$(this).is(\':checked\'));
                });
            });
        });';
        App::$Alias->addPlainCode('js', $js);
    }

}