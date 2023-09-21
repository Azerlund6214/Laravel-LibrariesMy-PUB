<?php

namespace LibMy;



/** Класс сдля работы со временем в классическом и Unix формате
 * Все нативное, без библиотек.
 */
class DaterUC
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    public static function getClassic_Date():string
    {
        return date("Y-m-d"); # 2023-04-24
    }
    public static function getClassic_Time():string
    {
        return date("H:i:s"); # 22:12:01
    }
    public static function getClassic_Full():string
    {
        return date("Y-m-d H:i:s"); # 2023-04-24 22:12:01
    }


    public static function makeUnix($minXX, $hourXX, $dayXX, $monXX, $yearXXXX):int
    {
        return mktime($hourXX,$minXX,0,$monXX,$dayXX,$yearXXXX);
    }

    # - ### ### ###
    #   NOTE:

    public static function convertUnixToClassic($u):string
    {
        return gmdate("Y-m-d H:i:s", $u);
        #date('c',$u);
    }

    public static function convertClassicToUnix($dateText):int
    {
        return strtotime($dateText);
    }

    # - ### ### ###
    #   NOTE:

    public static function modifyUnix_AddMinute(int $u, $cnt=1):int
    {
        return ($u + 60*$cnt );
    }
    public static function modifyUnix_AddHour(int $u, $cnt=1):int
    {
        return ($u + 3600*$cnt );
    }
    public static function modifyUnix_AddDay(int $u, $cnt=1):int
    {
        return ($u + 86400*$cnt );
    }

    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
