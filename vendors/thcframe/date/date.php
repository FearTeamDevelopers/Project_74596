<?php

namespace THCFrame\Date;

class Date
{

    const CZ_BASE_DATETIME_FORMAT = 'j.n. Y H:i';
    const CZ_BASE_DATE_FORMAT = 'j.n. Y';
    const CZ_BASE_TIME_FORMAT = 'H:i';
    const SYSTEM_BASE_DATETIME_FORMAT = 'Y-m-d H:i:s';
    const SYSTEM_BASE_DATE_FORMAT = 'Y-m-d';
    const SYSTEM_BASE_TIME_FORMAT = 'H:i:s';

    private static $_instance = null;

    private function __construct()
    {
        
    }

    /**
     * 
     * @return type
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 
     * @param type $datetime
     * @return type
     */
    public function getTimestamp($datetime)
    {
        $date = new \DateTime($datetime);

        return $date->getTimestamp();
    }

    /**
     * 
     * @param type $datetime
     * @param type $format
     * @return \DateTime
     */
    public function format($datetime, $format = 'Y-m-d H:i:s')
    {
        $date = new \DateTime($datetime);
        $date->format($format);

        return $date;
    }

    /**
     * 
     * @param type $format
     * @return type
     * @throws \THCFrame\Date\Exception\Argument
     */
    public function getFormatedCurDate($format = 'cz')
    {
        if (strtolower($format) == 'cz') {
            return $this->format(time(), self::CZ_BASE_DATE_FORMAT);
        } elseif (strtolower($format) == 'system') {
            return $this->format(time(), self::SYSTEM_BASE_DATE_FORMAT);
        } else {
            throw new \THCFrame\Date\Exception\Argument('Unsupported date format');
        }
    }

    /**
     * 
     * @param type $format
     * @return type
     * @throws \THCFrame\Date\Exception\Argument
     */
    public function getFormatedCurDatetime($format = 'cz')
    {
        if (strtolower($format) == 'cz') {
            return $this->format(time(), self::CZ_BASE_DATETIME_FORMAT);
        } elseif (strtolower($format) == 'system') {
            return $this->format(time(), self::SYSTEM_BASE_DATETIME_FORMAT);
        } else {
            throw new \THCFrame\Date\Exception\Argument('Unsupported datetime format');
        }
    }

    /**
     * 
     * @param type $date
     * @return string
     */
    public function monthEnToCz($date)
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

    /**
     * 
     * @param type $datetime
     * @param type $part
     * @return \DateTime
     */
    public function getDatePart($datetime, $part)
    {
        if (!empty($datetime)) {
            $date = new \DateTime($datetime);

            if ($part == 'day') {
                $date->format('j');
                return $date;
            } elseif ($part == 'month') {
                $date->format('n');
                return $date;
            } elseif ($part == 'year') {
                $date->format('Y');
                return $date;
            }
        } else {
            if ($part == 'day') {
                return date('j', time());
            } elseif ($part == 'month') {
                return date('n', time());
            } elseif ($part == 'year') {
                return date('Y', time());
            }
        }
    }

    /**
     * 
     * @param type $startDate
     * @param type $endDate
     * @return type
     */
    public function datediff($startDate, $endDate, $useSign = true)
    {
        $datetime1 = new \DateTime($startDate);
        $datetime2 = new \DateTime($endDate);
        $interval = $datetime1->diff($datetime2);

        if ($useSign) {
            return $interval->format('%R%a');
        } else {
            return $interval->format('%a');
        }
    }

    /**
     * 
     * @param string $originalDate
     * @param string $format
     * @param integer $years
     * @param integer $months
     * @param integer $days
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     * @return string
     */
    public function dateAdd($originalDate, $format = 'Y-m-d', $years = 0, $months = 0, $days = 0, $hours = 0, $minutes = 0, $seconds = 0)
    {
        $date = new \DateTime($originalDate);

        $intervalSpec = 'P';

        if ($years > 0) {
            $intervalSpec .= $years . 'Y';
        }

        if ($months > 0) {
            $intervalSpec .= $months . 'M';
        }

        if ($days) {
            $intervalSpec .= $days . 'D';
        }

        if ($hours > 0 || $minutes > 0 || $seconds > 0) {
            $intervalSpec .= 'T';

            if ($hours > 0) {
                $intervalSpec .= $hours . 'H';
            }

            if ($minutes > 0) {
                $intervalSpec .= $minutes . 'M';
            }

            if ($seconds > 0) {
                $intervalSpec .= $seconds . 'S';
            }
        }

        $date->add(new \DateInterval($intervalSpec));
        return $date->format($format);
    }

}
