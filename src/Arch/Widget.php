<?php

namespace Ffcms\Core\Arch;

use Apps\ActiveRecord\App as AppRecord;
use Ffcms\Core\App;
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
    
    public static $view;
    public static $request;
    public static $response;

    /**
     * Build and display widget
     * @param array|null $params
     * @return null|string
     */
    public static function widget(?array $params = null): ?string 
    {
        self::$class = get_called_class();
        self::$view = App::$View;
        self::$request = App::$Request;
        self::$response = App::$Response;

        // check if class exist
        if (!class_exists(self::$class)) {
            return 'Error: Widget is not founded: ' . self::$class;
        }

        /** @var iWidget $object */
        $object = new self::$class;
        if (Any::isArray($params) && count($params) > 0) {
            foreach ($params as $property => $value) {
                if (property_exists($object, $property)) {
                    $object->{$property} = $value;
                }
            }
        }

        // initialize widget
        $object->init();
        return $object->display();
    }

    /**
     * Get widget configs from admin part as array $cfg=>$value
     * @return array|null
     */
    public function getConfigs(): ?array
    {
        $realName = Str::lastIn(self::$class, '\\', true);
        return AppRecord::getConfigs('widget', $realName);
    }

    public function display(): ?string {}

    public function init(): void {}

    /**
     * Check if widget is enabled. For native widget is always enabled
     * @param string $name
     * @return bool
     */
    public static function enabled($name): bool
    {
        return true;
    }
}
