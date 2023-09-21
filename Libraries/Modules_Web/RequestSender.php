<?php

namespace LibMy;



/**
 * Класс с набором нативных методов для запросов.
 * Слабенький, использовать аккуратно.
 * Соседний класс с CURL на 300% лучше и удобнее во всем.
 *
 * Срочно нужен рефакторинг, тк пока просто сгреб все методы в одно место.
 */ # Написан 190123
class RequestSender
{
    # NOTE:
    #  - По умолчанию использую CURL, а не FGC
    #  - При любых ошибках - false
    #  -
    #  - HTML страницы, полученный от FGC и CURL 100% одинаковы.
    #  -

    # - ### ### ###

    public static $curlOpt_UA = 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0';

    public static $RESP_CODES = [
        # Потом   код => Текст
    ];

    # - ### ### ###
    #   NOTE:

    /**
     * Получить заголовки с любого сервера
     * @param string $URL = адрес сайта, Обязательно с протоколом!
     * @param integer $Arr_type = Тип массива на выходе = 1-Асоциативный 0-Одномерный
     * @return mixed  assoc_arr[]=>[] , bool false (если ответ был пуст)
     */
    public static function getHeaders_PHP( $URL = "https://yandex.ru" , $Arr_type = 1 )
    {
        $Answer = @get_headers( $URL , $Arr_type ); # Без 1 будет не асоциативный(Все в кучу)

        if( empty($Answer) )
            return false;

        return $Answer;
    }

    /**
     * Получить заголовки с любого сервера
     * @param string $URL = адрес сайта, Обязательно с протоколом!
     * @return string = 3-значный код ответа "404" и тд ,  bool = false (если ошибка)
     */
    public static function getHTTPResponse_PHP( $URL = "https://yandex.ru" )
    {
        /*
        $ch = curl_init('http://yoururl/');
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $c = curl_exec($ch);
        return curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // */

        $Answer = @get_headers( $URL , 1 ); # Без 1 будет не асоциативный(Все в кучу)

        if( empty($Answer) )
            return false;

        return substr($Answer[0], 9, 3 ); // HTTP/1.1 404 Not Found

    }


    # - ### ### ###
    #   NOTE:


    public static function getHTML_FGC($targetUrl)
    {
        try{
            return file_get_contents($targetUrl);
        }catch(\Exception $e){
            return false;
        }
    }

    public static function getHTML($targetUrl, $arrParams=[])
    {
        return self::curl_sendUniv($targetUrl, $arrParams,'GET')['CONTENT'];
    }

    public static function getHeaders($targetUrl, $arrParams=[])
    {
        return self::curl_sendUniv($targetUrl, $arrParams,'HEAD')['CONTENT'];
    }

    public static function getHTTPResponse($targetUrl, $arrParams=[])
    {
        return self::curl_sendUniv($targetUrl, $arrParams,'GET')['INFO']['http_code'];
    }


    # - ### ### ###
    #   NOTE: Методы-Прокладки

    public static function curl_sendGet($targetUrl, $arrParams=[])
    {
        return self::curl_sendUniv($targetUrl, $arrParams,'GET');
    }

    public static function curl_sendPost($targetUrl, $arrParams=[])
    {
        return self::curl_sendUniv($targetUrl, $arrParams,'POST');
    }

    # - ### ### ###
    #   NOTE: Низкоуровневые методы

    # Фулл разнесен
    public static function curl_sendUniv($targetUrl, $arrParams, $method)
    {
        # - ###



        # - ###

        $paramsEncoded = '';
        if( ! empty($arrParams) ) # Что бы в ссылке не висело пустого '?'
            $paramsEncoded = http_build_query($arrParams);

        # - ###

        $handler  = curl_init();

        switch($method)
        {
            case 'GET':
                    curl_setopt($handler, CURLOPT_URL,$targetUrl.'?'.$paramsEncoded);
                    curl_setopt($handler, CURLOPT_HEADER, false); # Вставлять ли хедер в начало

                    break;

            case 'POST':
                    curl_setopt($handler, CURLOPT_URL, $targetUrl);
                    curl_setopt($handler, CURLOPT_HEADER, false); # Вставлять ли хедер в начало
                    curl_setopt($handler, CURLOPT_POST, true);
                    curl_setopt($handler, CURLOPT_POSTFIELDS, $paramsEncoded);
                    break;

        }

        # Универсальные настройки
        #curl_setopt($handler, CURLOPT_USERAGENT, self::$curlOpt_UA);

        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        # Вернуть результат в виде строки, а не выводить на страницу



        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true); # Следовать редиректам
        curl_setopt($handler, CURLOPT_MAXREDIRS, 4); #

        #curl_setopt($handler, CURLOPT_AUTOREFERER, true); #
        #curl_setopt($handler, CURLOPT_REFERER, ''); #

        # - ###

        $content = curl_exec($handler);

        # Вся CURL инфа о запросе.  Есть все тайминги. Вызывать dd только после закрытия
        $arRequest = curl_getinfo($handler); # Вызывать только ДО закрытия.

        curl_close($handler);

        # - ###

        return [ 'INFO' => $arRequest , 'CONTENT' => $content ];

        # - ###
    }

    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:







    # - ### ### ###
    #   NOTE:

    /*

        // trying to open URL to process PerfectMoney Spend request
        $f=fopen($url, 'rb');

        if($f===false)
        {
            $this->responseReceived = false;
            return false;
        }

        // getting data
        $out = "";
        while( ! feof($f) )
            $out .= fgets($f);
        fclose($f);


    */

    private function post_json_request($url, $data=[]) {
        $postdata = http_build_query($data);
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context  = stream_context_create($opts);
        return json_decode(file_get_contents($url, false, $context), true);
    }


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
