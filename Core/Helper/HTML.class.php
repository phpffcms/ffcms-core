<?php

namespace Core\Helper;

class HTML {

    public static function strip($string, $allowed = null)
    {
        return strip_tags($string, $allowed);
    }
}