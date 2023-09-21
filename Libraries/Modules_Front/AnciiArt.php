<?php

namespace LibMy;

# NOTE:
#  .
#  .
#  .

/**
 * Класс для хранения и выдачи заготовленных ANCII-Артов
 * Пока набросок.
 */
class AnciiArt
{
    # - ### ### ###

    private static $anciiArtsJson = ['' => '',];

    # - ### ### ###

    public function __construct() {    }
    public function __destruct()  {    }

    # - ### ### ###

    # TODO
    public static function dumpAll()
    {
        #foreach(self::$anciiArtsJson as $char => $stringsArr )
        #    dump(self::univStringNum($char));

        #dd(self::univStringNum('End'));
    }

    # - ### ### ###
    #   NOTE: Вывод артов из JSON

    public static function oneStrMakerDD($text)
    {
        dd(json_encode($text));
    }

    public static function makeArt( $key, $action='DUMP' )
    {
        # TODO: Проверка существования
        $val = json_decode(self::$anciiArtsJson[$key]);

        switch($action)
        {
            case 'DUMP': dump($val); break;
            case 'DD': dd($val); break;
            case 'echo': echo $val; break;  # TODO: Возможн осразу с <pre>
        }
    }

    # - ### ### ###
    #   NOTE:

    /*
    public static function ()    {  self::univ(''       );  }
    public static function DD()  {  self::univ('','DD'  );  }
    public static function ECHO(){  self::univ('','echo');  }
    # */

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
