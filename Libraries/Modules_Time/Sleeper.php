<?php

namespace LibMy;


/**
 * Универсальный обратный отсчет+sleep с выводом динамическим прогресса на страницу.
 * Очень удобно и красиво. Часто используется.
 * По сути просто аналог sleep(), но с визуалом.
 */
class Sleeper
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    # IMPORTANT = Обязательно надо    ob_end_flush();

    # WORK FULL
    public static function sleeper($waitTimeSec, $comment='' , $deleteAfter=false)
    {
        # - ###

        if( count(ob_get_status()) !== 0 )
            dump('SLEEPER => ob_get_status!==0 - надо прописать ob_end_clean() | ob_end_flush()');

        # - ###

        # Чтоб разные таймеры не путались
        $divId = 'waitDiv-'.random_int(1000, 9999);

        # NOTE: Могут быть ошибки на +- 0.1 из-за округлений
        #  По факту может ждаться немного больше времени ибо вычисления.

        # Округлит вниз до 2 знака  2.1234 -> 21часть -> 2.1сек
        $partsCount = (int) floor($waitTimeSec*10); # 0.x

        $symbolDone = '#';
        $symbolWait = '_';

        #$secSeparator = ')<br>(';
        $secSeparator = ' | ';

        foreach( range(1,$partsCount) as $i )
        {
            #$i = (int)$i; # Так как '1.0'
            usleep(100000); # = 0.1 sec

            $arr = [ ];
            $arr['PARTS_FULL'] = $partsCount;
            $arr['PARTS_FULL_SEC'] = $arr['PARTS_FULL']*0.1;
            $arr['PARTS_DONE'] = $i;
            $arr['PARTS_DONE_SEC'] = $i*0.1;
            $arr['PARTS_WAIT'] = $partsCount-$i;
            $arr['PARTS_WAIT_SEC'] = $arr['PARTS_WAIT']*0.1;
            $arr['PARTS_PERCENT'] = floor( $arr['PARTS_DONE']/$arr['PARTS_FULL']*100 );


            $strFinal = '';
            foreach( range(0,$partsCount-1) as $ii )
            {
                # NOTE: str_pad здесь очень неудобен.
                if( $ii !== 0 ) if( $ii % 10 === 0 ) $strFinal .= $secSeparator;
                if( $ii <= $i ) $strFinal .= $symbolDone;
                if( $ii > $i ) $strFinal .= $symbolWait;

            }

            $text = 'SLEEP | Всего: '.$arr['PARTS_FULL_SEC'].'сек ';
            $text .= '| Осталось: '.$arr['PARTS_WAIT_SEC'].'сек ';
            $text .= '| '.$arr['PARTS_PERCENT'].'% ';
            $text .= '| '.$comment;

            $progress = '( '.$strFinal.' )';


            $style = 'border: .6rem solid red; padding: 10px; background-color: lawngreen; font-size:large; font-weight: bold;';
            if( $i === 1 )
                echo '<div id="'.$divId.'" style="'.$style.'"> '.$text.'<br>'.$progress.' </div>';
            else
                echo '<script> document.querySelectorAll(\'#'.$divId.'\')[0].innerHTML = \''.$text.'<br>'.$progress.'\'; </script>';

            flush(); # Обязательно
        }

        if($deleteAfter)
            echo '<script> document.querySelectorAll(\'#'.$divId.'\')[0].remove(); </script>';
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
