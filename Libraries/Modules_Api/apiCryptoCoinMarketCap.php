<?php

namespace LibMy;



# - ### ### ### ###
# - ###
	/* # = # = # = # */
	
	# Это оооооочень старый класс, сейчас бы писал вообще по другому.
	# Не стал разбирать и переписывать.
	# Был в продакшене.
	
	/* # = # = # = # */
# - ###
# - ### ### ### ###





/**
 * Class
 *
 * Альтернативы
 * https://medium.com/coinmonks/best-crypto-apis-for-developers-5efe3a597a9f
 * https://towardsdatascience.com/top-5-best-cryptocurrency-apis-for-developers-32475d2eb749
 *
 *
 * https://coinmarketcap.com/api/documentation/v1/
 * Надо регаться, вериф почты.  333 беспл запроса в день
 * Дальше или больше = платить 30$
 *
 */

class apiCryptoCoinMarketCap # 180321 2014
{
    # - ######################################

    public static $NAME1 = ''; #
    public static $NAME2 = array(); #


    public static $BASE_URL = 'https://pro-api.coinmarketcap.com'; #

    # https://pro.coinmarketcap.com/account
    # https://coinmarketcap.com/api/documentation/v1/
    public static $AUTH_KEYS_ARR = array(
        'da5b1735-2f26-4062-83c8-5682bfe2b013', # Тестовый одноразовый акк - лог пар = tewij19149@boersy.com
        #'', #
        #'', #
    );

    # - ######################################

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ######################################
    # - ######################################
    #   NOTE:

    # IMPORTANT: Главный обработчик.
    public static function request( $reqUrl, $params, $key )
    {
        $fin = array(
            'ERROR-HAS' => false,
            'ERROR-MSG' => '',
            'ERROR-CODE' => '',
            'RESPONSE-RAW' => '',
            'RESPONSE-FULL' => '',
        );

        # - ###  Защиты от дурака

        if( $reqUrl[0] !== '/' )
            dd(__METHOD__.' забыт слеш в начале reqUrl', $reqUrl);

        if( ! is_array($params) )
            dd(__METHOD__.' Params не массив', $params);

        # - ###

        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: ' . $key
        ];

        # - ###

        $urlFin = self::$BASE_URL . $reqUrl;

        if( ! empty($params) ) # Что бы в ссылке не висело пустого '?'
        {
            $qs = http_build_query($params); // query string encode the parameters
            $request = "{$urlFin}?{$qs}"; // create the request URL
        }
        else
        {
            $request = $urlFin; // create the request URL
        }

        # - ###

        $curl = curl_init(); // Get cURL resource

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $fin['RESPONSE-RAW'] = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request

        # - ###

        $fin['RESPONSE-FULL'] = json_decode($fin['RESPONSE-RAW'], true); // print json decoded response

        #dd($fin['RESPONSE']);

        $fin['ERROR-HAS'] = ( $fin['RESPONSE-FULL']['status']['error_code'] !== 0 );

        if( $fin['ERROR-HAS'] )
        {
            $fin['ERROR-MSG']  = $fin['RESPONSE-FULL']['status']['error_message'];
            $fin['ERROR-CODE'] = $fin['RESPONSE-FULL']['status']['error_code'];
            return $fin;
        }

        $fin['STATUS'] = $fin['RESPONSE-FULL']['status'];
        $fin['DATA']   = $fin['RESPONSE-FULL']['data'];

        unset($fin['RESPONSE-FULL']); # Ибо дублируется

        return $fin;
    }

    # - ######################################
    #   NOTE:

    public static function keyGiveRandom()
    {
        return self::$AUTH_KEYS_ARR[ array_rand(self::$AUTH_KEYS_ARR) ];
    }

    public static function keyCheckLimitExceed($key):bool
    {
        $res = self::requestKeyInfo($key);

        if( $res['ERROR-HAS'] )
            return true;

        #dd($res);
        if( $res['DATA']['usage']['current_minute']['requests_left'] <= 2 )
            return true;

        if( $res['DATA']['usage']['current_day']['credits_left'] <= 8 ) # С запасом, ибо могут быть составные транзакции
            return true;

        if( $res['DATA']['usage']['current_month']['credits_left'] <= 8 )
            return true;

        return false;
    }

    public static function requestKeyInfo( $key )
    {
        $reqUrl = '/v1/key/info';
        $params = [];
        return self::request($reqUrl,$params,$key);
    }

    # - ######################################
    #   NOTE:


    # 13, USD, RUB   USD BTC
    public static function requestPriceConvert( $amount , $from , $to , $key )
    {
        if( $key === 'RANDOM' )
            $key = self::keyGiveRandom();

        $reqUrl = '/v1/tools/price-conversion';
        $params = [
            'amount' => $amount,
            'symbol' => $from,
            'convert' => $to,  # Через запятую. В текущем тарифе ограничение на 1 валюту одновременно.
        ];
        return self::request($reqUrl,$params,$key);
    }

    # Прокладки
    public static function requestPriceConvert_FromUSD($amount , $to , $key )
    {
        if( $key === 'RANDOM' )
            $key = self::keyGiveRandom();

        return self::requestPriceConvert( $amount , 'USD' , $to , $key );
    }
    public static function requestPriceConvert_Slash( $amount , $fromToWithSlash , $key )
    {
        if( $key === 'RANDOM' )
            $key = self::keyGiveRandom();

        if( ! strstr($fromToWithSlash, '/') )
            dd(__METHOD__.' Строка вают пришла без слеша /', $fromToWithSlash);

        $buf = explode('/', $fromToWithSlash);

        return self::requestPriceConvert( $amount , $buf[0] , $buf[1] , $key );
    }
    public static function requestCurrencyRate_InUSD($currency , $key )
    {
        if( $key === 'RANDOM' )
            $key = self::keyGiveRandom();

        return self::requestPriceConvert( 1 , $currency ,'USD' , $key );
    }



    # - ######################################
    #   NOTE:


    # - ######################################
    #   NOTE:

    # - ######################################
    #   NOTE:



    # TODO:
    # TODO:
    # TODO:




    # - ######################################
    # - ######################################
    # - ######################################

    /*   свалка


    */



}
