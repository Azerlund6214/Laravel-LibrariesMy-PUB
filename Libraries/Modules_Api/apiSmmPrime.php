<?php

namespace LibMy;


/** Класс для работы с API сайта smmprime.com
 * Накрутка просмотров и прочего в телеграм в 1 клик.
 */
class apiSmmPrime
{
    # - ### ### ###
    #   NOTE:

    public static $apiUrl = 'https://smmprime.com/api/v2';
    #public static $apiKey = ''; # Мой

    # - ### ### ###
    #   NOTE:


    public static function sendRequest($arr)
    {
        #$arr['key'] = self::$apiKey;

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, self::$apiUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($arr, '', '&'));



        $out = curl_exec($ch);
        curl_close($ch);

        return json_decode($out,true);
        #return json_encode($out,JSON_UNESCAPED_UNICODE);
    }

    # - ### ### ###
    #   NOTE:

    public static function action_GetBalance($KEY)
    {
        $arr = [
            'key' => $KEY,
            'action' => 'balance',
        ];

        return self::sendRequest($arr);
    }

    public static function action_AddOrder($KEY,$serviceId,$link,$count)
    {
        $arr = [
            'key' => $KEY,
            'action' => 'add',
            'service' => $serviceId,
            'link' => $link,
            'quantity' => $count,
            #'' => '',
        ];

        return self::sendRequest($arr);
    }

    public static function action_OrderInfo($KEY,$orderNum)
    {
        $arr = [
            'key' => $KEY,
            'action' => 'status',
            'order' => $orderNum,
            #'' => '',
        ];

        return self::sendRequest($arr);
    }



    # - ### ### ###
    #   NOTE:


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
