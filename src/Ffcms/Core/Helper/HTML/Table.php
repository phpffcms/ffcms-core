<?php

namespace Ffcms\Core\Helper\HTML;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\System\Dom;
use Ffcms\Core\Helper\HTML\System\NativeGenerator;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class Table. Helper for drawing tables using php static call Table::display([params])
 * @package Ffcms\Core\Helper\HTML
 */
class Table extends NativeGenerator
{

    /**
     * Construct table based on passed elements as array: properties, thead, tbody, rows, items etc
     * @param array $elements
     * @return string|null
     */
    public static function display(array $elements): ?string
    {
        if (!array_key_exists('tbody', $elements) || !Any::isArray($elements['tbody']['items']) || count($elements['tbody']['items']) < 1)
            return null;

        $selectOptions = false;
        if (isset($elements['selectableBox'])) {
            $selectOptions = $elements['selectableBox'];
            unset($elements['selectableBox']);
        }

        // init dom model
        $dom = new Dom();
        // draw response
        $table = $dom->table(function () use ($dom, $elements, $selectOptions) {
            $res = null;
            // check if thead is defined
            if (isset($elements['thead']) && Any::isArray($elements['thead']) && count($elements['thead']) > 0 && Any::isArray($elements['thead']['titles'])) {
                // add thead container
                $res .= $dom->thead(function () use ($dom, $elements, $selectOptions) {
                    return $dom->tr(function () use ($dom, $elements, $selectOptions) {
                        $tr = null;
                        foreach ($elements['thead']['titles'] as $order => $title) {
                            $th = null;
                            if ($title['html'] === true) {
                                if ($title['!secure'] === true) {
                                    $th = $title['text'];
                                } else {
                                    $th = self::safe($title['text'], true);
                                }
                            } else {
                                $th = htmlentities($title['text'], null, "UTF-8");
                            }
                            // make global checkbox for selectable columns
                            if ($selectOptions !== false && $order + 1 === $selectOptions['attachOrder']) {
                                $th = $dom->input(function () {
                                    return null;
                                }, ['type' => 'checkbox', 'name' => 'selectAll']) . ' ' . $th;
                            }
                            // build tr row collecting all th's
                            $tr .= $dom->th(function () use ($th) {
                                return $th;
                            }, $title['property']);
                        }
                        // return tr row in thead
                        return $tr;
                    });
                }, $elements['thead']['property']);
            }
            // parse tbody array elements
            if (isset($elements['tbody']) && Any::isArray($elements['tbody']) && isset($elements['tbody']['items']) && Any::isArray($elements['tbody']['items'])) {
                // add tbody container
                $res .= $dom->tbody(function() use ($dom, $elements, $selectOptions){
                    $tr = null;
                    // each all items by row (tr)
                    foreach ($elements['tbody']['items'] as $row) {
                        // sort td items inside row by key increment
                        ksort($row);
                        // add data in tr container
                        $tr .= $dom->tr(function () use ($dom, $row, $selectOptions) {
                            $td = null;
                            foreach ($row as $order => $item) {
                                if (!Any::isInt($order))
                                    continue;

                                // collect td item
                                $td .= $dom->td(function () use ($dom, $order, $item, $selectOptions) {
                                    $text = null;
                                    // make text secure based on passed options
                                    if ($item['html'] === true) {
                                        if ($item['!secure'] === true) {
                                            $text = $item['text'];
                                        } else {
                                            $text = self::safe($item['text'], true);
                                        }
                                    } else {
                                        $text = htmlentities($item['text'], null, 'UTF-8');
                                    }
                                    // check if selectable box is enabled and equal current order id
                                    if ($selectOptions !== false && $order === $selectOptions['attachOrder']) {
                                        $text = $dom->input(function (){
                                            return null;
                                        }, Arr::merge($selectOptions['selector'], ['value' => htmlentities($text, null, 'UTF-8')])) . ' ' . $text;
                                    }
                                    return $text;
                                }, $item['property']);
                            }
                            return $td;
                        }, $row['property']);
                    }
                    return $tr;
                }, $elements['tbody']['property']);
            }



            // return all computed code
            return $res;
        }, $elements['table']);

        // check if select box is defined and used
        if ($selectOptions !== false || Any::isArray($selectOptions)) {
            // build js code for "selectAll" checkbox
            self::buildSelectorHtml($selectOptions);
            // return response inside "form" tag
            return $dom->form(function () use ($dom, $selectOptions, $table) {
                foreach ($selectOptions['buttons'] as $btn) {
                    if (!Any::isArray($btn))
                        continue;

                    $table .= $dom->input(function(){
                        return null;
                    }, $btn) . ' ';
                }
                return $table;
            }, $selectOptions['form']);
        }

        return $table;
    }

    /**
     * Build special js code for select boxes for "select all" button in header
     * @param array $opt
     */
    private static function buildSelectorHtml(array $opt)
    {
        $js = '$(function () {
            var targetSwitchbox = $(\'input[name="' . $opt['selector']['name'] . '"]\');
            $(\'input[name="selectAll"]\').change(function() {
                $(targetSwitchbox).each(function () {
                    $(this).prop(\'checked\', !$(this).is(\':checked\'));
                });
            });
        });';
        App::$Alias->addPlainCode('js', $js);
    }
}