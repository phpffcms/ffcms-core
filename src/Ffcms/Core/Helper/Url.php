<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\HTML\NativeGenerator;

class Url extends NativeGenerator {

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
        if ($controller == null || $action == null) {
            return App::$Alias->baseUrl;
        }

        $url = App::$Alias->baseUrl . strtolower(self::nohtml($controller)) . '/' . strtolower(self::nohtml($action)) . '/';
        if ($id !== null) {
            $url .= self::nohtml($id) . '/';
        }

        if ($add !== null) {
            $url .= self::nohtml($add);
            if (count($params) < 1) {
                $url .= '/';
            }
        }

        if(count($params) > 0) {
            $first = true;
            foreach($params as $key => $value) {
                if($first) {
                    $url .= '?' . self::nohtml($key) . '=' . self::nohtml($value);
                } else {
                    $url .= '&' . self::nohtml($key) . '=' . self::nohtml($value);
                }
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
        $compile_property = self::applyProperty($property);

        if (!is_array($to)) { // callback magic (:
            $to = [$to];
        }

        $invoke = new \ReflectionMethod(get_class(), 'to');
        $makeTo = $invoke->invokeArgs(null, $to);

        return '<a href="' . $makeTo . '"' . $compile_property . '>' . $name . '</a>';
    }

}