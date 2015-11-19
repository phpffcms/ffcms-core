<?php

namespace Ffcms\Core\Arch;

use Ffcms\Core\App;
use Ffcms\Core\Interfaces\iWidget;
use Ffcms\Core\Traits\DynamicGlobal;

abstract class Widget implements iWidget
{
    use DynamicGlobal;

    /** @var string|null */
    public static $class;

    public static function widget(array $params = null)
    {
        if (self::$class === null) {
            self::$class = get_called_class();
        }

        // check if class exist
        if (!class_exists(self::$class)) {
            return 'Error: Widget is not founded: ' . App::$Security->strip_tags(self::$class);
        }

        // init class and pass properties
        $object = new self::$class;
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