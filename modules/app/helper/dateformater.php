<?php

namespace App\Helper;

/**
 * Helper class for quick date formating
 */
class DateFormater
{

    const BASE_DATETIME_FORMAT = 'j.n. Y H:i';
    const BASE_DATE_FORMAT = 'j.n. Y';
    const BASE_TIME_FORMAT = 'H:i';

    /**
     * 
     * @param type $texttime
     * @return type
     */
    public static function t2dt($texttime)
    {
        if (!empty($texttime)) {
            return date(self::BASE_DATETIME_FORMAT, strtotime($texttime));
        } else {
            return date(self::BASE_DATETIME_FORMAT, time());
        }
    }

    /**
     * 
     * @param type $texttime
     * @return type
     */
    public static function t2d($texttime)
    {
        if (!empty($texttime)) {
            return date(self::BASE_DATE_FORMAT, strtotime($texttime));
        } else {
            return date(self::BASE_DATE_FORMAT, time());
        }
    }

    /**
     *
     * @param type $texttime
     * @return type
     */
    public static function t2t($texttime)
    {
        if (!empty($texttime)) {
            return date(self::BASE_TIME_FORMAT, strtotime($texttime));
        } else {
            return date(self::BASE_TIME_FORMAT, time());
        }
    }

    /**
     *
     * @param type $date
     */
    public static function g2dn($date)
    {
        if (!empty($date)) {
            return date('j', strtotime($date));
        } else {
            return date('j', time());
        }
    }

    /**
     *
     * @param type $date
     */
    public static function g2yy($date)
    {
        if (!empty($date)) {
            return date('Y', strtotime($date));
        } else {
            return date('Y', time());
        }
    }
    
    /**
     *
     * @param type $date
     */
    public static function g2mn($date)
    {
        $czechMonths = array(1 => 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec');
        
        if (!empty($date)) {
            $month = date('n', strtotime($date));
            return $czechMonths[$month];
        } else {
            $month = date('n', time());
            return $czechMonths[$month];
        }
    }
}
