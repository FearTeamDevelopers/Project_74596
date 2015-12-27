<?php

namespace App\Helper;

use THCFrame\Date\Date;

/**
 * Helper class for quick date formating.
 */
class DateFormater
{

    /**
     * @param type $texttime
     *
     * @return type
     */
    public static function t2dt($texttime)
    {
        $date = Date::getInstance();
        return $date->format($texttime, Date::CZ_BASE_DATETIME_FORMAT);
    }

    /**
     * @param type $texttime
     *
     * @return type
     */
    public static function t2d($texttime)
    {
        $date = Date::getInstance();
        return $date->format($texttime, Date::CZ_BASE_DATE_FORMAT);
    }

    /**
     * @param type $texttime
     *
     * @return type
     */
    public static function t2t($texttime)
    {
        $date = Date::getInstance();
        return $date->format($texttime, Date::CZ_BASE_TIME_FORMAT);
    }

    /**
     * @param type $date
     */
    public static function g2dn($date)
    {
        $dateObj = Date::getInstance();
        return $dateObj->getDatePart($date, 'day');
    }

    /**
     * @param type $date
     */
    public static function g2yy($date)
    {
        $dateObj = Date::getInstance();
        return $dateObj->getDatePart($date, 'year');
    }

    /**
     * @param type $date
     */
    public static function g2mn($date)
    {
        $dateObj = Date::getInstance();
        return $dateObj->getMonthName($date);
    }
}
