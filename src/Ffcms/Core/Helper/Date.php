<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\Helper\Type\Obj;

class Date
{
    const FORMAT_TO_DAY = 'd.m.Y';
    const FORMAT_TO_HOUR = 'd.m.Y H:i';
    const FORMAT_TO_SECONDS = 'd.m.Y H:i:s';

    const FORMAT_SQL_TIMESTAMP = 'Y-m-d H:i:s';
    const FORMAT_SQL_DATE = 'Y-m-d';

    /**
     * Try to convert string to date time format
     * @param string|int $rawDate
     * @param string $format
     * @return string|bool
     */
    public static function convertToDatetime($rawDate, $format = 'd.m.Y')
    {
        if (Obj::isLikeInt($rawDate)) { // convert timestamp to date format
            $rawDate = date($format, $rawDate);
        }
        try {
            $object = new \DateTime($rawDate);
            return $object->format($format);
        } catch (\Exception $e) {
            return false;
        }
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