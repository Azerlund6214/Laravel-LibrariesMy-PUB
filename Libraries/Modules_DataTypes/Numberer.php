<?php

namespace LibMy;



/**
 * Важный класс для проведения высокоточных, строго детерминированных операций над числами.<br>
 * Этот класс _СЧИТАЕТ_ДЕНЬГИ_ !!!!   ЛЮБЫЕ ошибки и неточности категорически недопустимы.<br>
 * Во всех методах не предусмотрена "Защита от дурака". Предполагается только корректное использование.<br>
 *
 * Что бы избежать неоднозначности, потери точности и неявных приведений - ВСЕ результаты выдаются в виде СТРОКИ.<br>
 *
 * ДатаВремя создания: 190321 1834 + 180921 полная переделка
 */
class Numberer
{
    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    /**
     * Полный аналог number_format. Просто удобная обертка.
     * @param int|float $number Обрабатываемое число.
     * @param int $accuracy Число знаков после запятой
     * @return int|float
     */
    public static function format($number, $accuracy)
    {
        return number_format($number,$accuracy,'.','');
    }
    # = Округляет от 0.49 и 0.51,  Ненадежное решение
    # Не юзать



    # NOTE: Протестировано руками.
    # IMPORTANT: Используется в биллингах и тд.

    /** IMPORTANT FINAL TESTED
     * Округление для денег - ФИАТ. С нулями, 2 знака.
     * @param int|float|string $amount Обрабатываемое число.
     * @return string
     */
    public static function roundBasicFiat($amount):string
    {
        return self::roundFloat_Down($amount, 2,true);
        # return self::format($amount,2);
    }
    
    # NOTE: roundBasicCrypto(...) - Убрано в соседний класс
    
	

    # - ### ### ###
    #   NOTE: Округления.
    #    Главные работающие методы.

    # NOTE: Работает идеально, в том числе с добавкой нулей. Корректно работают с отрицательными.
    #  Все протестировано и проверено, можно спокойно использовать в финансовых расчетах.

    /** FINAL TESTED
     * Округляет ВВЕРХ до ближайшего целого разряда. 1.120003 до 2 разряда превратится в 1.13
     * Точка отсчета округления НЕ 0.49/0.51 ,а 0.000001  Суть: Если хоть немного больше, то округляем вверх.
     * @param int|float|string $number Обрабатываемое число.
     * @param int $precision Число знаков после запятой. Неотрицательное.
     * @param bool $needZeros Добавлять ли нули в конец, если число получилось коротким.
     * @return string
     */
    public static function roundFloat_Up  ($number, $precision, $needZeros=false):string
    {
        # Тут черная магия, главное что работает.
        $fig = (int) str_pad('1', $precision+1, '0');
        $res = (ceil($number * $fig) / $fig);

        #dd($res);

        if( $needZeros ) # Добавляю нули если в числе их не хватало изначально.
        {
            if(strstr($res, '.'))
            { # Число дробное
                # Выясняю текущую длину дробной части.
                $rightPartLen = strlen(explode('.',$res)[1]);

                if($rightPartLen < $precision) # Если не хватает знаков, то дописываю нули в конец
                    $res = $res.str_pad('', $precision-$rightPartLen,'0');
            }
            else
            { # Число без дроби - просто добавляю нужное количество нулей и точку
                $res = $res.'.'.str_pad('', $precision,'0');
            }
        }

            #$res = self::format($number, $precision);

        return $res;
    }

    /** FINAL TESTED
     * Округляет ВНИЗ до ближайшего целого разряда. 1.1299999 до 2 разряда превратится в 1.12
     * Точка отсчета округления НЕ 0.49/0.51 ,а 0.000001  Суть: Если хоть немного ниже целого, то округляем вниз.
     * @param int|float|string $number Обрабатываемое число.
     * @param int $precision Число знаков после запятой. Неотрицательное.
     * @param bool $needZeros Добавлять ли нули в конец, если число получилось коротким.
     * @return string
     */
    public static function roundFloat_Down($number, $precision, $needZeros=false):string
    {
        # Тут черная магия, главное что работает.
        $fig = (int) str_pad('1', $precision+1, '0');
        $res = (floor($number * $fig) / $fig);

        if( $needZeros ) # Добавляю нули если в числе их не хватало изначально.
        {
            if(strstr($res, '.'))
            { # Число дробное
                # Выясняю текущую длину дробной части.
                $rightPartLen = strlen(explode('.',$res)[1]);

                if($rightPartLen < $precision) # Если не хватает знаков, то дописываю нули в конец
                    $res = $res.str_pad('', $precision-$rightPartLen,'0');
            }
            else
            { # Число без дроби - просто добавляю нужное количество нулей и точку
                $res = $res.'.'.str_pad('', $precision,'0');
            }
        }

        return $res;
    }

    public static function roundFloat_Debug()
    {
        $numbers = array(
            1.120001 ,
            1.129999 ,
            1.12 ,
            1.19 ,
            1.1 ,
            1.9 ,
            1. ,
            1 ,
            0.01 ,
            0.09 ,
            -0.11 ,
            -0.10 ,
            -0.09 ,
            -0.01
        );

        $precision = 5;
        $zeros = true;

        $text = '';

        dump( "Точность исходных чисел = $precision" );
        dump( "Добавка нулей = ".$zeros );


        for ($i=0 ; $i<=30 ; $i++)
        {
            $one = - RandomH::getOneRandomFloat(1,9,$precision);

            $precisionForThis = RandomH::getOneRandomInt(1, $precision-1);
            $text .= "Дано:  $one  приводим к точности $precisionForThis".PHP_EOL;
            $text .= "UP:    ".self::roundFloat_Up($one, $precisionForThis,$zeros).PHP_EOL;
            $text .= "DOWN:  ".self::roundFloat_Down($one, $precisionForThis,$zeros).PHP_EOL;

            $text .= '--------------------------------------'.PHP_EOL;
        }

        dd($text,'End '.__METHOD__);
    }



    # - ### ### ###
    #   NOTE: Округление до целого. По сути просто обертки.

    /** FINAL TESTED
     * Округление до целого, вверх.  1.0001 -> 2
     * @param int|float|string $number
     * @return string
     */
    public static function roundInt_Up  ( $number ):string
    {
        return ceil($number);
    }

    /** FINAL TESTED
     * Округление до целого, вниз.  1.99 -> 1
     * @param int|float|string $number
     * @return string
     */
    public static function roundInt_Down( $number ):string
    {
        return floor($number);
    }

    public static function roundInt_Debug()
    {
        $text = '';

        $precision = 2;

        dump( "Точность исходных чисел = $precision" );

        for ($i=0 ; $i<=30 ; $i++)
        {
            $one = RandomH::getOneRandomFloat(1,9,$precision);

            $precisionForThis = RandomH::getOneRandomInt(1, $precision-1);
            $text .= "Дано:  $one  приводим к точности $precisionForThis".PHP_EOL;
            $text .= "UP:    ".self::roundInt_Up($one).PHP_EOL;
            $text .= "DOWN:  ".self::roundInt_Down($one).PHP_EOL;

            $text .= '--------------------------------------'.PHP_EOL;
        }

        dd($text,'End '.__METHOD__);
    }



    # - ### ### ###
    #   NOTE: Округление по кратному (коэффициенту).

    /** FINAL TESTED
     * Округление ВВЕРХ до ближайшего кратного числа.
     * Например при коэфф=50 : 99->100  101->150 0->0  35->50
     * @param int|float|string $number Обрабатываемое число.
     * @param int|float|string $onePart Коэффициент. (Минимальный размер одной части числа)
     * @return string
     */
    public static function roundToMultiple_Up  ($number, $onePart):string
    {
        # Разделим число на коэффициент, а результат округлим в большую сторону.
        # Потом умножим число на округленный коэффициент

        # Считается количество вхождений(дробное 3.5 раз), округляется ВВЕРХ (4.0) и умножается на целое( 3.0 раза * 1 часть )
        return ceil($number / $onePart) * $onePart;
    }

    /** FINAL TESTED
     * Округление ВНИЗ до ближайшего кратного числа.
     * Например при коэфф=50 : 99->50  101->100 0->0  35->0  746->700
     * @param int|float|string $number Обрабатываемое число.
     * @param int|float|string $onePart Коэффициент. (Минимальный размер одной части числа)
     * @return string
     */
    public static function roundToMultiple_Down($number, $onePart):string
    {
        # Разделим число на коэффициент, а результат округлим в большую сторону.
        # Потом умножим число на округленный коэффициент

        # Считается количество вхождений(дробное 3.5 раз), округляется ВНИЗ(3.0) и умножается на целое( 3.0 раза * 1 часть )
        return floor($number / $onePart) * $onePart;
    }

    public static function roundToMultiple_Debug()
    {
        $text = '';
        $koeff = 5;

        dump( "Целевая кратность = $koeff" );

        for ($i=0 ; $i<=30 ; $i++)
        {
            $one = RandomH::getOneRandomInt(0,16);

            $text .= "Дано:  $one  ".PHP_EOL;
            $text .= "UP:    ".self::roundToMultiple_Up($one,$koeff).PHP_EOL;
            $text .= "DOWN:  ".self::roundToMultiple_Down($one,$koeff).PHP_EOL;

            $text .= '--------------------------------------'.PHP_EOL;
        }

        dd($text,'End '.__METHOD__);
    }



    # - ### ### ###
    #   NOTE: Проверка типов числа.

    /** FINAL TESTED
     * Проверить число по всем показателям сразу.
     * @param int|float|string $number
     * @return array
     */
    public static function getFullNumberInfo($number):array
    {
        $INFO = array();

        $INFO['RAW'] = $number;
        $INFO['RAWSTR'] = (string)$number;
        $INFO['TYPE'] = gettype($number);

        $INFO['IS_INT'] = is_integer($number);
        $INFO['IS_FLOAT'] = is_float($number);
        $INFO['IS_DOUBLE'] = is_double($number);
        $INFO['IS_NUMERIC'] = is_numeric($number);
        $INFO['IS_INFINITY'] = is_infinite($number);
        $INFO['IS_FRACTIONAL'] = self::isFractional($number);
        $INFO['IS_EXPONENTIAL'] = self::isExponential($number);

        return $INFO;
    }

    /** FINAL TESTED
     * Является ли число дробным + НЕ экспанентой.  3.57142
     * @param int|float|string $number
     * @return bool
     */
    public static function isFractional($number):bool
    {
        $str = (string) $number;

        # Если экспанента, то отбраковываю.
        if( self::isExponential($str) )
            return false;

        # Обязательно должен быть разделитель.
        if( strpos($str,'.') )
            return true;

        # Если вдруг его нет + не экспанента, то это не дробное. Например Int
        return false;
    }

    /** FINAL TESTED
     * Является ли число экспоненциальным.  9.2233720368548E+18  5.0E+19  7E-10
     * @param int|float|string $number
     * @return bool
     */
    public static function isExponential($number):bool
    {
        $str = (string) $number;

        if( strpos($str,'E') ) return true;
        if( strpos($str,'e') ) return true;
        if( strpos($str,'+') ) return true;

        return false;
    }



    # - ### ### ###
    #   NOTE:

    /** FINAL TESTED
     * Убрать лишние нули из конца числа.  9.22000 -> 9.22
     * @param int|float|string $number
     * @return string
     */
    public static function removeExtraZeros($number):string
    {
        return (string) ((string) $number * 1);
    }

    public static function debugZeros()
    {
        $amountArr = [
            2  ,  2.00  ,  2.001  ,  2.04000  ,
            '2' , '2.00' , '2.001' , '2.04000' ,
            '2.75473000' ,
        ];

        $arrInfo = '';
        foreach( $amountArr as $one )
        {
            $arrInfo .= "Вход:  $one (".gettype($one).")".PHP_EOL;
            $res = self::removeExtraZeros($one);
            $arrInfo .= "Выход: $res (".gettype($res).")".PHP_EOL;
            $arrInfo .= '==== ==== ==== ==== ==== ===='.PHP_EOL;
        }

        dd($arrInfo, 'End '.__METHOD__);
    }




    # - ### ### ###
    #   NOTE: Всякое отовсюду.

    


    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:

    /**  NOTE: Пока убрал, надо додумывать, проверять и тестить.
     * Truncate a float number, example:
     *      truncate(-1.49799, 2); // returns -1.49
     *      truncate(  .49979, 3); // returns  0.499
     * @param float $number Float number to be truncate
     * @param int $precision Number of precision
     * @return float
    function truncateFloat($number, $precision=0)
    {   # Взято с php.net, не переписывал.
    if(($p = strpos($number, '.')) !== false) {
    $number = floatval(substr($number, 0, $p + 1 + $precision));
    }
    return $number;
    }
     */

    # - ### ### ###
    #   NOTE:


    /* метод парсящий кол-во знаком после запятой.
    сначала поиск "."
    если нет, то 0
    еще убирать лишние нули через уже написанный метод
    если да, то to string + explode + strlen()[1]
    возможно уже есть */


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



     **
     * Является ли число нормальным целочисленным.
     * @param int|float|string $number
     * @return bool
    public static function isInteger($number):bool
    {
        $str = (string) $number;

        is_integer($number)

        if( self::isFractional ($number) ) return false;
        if( self::isInfinity   ($number) ) return false;
        if( self::isExponential($number) ) return false;

        return true;
    }




    # NOTE: КРИВОЙ!!! Округляет от x.5
    # К ближайшему, с заданной точностью.   Если точность больше чем есть, то НЕ добавляет нули.
    public static function roundToFloat___NO_USE($number, $accuracy, $mode='DOWN'):string
    {
        if( ! in_array($mode, ['UP','DOWN']))
            dd(__METHOD__.' Неверный тип округления - '.$mode);

        # https://www.php.net/manual/ru/function.round.php

        if( $mode === 'UP' )
            $mode = PHP_ROUND_HALF_UP;   # 1.1 -> 2
        else
            $mode = PHP_ROUND_HALF_DOWN; # 1.9 -> 1

        $res =  round( $number, $accuracy, $mode );

        # Добавляю недостающие нули, если их изначально не хватало.
        $resZeros = self::format($res, $accuracy);

        return $resZeros;
    }





    */
} # End class
