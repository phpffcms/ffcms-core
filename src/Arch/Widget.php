<?php

namespace Ffcms\Core\Arch;

use Apps\ActiveRecord\App as AppRecord;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Core\Interfaces\iWidget;

/**
 * Class Widget. Provide constructor to work with widget-type based extensions for ffcms.
 * @package Ffcms\Core\Arch
 */
class Widget implements iWidget
{
    /** @var string|null */
    public static $class;

    public static function widget(array $params = null)
    {
        self::$class = get_called_class();

        // check if class exist
        if (!class_exists(self::$class)) {
            return 'Error: Widget is not founded: ' . self::$class;
        }

        // init class and pass properties
        $object = new self::$class;
        if (Any::isArray($params) && count($params) > 0) {
            foreach ($params as $property => $value) {
                if (property_exists($object, $property)) {
                    $object->{$property} = $value;
                }
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

    /**
     * Get widget configs from admin part as array $cfg=>$value
     * @return array|null|string
     */
    public function getConfigs()
    {
        $realName = Str::lastIn(self::$class, '\\', true);
        return AppRecord::getConfigs('widget', $realName);
    }

    public function display()
    {
    }
    public function init()
    {
    }

    /**
     * Check if widget is enabled. For native widget is always enabled
     * @param string $name
     * @return bool
     */
    public static function enabled($name)
    {
        return true;
    }
}
