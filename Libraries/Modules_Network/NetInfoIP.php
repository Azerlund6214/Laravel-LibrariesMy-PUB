<?php

namespace LibMy;


/**
 * Класс для любой работы с IP.
 * Включая кучу готовых методов пробива.
 */
class NetInfoIP
{
    # - ### ### ###
    #   NOTE:
	
    public static function getIpInfoToken( )
    {
    	return env('TOKEN__IPINFO__REAL');
    	#return authTokenStrings::$TOKEN__IPINFO__REAL;
    }

    # WORK
    # NOTE: До 50к запросов в месяц | Реал токен из ENV
    public static function getIpCurr_IpInfo( $ip )
    {
        return RequestCURL::GET(
            'https://ipinfo.io/json',
            ['token'=>self::getIpInfoToken()])['ANSWER_JSON'];
    }
    public static function getIpInfo_IpInfo( $ip )
    {
        return RequestCURL::GET(
            'https://ipinfo.io/'.$ip.'/json',
            ['token'=>self::getIpInfoToken()])['ANSWER_JSON'];
    }

    # CONCEPT: https://ipapi.co/   УЛЬТРА САЙТ
	#  Большой список хороших https://overcoder.net/q/2730/как-получить-ip-адрес-клиента-с-помощью-javascript

    # - ### ### ###
    #   NOTE:

    /** FINAL WORK
     * <br>Получить внешний ip через ВОСЕМЬ сервисов. (+ статистика времени запросов.)
     * <br> !!! Только ручной вызов где надо.
     * @param bool $onlyMain Получить только через один, самый надежный способ.
     * @return array
     */
    public static function getExternalIpInfo( $onlyMain = true )
    {
        $INFO = [
            'DESC' => 'Публичные IP сервера. Все получены через внешние сервисы.' ,

            # Считаю главным, тк профильный сайт с кучей инфы.
            '1' => 'EMPTY' , # https://ipinfo.io # Большой json инфы.

            '2' => 'EMPTY' , # http://ip4only.me/api/
            '3' => 'EMPTY' , # http://checkip.dyndns.com/
            '4' => 'EMPTY' , # https://checkip.amazonaws.com/  # Около 500мс
            '5' => 'EMPTY' , # https://ipecho.net/plain
            '6' => 'EMPTY' , # https://wtfismyip.com/text # Еще есть json с инфой
            '7' => 'EMPTY' , # https://api.ipify.org   # Около 500мс

            # В среднем около 300мс на 1 запрос.
            'TIME' => [] ,
        ];
        # - ###

        # NOTE: Не существует способов узнать свой внешний IP через PHP.
        #  Это возможно исключительно через обращение к внещним сайтам.
        #  Если у сети много выходных маршрутизаторов, то внешние IP могут быть разными.

        # - ###

        $timer = new TimerMy();;
        try{
            # JSON
            $INFO['1'] = json_decode(file_get_contents('https://ipinfo.io/json') , true)['ip'];
        }catch( \Exception $e ){
            $INFO['1'] = 'ERROR';
        }
        $INFO['TIME']['1'] = $timer->getTimeMs();

        if( $onlyMain )
            return $INFO;

        # - ###

        $timer = new TimerMy();
        try{
            # IPv4,111.11.11.11,Remaining fields reserved for future use,,,
            $INFO['2'] = explode(',' , file_get_contents('http://ip4only.me/api/'))[1];
        }catch( \Exception $e ){
            $INFO['2'] = 'ERROR';
        }
        $INFO['TIME']['2'] = $timer->getTimeMs();

        $timer = new TimerMy();
        try{
            # Current IP Address: 111.11.11.11 + голые теги хтмл хед боди
            $INFO['3'] = explode('<' , trim(explode(':' , file_get_contents('http://checkip.dyndns.com/'))[1]))[0];
        }catch( \Exception $e ){
            $INFO['3'] = 'ERROR';
        }
        $INFO['TIME']['3'] = $timer->getTimeMs();

        $timer = new TimerMy();
        try{
            # Сразу IP, 2 строка пустая, поэтому трим
            $INFO['4'] = trim(file_get_contents('https://checkip.amazonaws.com/'));
        }catch( \Exception $e ){
            $INFO['4'] = 'ERROR';
        }
        $INFO['TIME']['4'] = $timer->getTimeMs();

        $timer = new TimerMy();
        try{
            # Сразу ip
            $INFO['5'] = file_get_contents('https://ipecho.net/plain');
        }catch( \Exception $e ){
            $INFO['5'] = 'ERROR';
        }
        $INFO['TIME']['5'] = $timer->getTimeMs();

        $timer = new TimerMy();
        try{
            # Сразу IP
            $INFO['6'] = trim(file_get_contents('https://wtfismyip.com/text'));
        }catch( \Exception $e ){
            $INFO['6'] = 'ERROR';
        }
        $INFO['TIME']['6'] = $timer->getTimeMs();

        $timer = new TimerMy();
        try{
            # Сразу IP
            $INFO['7'] = trim(file_get_contents('https://api.ipify.org'));
        }catch( \Exception $e ){
            $INFO['7'] = 'ERROR';
        }
        $INFO['TIME']['7'] = $timer->getTimeMs();


        #try {
        #    $INFO['EXTERNAL_'] = file_get_contents('');
        #}catch(\Exception $e){  $INFO['EXTERNAL_'] = 'ERROR';  }

        # - ###

        return $INFO;

        # - ###
    }

    /** FINAL WORK
     * <br>Получить внешний ip через 3 костыльных способа. (+ статистика времени запросов.)
     * <br> !!! Только ручной вызов где надо.
     * @return array
     */
    public static function getExternalIpInfoProd()
    {
        $INFO = [
            'DESC-0' => '!!!!!! ТЕСТИТЬ в продакшене.  99% что будут ошибки.' ,
            'DESC-1' => 'Публичные IP сервера. Все получены через извращенские, но рабочие методы.' ,
            'DESC-2' => 'Работает ТОЛЬКО в продакшене, на реальном хостинге. Иначе выдаст локальный IP.' ,
            'DESC-3' => 'Под проксифаером тоже дает локальные' ,

            'getHost' => 'EMPTY' , #
            'socket' => 'EMPTY' , #
            'dns-a' => 'EMPTY' , #

            'TIME' => [] ,
        ];
        # - ###

        $timer = new TimerMy();;
        try{
            $INFO['getHost'] = gethostbyname(gethostname()); # $_SERVER['SERVER_NAME']
        }catch( \Exception $e ){
            $INFO['getHost'] = 'ERROR';
        }
        $INFO['TIME']['getHost'] = $timer->getTimeMs();

        # - ###

        # NOTE: Суть - Открываю подключение к днс гугла и получаю метаинфу.
        $timer = new TimerMy();
        try{
            $sock = socket_create(AF_INET , SOCK_DGRAM , SOL_UDP);
            $res = socket_connect($sock , '8.8.8.8' , 53); # 53 - дефолтный порт для установления соединений.
            # NOTE: IP можно поставить любой публичный.
            // You might want error checking code here based on the value of $res
            socket_getsockname($sock , $addr);
            socket_shutdown($sock);
            socket_close($sock);
            $INFO['socket'] = $addr; // Ta-da! The IP address you're connecting from
        }catch( \Exception $e ){
            $INFO['socket'] = 'ERROR';
        }
        $INFO['TIME']['socket'] = $timer->getTimeMs();

        # - ###

        # Получаем IP через записи днс, но это совсем изврат + только продакшен.
        $timer = new TimerMy();
        try{
            $dnsARecord = dns_get_record($_SERVER['HTTP_HOST'] , DNS_A);
            if( $dnsARecord )
                $INFO['dns-a'] = 'IPv4: ' . $dnsARecord[0]['ip'];

            $dnsARecord = dns_get_record($_SERVER['HTTP_HOST'] , DNS_AAAA);
            if( $dnsARecord )
                $INFO['dns-a'] = 'IPv6: ' . $dnsARecord[0]['ip'];

        }catch( \Exception $e ){
            $INFO['dns-a'] = 'ERROR';
        }
        $INFO['TIME']['dns-a'] = $timer->getTimeMs();

        # - ###

        return $INFO;

        # - ###
    }


    # - ### ### ###
    #   NOTE:


    /**
     * Получить подробную информацию и локации пользователя
     * NOTE: Сервис выдержал 100 запросов подряд (никаких ошибок, 500 и тд).
     * @param string $ip
     * @return array|string = Массив с данныи ЛИБО строка с ошибкой
     */
    private static function getIpInfo_Geoplug( $ip )
    {
        # - ###
        dd(__METHOD__,'Не юзать, лучше соседний.');
        # - ###
        # http://cccp-blog.com/koding/opredelenie-goroda-po-ip-v-php

        if ( ! filter_var($ip, FILTER_VALIDATE_IP))
            return "BAD IP => $ip";

        $ip = trim($ip);

        $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip )) ;

        #SF::PRINTER($ip_data);


        if( ! $ip_data )
            return false;
        #return "IP Request error. Не удалось сделать запрос к серверу. Ответ не получен.";

        #if( $ip_data->geoplugin_status != 200 && $ip_data->geoplugin_status != 206 )
        #    return false;
        #return "IP Request error. Status not 200. $ip => ". $ip_data->geoplugin_status;


        $final_data = array(
            'status'       => @$ip_data->geoplugin_status,
            'ip'           => @$ip_data->geoplugin_request,
            'city'         => @$ip_data->geoplugin_city,
            'region'       => @$ip_data->geoplugin_region,
            'region_code'  => @$ip_data->geoplugin_regionCode,
            'region_name'  => @$ip_data->geoplugin_regionName,

            'country'      => @$ip_data->geoplugin_countryName,
            'country_code' => @$ip_data->geoplugin_countryCode,

            'continent'    => @$ip_data->geoplugin_continentName,

            'latitude'     => @$ip_data->geoplugin_latitude,
            'longitude'    => @$ip_data->geoplugin_longitude,
            'loc_accuracy' => @$ip_data->geoplugin_locationAccuracyRadius,
        );


        # SF::PRINTER($final_data);

        return $final_data;
    }
    # NOTE: Не юзаю


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
