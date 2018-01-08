<?php

namespace Ffcms\Core\Helper\Type;

/**
 * Class Obj. Helper to work with "Object" type.
 * @package Ffcms\Core\Helper\Type
 */
class Obj
{
    /**
     * Get object properties
     * @param object $obj
     * @return array
     */
    public static function properties(object $obj): array
    {
        return get_object_vars($obj);
    }
}
