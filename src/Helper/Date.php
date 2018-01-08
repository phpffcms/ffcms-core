<?php

namespace Ffcms\Core\Helper;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Obj;

/**
 * Class Date. Helper to work with dates and timestamps
 * @package Ffcms\Core\Helper
 */
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
        // convert timestamp to date format
        if (Any::isInt($rawDate)) {
            $rawDate = date(\DateTime::ATOM, $rawDate);
        }

        try {
            $object = new \DateTime($rawDate);
            return $object->format($format);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Try to convert string to unix timestamp format. Return 0 if converting is failed
     * @param string $date
     * @return int
     */
    public static function convertToTimestamp($date): int
    {
        // make clearly cast $date into string type
        // Some times, $date could be an object with __toString() magic
        return (int)strtotime((string)$date);
    }

    /**
     * Humanize date format
     * @param string|int $raw
     * @return bool|string
     */
    public static function humanize($raw)
    {
        // convert to timestamp
        $timestamp = $raw;
        // raw can be instance of eloquent active record object, convert to str
        if (!Any::isInt($raw)) {
            $timestamp = self::convertToTimestamp((string)$timestamp);
        }

        // calculate difference between tomorrow day midnight and passed date
        $diff = time() - $timestamp;

        // date in future, lets return as is
        if ($diff < 0) {
            return self::convertToDatetime($timestamp, static::FORMAT_TO_SECONDS);
        }

        // calculate delta and make offset sub. Maybe usage instance of Datetime is better, but localization is sucks!
        $deltaSec = $diff % 60;
        $diff /= 60;

        $deltaMin = $diff % 60;
        $diff /= 60;

        $deltaHour = $diff % 24;
        $diff /= 24;

        $deltaDays = ($diff > 1) ? (int)floor($diff) : (int)$diff;

        // sounds like more then 1 day's ago
        if ($deltaDays > 1) {
            // sounds like more then 2 week ago, just return as is
            if ($deltaDays > 14) {
                return self::convertToDatetime($timestamp, static::FORMAT_TO_HOUR);
            }

            return App::$Translate->get('DateHuman', '%days% days ago', ['days' => (int)$deltaDays]);
        }

        // sounds like yesterday
        if ($deltaDays === 1) {
            return App::$Translate->get('DateHuman', 'Yestarday, %hi%', ['hi' => self::convertToDatetime($timestamp, 'H:i')]);
        }

        // sounds like today, more then 1 hour ago
        if ($deltaHour >= 1) {
            return App::$Translate->get('DateHuman', '%h% hours ago', ['h' => $deltaHour]);
        }

        // sounds like last hour ago
        if ($deltaMin >= 1) {
            return App::$Translate->get('DateHuman', '%m% minutes ago', ['m' => $deltaMin]);
        }

        // just few seconds left, lets return it
        return App::$Translate->get('DateHuman', '%s% seconds ago', ['s' => $deltaSec]);
    }
}
