<?php

namespace LibMy;


# Из Laravel

# Модули отдельные

# Модули логики

# Хелперы

# Модели

/**
 *
 * ДатаВремя создания: 220921 2201
 */
class PlasticCards
{
    # - ### ### ###

    public static $NAME1 = ''; #
    public static $NAME2 = array(); #

    public static $ccExamples = array(
        'VISA' => ['4000001234567899','4916338506082832','4556015886206505','4539048040151731','4024007198964305','4716175187624512'],
        'MASTER' => ['5110000134567579','5280934283171080','5456060454627409','5331113404316994','5259474113320034','5442179619690834'],
        'MIR' => ['2201382000000013','2200770212727079','2202200223948454'],
        'AMEX' => ['346129497012763','378282256310005','345936346788903','377669501013152','373083634595479','370710819865268','371095063560404'],
        'DISC' => ['6011894492395579','6011388644154687','6011880085013612','6011652795433988','6011375973328347'],
        #'JCB' => [],
        #'MAES' => [],
        'TEST' => ['1234567864725837'],
    );

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    # NOTE: ВСЕ виды ошибок https://developer.rbk.money/docs/payments/refs/testcards/

    # - ### ### ###
    #   NOTE:
    # TODO:

    public static function getCardEmitent($ccNum)
    {
        if(self::checkCard_Mir($ccNum)) return 'MIR';
        if(self::checkCard_Visa($ccNum)) return 'VISA';
        if(self::checkCard_Master($ccNum)) return 'MASTER';
        if(self::checkCard_Amex($ccNum)) return 'AMEX';
        if(self::checkCard_JCB($ccNum)) return 'JCB';
        if(self::checkCard_Discover($ccNum)) return 'DISCOVER';

        return 'UNKNOWN';
    }

    public static function debugTestEmitents()
    {
        $fin = 'Реальный эмитент => номер => как определил. (должно совпасть)'.PHP_EOL;
        foreach( array_keys(self::$ccExamples) as $emitent )
        {
            $fin .= '====='.PHP_EOL;
            foreach( self::$ccExamples[$emitent] as $cc )
                $fin .= "$emitent = $cc => ".self::getCardEmitent($cc).PHP_EOL;
        }
        dd($fin);
    }


    # - ### ### ###
    #   NOTE: Отдельно по эмитентам.

    # NOTE: не факт.  перепроверять паттерн.
    public static function checkCard_Mir($ccNum)
    {
        # ОФФ = Первые 4 цифры номера карты должны быть в диапазоне 2200-2204;   от 16 до 19 цифр;
        $pattern = "/^([2]{1})([0-9]{12,15})$/"; //
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }

    public static function checkCard_Visa($ccNum)
    {
        # Все номера карт Visa начинаются с 4. Новые карты имеют 16 цифр. Старые карты имеют 13.
        $pattern = "/^([4]{1})([0-9]{12,15})$/"; // Visa
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }

    public static function checkCard_Master($ccNum)
    {
        # Все номера MasterCard начинаются с номеров от 51 до 55. Все имеют 16 цифр.
        $pattern = "/^([51|52|53|54|55]{2})([0-9]{14})$/"; // Mastercard
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }


    public static function checkCard_Amex($ccNum)
    {
        # Номера карт American Express начинаются с 34 или 37 и имеют 15 цифр.
        $pattern = "/^([34|37]{2})([0-9]{13})$/"; // American Express
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }

    public static function checkCard_JCB($ccNum)
    {
        #
        # Карты JCB, начинающиеся с 2131 или 1800, имеют 15 цифр. Карты JCB, начинающиеся с 35, имеют 16 цифр.
        $pattern = "/^(?:2131|1800|35\d{3})\d{11}$/"; // JCB  #NOTE: Вообще не уверен в регулярке.
        # Оригинал  ^(?:2131|1800|35\d{3})\d{11}$  я добавил слеши
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }

    public static function checkCard_Discover($ccNum)
    {
        # Discover card numbers begin with 6011 or 65. All have 16 digits.
        $pattern = "/^6(?:011|5[0-9]{2})[0-9]{12}$/"; // JCB  #NOTE: Вообще не уверен в регулярке.
        # Оригинал  ^6(?:011|5[0-9]{2})[0-9]{12}$  я добавил слеши
        return (bool) preg_match($pattern,$ccNum); # 1(да) 0(нет) false(ошибка)
    }



    /*  Топорные регулярки по первым цифрам.
        electron: /^(4026|417500|4405|4508|4844|4913|4917)\d+$/,
        maestro: /^(5018|5020|5038|5612|5893|6304|6759|6761|6762|6763|0604|6390)\d+$/,
        dankort: /^(5019)\d+$/,
        interpayment: /^(636)\d+$/,
        unionpay: /^(62|88)\d+$/,
        visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
        mastercard: /^5[1-5][0-9]{14}$/,
        amex: /^3[47][0-9]{13}$/,
        diners: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
        discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
        jcb: /^(?:2131|1800|35\d{3})\d{11}$/

        Другое:
        Visa  ^4[0-9]{12}(?:[0-9]{3})?$
        Master  ^5[1-5][0-9]{14}$
        Express  ^3[47][0-9]{13}$
        Diners  ^3(?:0[0-5]|[68][0-9])[0-9]{11}$
        Discover  ^6(?:011|5[0-9]{2})[0-9]{12}$
        JCB  ^(?:2131|1800|35\\d{3})\\d{11}$
    */

    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE: Шаблон метода

    /**
     * Описание тут
     * @todo 123123
     * @param bool $Protocol Описание
     * @return mixed|string|bool|object|array Описание
     */
    public static function template($Protocol)
    {

        dd('End'.__CLASS__.''.__METHOD__);
    }

    /*
    Обновлено 09.06.2020.
        Номера карт начинаются с
        2-Мир
        3- American Express, JCB International, Diners Club
        ____30,36,38-Diners Club
        ____31,35-JCB International
        ____34,37-American Express
        4- VISA
        5- MasterCard, Maestro
        ____50,56,57,58-Maestro
        ____51,52,53,54,55-MasterCard
        6- Maestro, China UnionPay, Discover
        ____60-Discover
        ____62 - China UnionPay
        ____63, 67 - Maestro
        7-УЭК

        Первые 6 цифр - это БИН ( Банковский Идентификационный Номер ) выпустившего карту банка.
        Подробнее см. здесь: https://pikabu.ru/story/bin_bankovskoy_kartyi_4961206
        А это сервис, позволяющий по БИН узнать название банка: https://psm7.com/bin-card
    */
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
