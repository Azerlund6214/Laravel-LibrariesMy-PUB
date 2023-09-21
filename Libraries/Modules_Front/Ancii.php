<?php

namespace LibMy;

# NOTE: "Шрифты"
#  Топ = https://patorjk.com/software/taag/#p=display&f=Graffiti&t=Type%20Something%20
#  Тот топорный = https://www.ascii-art-generator.org/
#  Хз = https://textkool.com/en/ascii-art-generator?hl=default&vl=default&font=Red%20Phoenix&text=Your%20text%20here%20

/**
 * Незаменимый класс для генерации любых текстов в ANCII-виде для последующего дампа.
 * Теперь вместо 1 еле видной строчки будет красивая и легко читаемая портянка.
 * Только английский и символы. Для нераспознанного будет автозамена.
 * Часто используется.
 */
class Ancii
{
    # - ### ### ###

    private static $strMaxLenRecommend = 18; # Если больше, то дампер сворачивает строку. (Конкретно у меня)

    private static $anciiCharsRefs = [

        # NOTE: Я знаю, что их можно ужать до 1 строки. Пусть пока будет так.

        '0' => ['  ###  ',' #   # ','#     #','#     #','#     #',' #   # ','  ###  ', ],
        '1' => ['  #  ',' ##  ','# #  ','  #  ','  #  ','  #  ','#####', ],
        '2' => [' ##### ','#     #','      #',' ##### ','#      ','#      ','#######', ],
        '3' => [' ##### ','#     #','      #',' ##### ','      #','#     #',' ##### ', ],
        '4' => ['#      ','#    # ','#    # ','#    # ','#######','     # ','     # ', ],
        '5' => ['#######','#      ','#      ','###### ','      #','#     #',' ##### ', ],
        '6' => [' ##### ','#     #','#      ','###### ','#     #','#     #',' ##### ', ],
        '7' => ['#######','#    # ','    #  ','   #   ','  #    ','  #    ','  #    ', ],
        '8' => [' ##### ','#     #','#     #',' ##### ','#     #','#     #',' ##### ', ],
        '9' => [' ##### ','#     #','#     #',' ######','      #','#     #',' ##### ', ],

        'A' =>[ '   #   ',
                '  # #  ',
                ' #   # ',
                '#     #',
                '#######',
                '#     #',
                '#     #', ],
        'B' =>[ '###### ',
                '#     #',
                '#     #',
                '###### ',
                '#     #',
                '#     #',
                '###### ', ],
        'C' =>[ ' ##### ',
                '#     #',
                '#      ',
                '#      ',
                '#      ',
                '#     #',
                ' ##### ', ],
        'D' => ['###### ',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                '###### ', ],
        'E' => ['#######',
                '#      ',
                '#      ',
                '#####  ',
                '#      ',
                '#      ',
                '#######', ],
        'F' => ['#######',
                '#      ',
                '#      ',
                '#####  ',
                '#      ',
                '#      ',
                '#      ', ],
        'G' =>[ ' ##### ',
                '#     #',
                '#      ',
                '#  ####',
                '#     #',
                '#     #',
                ' ##### ', ],
        'H' =>[ '#     #',
                '#     #',
                '#     #',
                '#######',
                '#     #',
                '#     #',
                '#     #', ],
        'I' =>[ '###',
                ' # ',
                ' # ',
                ' # ',
                ' # ',
                ' # ',
                '###', ],
        'J' =>[ '      #',
                '      #',
                '      #',
                '      #',
                '#     #',
                '#     #',
                ' ##### ', ],
        'K' =>[ '#    #',
                '#   # ',
                '#  #  ',
                '###   ',
                '#  #  ',
                '#   # ',
                '#    #', ],
        'L' =>[ '#      ',
                '#      ',
                '#      ',
                '#      ',
                '#      ',
                '#      ',
                '#######', ],
        'M' =>[ '#     #',
                '##   ##',
                '# # # #',
                '#  #  #',
                '#     #',
                '#     #',
                '#     #', ],
        'N' =>[ '#     #',
                '##    #',
                '# #   #',
                '#  #  #',
                '#   # #',
                '#    ##',
                '#     #', ],
        'O' =>[ '#######',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                '#######', ],
        'P' =>[ '###### ',
                '#     #',
                '#     #',
                '###### ',
                '#      ',
                '#      ',
                '#      ', ],
        'Q' =>[ ' ##### ',
                '#     #',
                '#     #',
                '#     #',
                '#   # #',
                '#    # ',
                ' #### #', ],
        'R' =>[ '###### ',
                '#     #',
                '#     #',
                '###### ',
                '#   #  ',
                '#    # ',
                '#     #', ],
        'S' =>[ ' ##### ',
                '#     #',
                '#      ',
                ' ##### ',
                '      #',
                '#     #',
                ' ##### ', ],
        'T' =>[ '#######',
                '   #   ',
                '   #   ',
                '   #   ',
                '   #   ',
                '   #   ',
                '   #   ', ],
        'U' =>[ '#     #',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                '#     #',
                ' ##### ', ],
        'V' =>[ '#     #',
                '#     #',
                '#     #',
                '#     #',
                ' #   # ',
                '  # #  ',
                '   #   ', ],
        'W' =>[ '#     #',
                '#  #  #',
                '#  #  #',
                '#  #  #',
                '#  #  #',
                '#  #  #',
                ' ## ## ', ],
        'X' =>[ '#     #',
                ' #   # ',
                '  # #  ',
                '   #   ',
                '  # #  ',
                ' #   # ',
                '#     #', ],
        'Y' =>[ '#     #',
                ' #   # ',
                '  # #  ',
                '   #   ',
                '   #   ',
                '   #   ',
                '   #   ', ],
        'Z' =>[ '#######',
                '     # ',
                '    #  ',
                '   #   ',
                '  #    ',
                ' #     ',
                '#######', ],


        '-' =>[ '     ',
                '     ',
                '     ',
                '#####',
                '     ',
                '     ',
                '     ', ],
        '_' =>[ '       ',
                '       ',
                '       ',
                '       ',
                '       ',
                '       ',
                '#######', ],
        '+' =>[ '     ',
                '  #  ',
                '  #  ',
                '#####',
                '  #  ',
                '  #  ',
                '     ', ],
        '#' =>[ '  # #  ',
                '  # #  ',
                '#######',
                '  # #  ',
                '#######',
                '  # #  ',
                '  # #  ', ],
        '=' =>[ '     ',
                '     ',
                '#####',
                '     ',
                '#####',
                '     ',
                '     ', ],
        '!' =>[ '###',
                '###',
                '###',
                ' # ',
                '   ',
                '###',
                '###', ],
        '(' =>[ '  ##',
                ' #  ',
                '#   ',
                '#   ',
                '#   ',
                ' #  ',
                '  ##', ],
        ')' =>[ '##  ',
                '  # ',
                '   #',
                '   #',
                '   #',
                '  # ',
                '##  ', ],
        '.' =>[ '    ',
                '    ',
                '    ',
                '    ',
                '    ',
                '### ',
                '### ', ],
        ' ' =>[ '       ', # Пробел
                '       ',
                '       ',
                '       ',
                '       ',
                '       ',
                '       ', ],
        'BAD' =>[ ' ? ', # Заглушка для пустых
                ' ? ',
                ' ? ',
                ' ? ',
                ' ? ',
                ' ? ',
                ' ? ', ],
    ];


    # - ### ### ###

    public function __construct() {    }
    public function __destruct()  {    }

    # - ### ### ###

    public static function dumpAllDD()
    {
        $finStr = '';
        foreach(self::$anciiCharsRefs as $char => $stringsArr )
            $finStr .= self::anyText($char,'').PHP_EOL.PHP_EOL;

        dump($finStr);
        dd(self::anyText('End'));
    }

    # - ### ### ###
    #   NOTE:
    # - ### ### ###
    #   NOTE: Вывод любого текста

    public static function anyTextDD($string , $space='  ')
    {
        dd(self::anyText($string,$space));
    }
    public static function anyTextDump($string , $space='  ')
    {
        dump(self::anyText($string,$space));
        flush();
    }
    public static function anyText($string , $space='  ')
    {
        $fin = [ '', '', '', '', '', '', '' ]; # 7 пустых

        $string = strtoupper($string);

        foreach(str_split($string) as $char )
        {
            if( ! array_key_exists( $char, self::$anciiCharsRefs) )
                $char = 'BAD'; # Заглушечный символ

            $fin[0] .= self::$anciiCharsRefs[$char][0].$space;
            $fin[1] .= self::$anciiCharsRefs[$char][1].$space;
            $fin[2] .= self::$anciiCharsRefs[$char][2].$space;
            $fin[3] .= self::$anciiCharsRefs[$char][3].$space;
            $fin[4] .= self::$anciiCharsRefs[$char][4].$space;
            $fin[5] .= self::$anciiCharsRefs[$char][5].$space;
            $fin[6] .= self::$anciiCharsRefs[$char][6].$space;
        }

        return implode(PHP_EOL, $fin);
    }

    public static function success()  { dump(self::anyText('success'));     }
    public static function ok()       { dump(self::anyText('ok'));          }
    public static function fail()     { dump(self::anyText('fail'));        }
    public static function failed()   { dump(self::anyText('failed'));      }
    public static function end()      { dump(self::anyText('end'));         }
    public static function ended()    { dump(self::anyText('ended'));       }
    public static function auchtung() { dump(self::anyText('auchtung !!1')); }
    public static function sleep()    { dump(self::anyText('sleep...'));    }

    
    # - ### ### ###
    #   NOTE:


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
