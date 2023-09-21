<?php

namespace LibMy;

# Из Laravel
use Carbon\Carbon;


/**
 * Хелпер для удобной работы с датами через Carbon
 * ДатаВремя создания: 210121
 */
class CarbonDT
{
    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Получение первичных дат.

    /**
     * Просто получить текущую дату.
     * @param bool $asString Вернуть сразу в виде строки
     * @return Carbon|string
     */
    public static function getNow($asString=true)
    {
        if($asString)
            return Carbon::now()->toDateTimeString();
        return Carbon::now();
    }

    /**
     * Просто получить текущую дату сразу со сдвигом на москву (+3ч).
     * @param bool $asString Вернуть сразу в виде строки
     * @return Carbon|string
     */
    public static function getNowMsk($asString=true)
    {
        if($asString)
            return self::shiftToTimezoneMsk(Carbon::now() )->toDateTimeString();
        return self::shiftToTimezoneMsk(Carbon::now() );
    }


    /*
    public static function getTestDateFuture()
    {
        return Carbon::now()->addMonths(3)->addDays(2)->addHours(6)->addMinutes(35)->addSeconds(41);
    }
    public static function getTestDatePast()
    {
        return Carbon::now()->subMonths(3)->subDays(12)->subHours(6)->subMinutes(35)->subSeconds(41);
    }
    */

    # - ### ### ###
    #   NOTE: Получение разницы дат.

    /**
     * Получить абсолютную разницу дат в Секундах/Минутах/Часах/Днях.
     * 2 формата => дробные и целые числа.
     * @param Carbon $firstDtCarbon
     * @param Carbon|string $secDT = "NOW" либо Carbon
     * @return array
     */
    public static function diffAbsolute(Carbon $firstDtCarbon, $secDT='NOW')
    {
        if( $secDT === 'NOW' )
            $secDT = self::getNow();

        $final = array();

        # Целое
        $final[] = $firstDtCarbon->diffInSeconds($secDT);
        $final[] = $firstDtCarbon->diffInMinutes($secDT);
        $final[] = $firstDtCarbon->diffInHours($secDT);
        $final[] = $firstDtCarbon->diffInDays($secDT);

        # Float
        $final[] = $firstDtCarbon->floatDiffInSeconds($secDT);
        $final[] = $firstDtCarbon->floatDiffInMinutes($secDT);
        $final[] = $firstDtCarbon->floatDiffInHours($secDT);
        $final[] = $firstDtCarbon->floatDiffInDays($secDT);

        return $final;
    }

    /**
     * Получить разницу дат в Секундах/Минутах/Часах/Днях. Все отдельно + разные форматы + доп инфа.
     * Каждый вид времени отдельно.
     * @param Carbon $firDT
     * @param Carbon|string $secDT = "NOW" либо Carbon
     * @param bool $needFormat = Генерировать ли форматированные даты?
     * @return array
     */
    public static function diffReadable(Carbon $firDT, $secDT='NOW', $needFormat=false)
    {
        if( $secDT === 'NOW' )
            $secDT = self::getNow();

        $dtInterval  = $firDT->diff($secDT);

        $final = array();
        $final['sec']   = $dtInterval->s;
        $final['min']   = $dtInterval->i;
        $final['hour']  = $dtInterval->h;
        $final['day']   = $dtInterval->d;
        $final['month'] = $dtInterval->m;
        $final['year']  = $dtInterval->y;

        if( $needFormat )
        {
            $final['format-1'] = $dtInterval->format('%yy %mm %dd %hh:%im:%ss');
            $final['format-2'] = $dtInterval->format('%yy %mm %dd %hh %im %ss'); # Без :
            $final['format-3'] = $dtInterval->format('%D:%H:%I:%S');
            $final['format-4'] = $dtInterval->format('%d:%h:%i:%s');
            $final['format-5'] = $dtInterval->format('%I:%S');
            $final['format-6'] = $dtInterval->format('%i:%s');
        }

        $final['full-days'] = $dtInterval->days;
        $final['is-future'] =   (bool) $dtInterval->invert;
        $final['is-past']   = ! (bool) $dtInterval->invert;

        return $final;
    }

    /**
     * Получить разницу дат в Секундах/Минутах/Часах/Днях - Что-то одно за раз. Одним целым числом.
     * @param string $interval = (SEC/SECOND/SECONDS) (MIN/MINUTES) (HOUR/HOURS) (DAY/DAYS)
     * @param Carbon $firDT
     * @param Carbon|string $secDT = "NOW" либо Carbon
     * @return int
     */
    public static function diffIn($interval , $firDT, $secDT='NOW')
    {
        if( $secDT === 'NOW' )
            $secDT = self::getNow();

        if( is_string($firDT ) ) $firDT = Carbon::createFromTimeString($firDT);
        if( is_string($secDT ) ) $secDT = Carbon::createFromTimeString($secDT);

        switch($interval)
        {
            case "SEC":
            case "SECOND":
            case "SECONDS": return $firDT->diffInSeconds($secDT);

            case "MIN":
            case "MINUTES": return $firDT->diffInMinutes($secDT);

            case "HOUR":
            case "HOURS":   return $firDT->diffInHours($secDT);

            case "DAY":
            case "DAYS":    return $firDT->diffInDays($secDT);

            default: dd(__CLASS__."@diffIn - дефолт считка - неверный интервал. - ".$interval);
        }

    }

    # - ### ### ###
    #   NOTE: Универсальное изменение времени.

    # IMPORTANT - Важный метод.
    /**
     * Любые операции над временем. Сдвиги в любую сторону на любую величину.
     * @param string $direction = '+' или '-'
     * @param Carbon $carbonDT
     * @param string $changes = '5y 1mo 15d 13h 33m 34s' = на сколько двигать
     * @param string $delimiter = 'пробел'
     * @return Carbon
     */
    public static function modify( $direction , Carbon $carbonDT, $changes, $delimiter=' ' )
    {

        $arrChanges = explode( $delimiter, trim($changes) );

        #dump($carbonDT);

        # Слегка костыльно, но по-другому сделать нельзя.
        switch($direction)
        {
            case 'sub':
            case 'SUB':
            case '-':
                foreach( $arrChanges as $one )
                {

                    if( strstr($one, 'y') )  { $one = str_replace('y',  '', $one); $carbonDT->subYears(  $one); continue; }
                    if( strstr($one, 'd') )  { $one = str_replace('d',  '', $one); $carbonDT->subDays(   $one); continue; }
                    if( strstr($one, 'h') )  { $one = str_replace('h',  '', $one); $carbonDT->subHours(  $one); continue; }
                    if( strstr($one, 'mo') ) { $one = str_replace('mo', '', $one); $carbonDT->subMonths( $one); continue; }
                    if( strstr($one, 'm') )  { $one = str_replace('m',  '', $one); $carbonDT->subMinutes($one); continue; }
                    if( strstr($one, 's') )  { $one = str_replace('s',  '', $one); $carbonDT->subSeconds($one); continue; }
                }
                break;

            case 'add':
            case 'ADD':
            case '+':
                foreach( $arrChanges as $one )
                {
                    if( strstr($one, 'y') )  { $one = str_replace('y',  '', $one); $carbonDT->addYears(  $one); continue; }
                    if( strstr($one, 'd') )  { $one = str_replace('d',  '', $one); $carbonDT->addDays(   $one); continue; }
                    if( strstr($one, 'h') )  { $one = str_replace('h',  '', $one); $carbonDT->addHours(  $one); continue; }
                    if( strstr($one, 'mo') ) { $one = str_replace('mo', '', $one); $carbonDT->addMonths( $one); continue; }
                    if( strstr($one, 'm') )  { $one = str_replace('m',  '', $one); $carbonDT->addMinutes($one); continue; }
                    if( strstr($one, 's') )  { $one = str_replace('s',  '', $one); $carbonDT->addSeconds($one); continue; }
                }
                break;

            default: dd(__METHOD__.' Дефолт свитча = Неверное направление! => '.$direction );

        }

        return $carbonDT;
    }

    # - ### ### ###
    #   NOTE: Сдвиг по временным зонам.

    /**
     * Сдвинуть дату до нужного часового пояса.
     * @param Carbon $carbonDT Дата для обработки
     * @param int $targetZone Целевой пояс - любое ЦЕЛОЕ число => -1 -8 5 32
     * @return Carbon Дата со сдвигом
     */
    public static function shiftToTimezone( Carbon $carbonDT, int $targetZone )
    {
        if( ! is_string($targetZone) && ! is_numeric($targetZone) )
            dd(__METHOD__.' Пришла НЕ строка и не число!',$targetZone );

        return $carbonDT->timezone($targetZone);
    }

    /**
     * Превращает дату UTC(+0) в MSK(+3)
     * @param Carbon $carbonDT Дата для обработки
     * @return Carbon Дата со сдвигом
     */
    public static function shiftToTimezoneMsk( Carbon $carbonDT )
    {
        return self::shiftToTimezone($carbonDT, 3);
    }

    # - ### ### ###
    #   NOTE: Справочная.

    /*  'Y-m-d H:i:s', '2018-12-10 09:00:00'

        d - The day of the month (from 01 to 31)
        D - A textual representation of a day (three letters)
        j - The day of the month without leading zeros (1 to 31)
        l (lowercase 'L') - A full textual representation of a day
        N - The ISO-8601 numeric representation of a day (1 for Monday, 7 for Sunday)
        S - The English ordinal suffix for the day of the month (2 characters st, nd, rd or th. Works well with j)
        w - A numeric representation of the day (0 for Sunday, 6 for Saturday)
        z - The day of the year (from 0 through 365)
        W - The ISO-8601 week number of year (weeks starting on Monday)
        F - A full textual representation of a month (January through December)
        m - A numeric representation of a month (from 01 to 12)
        M - A short textual representation of a month (three letters)
        n - A numeric representation of a month, without leading zeros (1 to 12)
        t - The number of days in the given month
        L - Whether it's a leap year (1 if it is a leap year, 0 otherwise)
        o - The ISO-8601 year number
        Y - A four digit representation of a year
        y - A two digit representation of a year
        a - Lowercase am or pm
        A - Uppercase AM or PM
        B - Swatch Internet time (000 to 999)
        g - 12-hour format of an hour (1 to 12)
        G - 24-hour format of an hour (0 to 23)
        h - 12-hour format of an hour (01 to 12)
        H - 24-hour format of an hour (00 to 23)
        i - Minutes with leading zeros (00 to 59)
        s - Seconds, with leading zeros (00 to 59)
        u - Microseconds (added in PHP 5.2.2)
        e - The timezone identifier (Examples: UTC, GMT, Atlantic/Azores)
        I (capital i) - Whether the date is in daylights savings time (1 if Daylight Savings Time, 0 otherwise)
        O - Difference to Greenwich time (GMT) in hours (Example: +0100)
        P - Difference to Greenwich time (GMT) in hours:minutes (added in PHP 5.1.3)
        T - Timezone abbreviations (Examples: EST, MDT)
        Z - Timezone offset in seconds. The offset for timezones west of UTC is negative (-43200 to 50400)
        c - The ISO-8601 date (e.g. 2013-05-05T16:34:42+00:00)
        r - The RFC 2822 formatted date (e.g. Fri, 12 Apr 2013 12:01:05 +0200)
        U - The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
    */

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
