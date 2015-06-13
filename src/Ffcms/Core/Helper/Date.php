<?php

namespace Ffcms\Core\Helper;

class Date
{
    const FORMAT_TO_DAY = 'd.m.Y';
    const FORMAT_TO_HOUR = 'd.m.Y H:i';
    const FORMAT_TO_SECONDS = 'd.m.Y H:i:s';

    const FORMAT_SQL_TIMESTAMP = 'Y-m-d H:i:s';

    /**
     * Try to convert string to date time format
     * @param string $rawDate
     * @param string $format
     * @return string
     */
    public static function convertToDatetime($rawDate, $format = 'd.m.Y')
    {
        if (Object::isLikeInt($rawDate)) { // convert timestamp to date format
            $rawDate = date($format, $rawDate);
        }

        $object = new \DateTime($rawDate);
        return $object->format($format);
    }

    /**
     * Try to convert string to unix timestamp format
     * @param string $date
     * @return int
     */
    public static function convertToTimestamp($date)
    {
        return strtotime($date);
    }
}