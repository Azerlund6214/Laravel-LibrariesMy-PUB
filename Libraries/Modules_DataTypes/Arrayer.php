<?php

namespace LibMy;



/** Универсальный класс для работы с МАССИВАМИ. */
class Arrayer
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    public static function isAsoc( $arr )
    {
        # Самый топовый
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    public static function isAsoc_v1( $arr )
    {
        return (count(array_filter(array_keys($arr),'is_string')) === 0);
    }

    public static function isAsoc_v2( $arr )
    {
        # Спорный
        return (array_values($arr) === $arr);
    }


    public static function regenerateKeysNumeric( &$arr )
    {
        return array_values($arr);
    }

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
