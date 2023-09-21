<?php

namespace LibMy;



/** Класс для работы с API сайта vak-sms.com
 * Аренда и использование виртуальных номеров в 1 клик.
 * Ручной статичный вызов с передачей токена.
 */
class apiVakSms
{
    # - ### ### ###

    public function __construct($token) {  $this->token = $token;  }
    public function __destruct()  {    }

    # - ### ### ###

    public $token;
    public static $baseUrl = 'https://vak-sms.com/api/';

    # - ### ### ###
    # NOTE: Заметки
    #  https://vak-sms.com/api/vak/
    #  Все через GET
    #
    #
    #

    /*
        beeline
        lycamobile
        megafon
        mts
        mtt
        patriot
        rostelecom
        tele2
        tinkoff
        vtbmobile
        yota
    */

    # - ### ### ###
    #   NOTE:


    # Сервисы за 1руб.   pm=Protonmail   kf=KFC

    # - ### ### ###
    #   NOTE: Получение инфы

    public static function getBalance($T)
    {
        $method = 'getBalance';

        $RES = self::sendQueryVak_GET($T,$method);

        return $RES['ANSWER_JSON']['balance'];
    }

    public static function getCountNumber_OGON_BEE($T){ return self::getCountNumber_ANY($T,'og','beeline'); }
    public static function getCountNumber_OGON_MTS($T){ return self::getCountNumber_ANY($T,'og','mts'); }
    public static function getCountNumber_OGON_MTT($T){ return self::getCountNumber_ANY($T,'og','mtt'); }
    public static function getCountNumber_OGON_ALL($T){ return self::getCountNumber_ANY($T,'og'); }
    public static function getCountNumber_TEST_ALL($T){ return self::getCountNumber_ANY($T,'pm'); }
    public static function getCountNumber_ANY($T, $service, $operator='')
    {
        $method = 'getCountNumber';
        $par = [
            'service' => $service,
            'country' => 'ru',
            'operator' => $operator,
        ];

        $RES = self::sendQueryVak_GET($T,$method,$par);

        return $RES['ANSWER_JSON'][$service];
    }



    public static function buyNumber_TEST_ALL($T){ return self::buyNumber_ANY($T,'pm'); }
    public static function buyNumber_ANY($T, $service, $operator='')
    {
        $method = 'getNumber';
        $par = [
            'service' => $service,
            'country' => 'ru',
            'operator' => $operator,
        ];

        $RES = self::sendQueryVak_GET($T,$method,$par);

        #if( isset( $RES['error'] ) )
        #    return [];

        # {"tel": 79991112233, "idNum": "3adb61376b8f4adb90d6e758cf8084fd"}
        return $RES['ANSWER_JSON'];
    }

    public static function number_SetStatus_Cancel($T, $idNum)
    {
        $RES = self::sendQueryVak_GET($T,'setStatus',[ 'idNum' => $idNum, 'status' => 'end' ]);
        return $RES['ANSWER_JSON'];
    }
    public static function number_SetStatus_NeedNewSms($T, $idNum)
    {
        $RES = self::sendQueryVak_GET($T,'setStatus',[ 'idNum' => $idNum, 'status' => 'send' ]);
        return $RES['ANSWER_JSON'];
    }

    public static function number_GetCodes_ALL($T, $idNum):array
    {
        $RES = self::sendQueryVak_GET($T,'getSmsCode',[ 'idNum' => $idNum , 'all' => true ]);
        return $RES['ANSWER_JSON']['smsCode'] ?? [ ];
    }
    public static function number_GetCodes_LAST($T, $idNum):string
    {
        $RES = self::sendQueryVak_GET($T,'getSmsCode',[ 'idNum' => $idNum ]);
        return $RES['ANSWER_JSON']['smsCode'] ?? '';
    }


    # - ### ### ###
    #   NOTE: Базовое

    public static function sendQueryVak_GET( $T , $method , $dataQuery=[] )
    {
        $dataQuery['apiKey'] = $T;

        $RES = RequestCURL::GET(self::$baseUrl . $method , $dataQuery);

        self::onEveryResult($RES);

        return $RES;
    }

    public static function onEveryResult($RES)
    {
        if($RES['IS_ERROR'])
            dd($RES);

        dump(__METHOD__);
        dump($RES);
    }


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
