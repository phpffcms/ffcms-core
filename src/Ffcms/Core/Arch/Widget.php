<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Interfaces\iWidget;
use Ffcms\Core\Traits\DynamicGlobal;

class Widget implements iWidget
{
    use DynamicGlobal;

    public static function widget(array $params = null)
    {
        // check if widget class is passed directly
        if ($params['class'] === null) {
            $params['class'] = get_called_class();
        }

        // set class object name
        $className = $params['class'];
        unset($params['class']);

        // check if class exist
        if (!class_exists($className)) {
            return 'Error: Widget is not founded: ' . App::$Security->strip_tags($className);
        }

        // init class and pass properties
        $object = new $className;
        foreach ($params as $property => $value) {
            if (property_exists($object, $property)) {
                $object->$property = $value;
            }
        }

        // prepare output
        $out = null;
        try {
            $object->init();
            $out = $object->display();
        } catch (\Exception $e) {
            throw $e;
        }

        return $out;
    }

    public function display() {}
    public function init() {}

}