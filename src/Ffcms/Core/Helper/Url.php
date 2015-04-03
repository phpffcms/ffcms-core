<?php

namespace Ffcms\Core\Helper;

use Core\App;

class Url {

    /**
     * Build link via controller/action and other params
     * @param string $controller_action
     * @param string|null $id
     * @param string|null $add
     * @param array $params
     * @return null|string
     */
    public static function to($controller_action, $id = null, $add = null, $params = [])
    {
        list($controller, $action) = explode('/', trim($controller_action, '/'));
        if($controller == null || $action == null)
            return App::$Alias->baseUrl;

        $url = App::$Alias->baseUrl . strtolower($controller) . '/' . strtolower($action) . '/';
        if($id !== null)
            $url .= $id . '/';

        if($add !== null) {
            $url .= $add;
            if(sizeof($params) < 1)
                $url .= '/';
        }

        if(sizeof($params) > 0) {
            $first = true;
            foreach($params as $key => $value) {
                if($first)
                    $url .= '?' . $key . '=' . $value;
                else
                    $url .= '&' . $key . '=' . $value;
            }
        }

        return $url;
    }

    /**
     * Create <a></a> block link
     * @param string|array $to
     * @param string $name
     * @param array $property
     * @return string
     */
    public static function link($to, $name, $property = [])
    {
        $compile_property = null;
        if(sizeof($property) > 0) {
            foreach($property as $param => $value) {
                $compile_property .= ' ' . $param . '="' . $value . '"';
            }
        }

        if(!is_array($to)) // callback magic (:
            $to = [$to];

        $invoke = new \ReflectionMethod(get_class(), 'to');
        $makeTo = $invoke->invokeArgs(null, $to);

        return '<a href="' . $makeTo . '"' . $compile_property . '>' . $name . '</a>';
    }

}