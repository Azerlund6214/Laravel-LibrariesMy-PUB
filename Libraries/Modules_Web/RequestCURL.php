<?php

namespace LibMy;


/**
 * Огромный универсальный класс для любых запросов через CURL.
 * Куча тонких настроек, логов, промежуточных и обработанных данных.
 * Используется постоянно и везде. Отазоустойчив.
 */
class RequestCURL
{
    # - ### ### ###

    public $CH = null;
    
    public $UA_Default = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36';

    public $OPT_ARR_NUMS = [ ];
    public $OPT_ARR_TEXT = [ ];

    public static $CURL_ERR_CODES = [
        1 => 'CURLE_UNSUPPORTED_PROTOCOL',
        2 => 'CURLE_FAILED_INIT',
        3 => 'CURLE_URL_MALFORMAT',
        4 => 'CURLE_URL_MALFORMAT_USER',
        5 => 'CURLE_COULDNT_RESOLVE_PROXY',
        6 => 'CURLE_COULDNT_RESOLVE_HOST',
        7 => 'CURLE_COULDNT_CONNECT',
        8 => 'CURLE_FTP_WEIRD_SERVER_REPLY',
        9 => 'CURLE_REMOTE_ACCESS_DENIED',
        11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
        13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
        14 => 'CURLE_FTP_WEIRD_227_FORMAT',
        15 => 'CURLE_FTP_CANT_GET_HOST',
        17 => 'CURLE_FTP_COULDNT_SET_TYPE',
        18 => 'CURLE_PARTIAL_FILE',
        19 => 'CURLE_FTP_COULDNT_RETR_FILE',
        21 => 'CURLE_QUOTE_ERROR',
        22 => 'CURLE_HTTP_RETURNED_ERROR',
        23 => 'CURLE_WRITE_ERROR',
        25 => 'CURLE_UPLOAD_FAILED',
        26 => 'CURLE_READ_ERROR',
        27 => 'CURLE_OUT_OF_MEMORY',
        28 => 'CURLE_OPERATION_TIMEDOUT',
        30 => 'CURLE_FTP_PORT_FAILED',
        31 => 'CURLE_FTP_COULDNT_USE_REST',
        33 => 'CURLE_RANGE_ERROR',
        34 => 'CURLE_HTTP_POST_ERROR',
        35 => 'CURLE_SSL_CONNECT_ERROR',
        36 => 'CURLE_BAD_DOWNLOAD_RESUME',
        37 => 'CURLE_FILE_COULDNT_READ_FILE',
        38 => 'CURLE_LDAP_CANNOT_BIND',
        39 => 'CURLE_LDAP_SEARCH_FAILED',
        41 => 'CURLE_FUNCTION_NOT_FOUND',
        42 => 'CURLE_ABORTED_BY_CALLBACK',
        43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
        45 => 'CURLE_INTERFACE_FAILED',
        47 => 'CURLE_TOO_MANY_REDIRECTS',
        48 => 'CURLE_UNKNOWN_TELNET_OPTION',
        49 => 'CURLE_TELNET_OPTION_SYNTAX',
        51 => 'CURLE_PEER_FAILED_VERIFICATION',
        52 => 'CURLE_GOT_NOTHING',
        53 => 'CURLE_SSL_ENGINE_NOTFOUND',
        54 => 'CURLE_SSL_ENGINE_SETFAILED',
        55 => 'CURLE_SEND_ERROR',
        56 => 'CURLE_RECV_ERROR',
        58 => 'CURLE_SSL_CERTPROBLEM',
        59 => 'CURLE_SSL_CIPHER',
        60 => 'CURLE_SSL_CACERT',
        61 => 'CURLE_BAD_CONTENT_ENCODING',
        62 => 'CURLE_LDAP_INVALID_URL',
        63 => 'CURLE_FILESIZE_EXCEEDED',
        64 => 'CURLE_USE_SSL_FAILED',
        65 => 'CURLE_SEND_FAIL_REWIND',
        66 => 'CURLE_SSL_ENGINE_INITFAILED',
        67 => 'CURLE_LOGIN_DENIED',
        68 => 'CURLE_TFTP_NOTFOUND',
        69 => 'CURLE_TFTP_PERM',
        70 => 'CURLE_REMOTE_DISK_FULL',
        71 => 'CURLE_TFTP_ILLEGAL',
        72 => 'CURLE_TFTP_UNKNOWNID',
        73 => 'CURLE_REMOTE_FILE_EXISTS',
        74 => 'CURLE_TFTP_NOSUCHUSER',
        75 => 'CURLE_CONV_FAILED',
        76 => 'CURLE_CONV_REQD',
        77 => 'CURLE_SSL_CACERT_BADFILE',
        78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
        79 => 'CURLE_SSH',
        80 => 'CURLE_SSL_SHUTDOWN_FAILED',
        81 => 'CURLE_AGAIN',
        82 => 'CURLE_SSL_CRL_BADFILE',
        83 => 'CURLE_SSL_ISSUER_ERROR',
        84 => 'CURLE_FTP_PRET_FAILED',
        84 => 'CURLE_FTP_PRET_FAILED',
        85 => 'CURLE_RTSP_CSEQ_ERROR',
        86 => 'CURLE_RTSP_SESSION_ERROR',
        87 => 'CURLE_FTP_BAD_FILE_LIST',
        88 => 'CURLE_CHUNK_FAILED',
    ];

    # - ### ### ###

    public function __construct()
    {
        # - ###
        if ( ! function_exists('curl_init') )
            dd('curl library not installed');
        $this->CH = curl_init();
        # - ###

        # NOTE: Только предельно общие и универсальные вещи. Которые 99% не придется менять никогда.

        $this->setOpt('CURLOPT_FOLLOWLOCATION',true); /*  Следовать редиректам  */
        $this->setOpt('CURLOPT_MAXREDIRS',5);

        $this->setOpt('CURLOPT_RETURNTRANSFER',true); # Вернуть результат в виде строки, а не выводить на страницу
        $this->setOpt('CURLOPT_HEADER',true); # Вставлять ли хедер в начало

        $this->setOpt('CURLOPT_AUTOREFERER',true);

        # - ###

        $this->setOpt_TimeoutExec(60);
        $this->setOpt_TimeoutConnect(10);

        $this->setOpt_SslVerif_Disable();


        # - ###
    }

    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Вместо конструкторов

    # FINAL
    public static function GET($URL, $dataQuery=[])
    {
        $CH = new self();
		
        $CH->setOpt_UserAgent($CH->UA_Default);
        
        $CH->setOpt_Main_GET($URL,$dataQuery);

        return $CH->action_ExecGetAnswer();
    }
    public static function POST($URL, $urlParamsArr=[], $postDataAny='')
    {
        $CH = new self();
	
	    $CH->setOpt_UserAgent($CH->UA_Default);
        
        $CH->setOpt_Main_POST($URL,$urlParamsArr,$postDataAny);

        return $CH->action_ExecGetAnswer();
    }
    public static function POST_Multi($URL, $urlParamsArr=[], $postDataAny='')
    {
        $CH = new self();
	
	    $CH->setOpt_UserAgent($CH->UA_Default);
        
        $CH->setOpt_Main_POST_MultiPart($URL,$urlParamsArr,$postDataAny);

        return $CH->action_ExecGetAnswer();
    }


    # - ### ### ###
    #   NOTE:

    public $ANS_RESP_FULL;
    public $ERROR_ANY;

    public function action_ExecGetAnswer()
    {
        # - ###

        curl_setopt_array($this->CH,$this->OPT_ARR_NUMS);

        $this->ANS_RESP_FULL = curl_exec($this->CH);
        $this->ERROR_ANY = ($this->ANS_RESP_FULL === false);

        # - ###

        $RES = $this->action_ParseAllInfo();

        curl_close($this->CH);

        return $RES;
        # - ###
    }

    public function action_ParseAllInfo()
    {
        $FIN = [
            'IS_ERROR'=>false,
            'INFO'=>[],
            'INFO_ERR'=>[],
            'INFO_REQ'=>[],
            'LAST_URL'=>'',
            'HTTP_CODE'=>'',
            'CONTENT_TYPE'=>'UNDEF',
            'ANSWER_FULL_RAW'=>[],
            'ANSWER_HEADER_FRAMES'=>[],
            'ANSWER_TEXT'=>[],
            'ANSWER_JSON'=>[],
        ];

        $answer_resp = $this->ANS_RESP_FULL;

        # - ###

        if( (curl_error($this->CH) !== '') || (curl_errno($this->CH) !== 0) )
        {
            $FIN['IS_ERROR']   = true;

            $FIN['INFO_ERR']['ERRNO']        = curl_errno($this->CH); # 0
            $FIN['INFO_ERR']['ERRNO_PARSED'] = curl_strerror(curl_errno($this->CH) ); # "No error"
            $FIN['INFO_ERR']['ERROR']        = curl_error($this->CH); # ""

            $FIN['INFO_ERR']['ERROR_CODE_MY'] = (isset(self::$CURL_ERR_CODES[ $FIN['INFO_ERR']['ERRNO'] ]))? self::$CURL_ERR_CODES[ $FIN['INFO_ERR']['ERRNO'] ] : 'UNDEF' ; # Может быть неверным
        }

        # - ###

        $FIN['HTTP_CODE'] = (string) curl_getinfo($this->CH,CURLINFO_HTTP_CODE);
        $FIN['LAST_URL'] = curl_getinfo($this->CH,CURLINFO_EFFECTIVE_URL);

        $FIN['INFO_REQ'] = $this->OPT_ARR_TEXT;


        $contType = curl_getinfo($this->CH)['content_type'];
        #$FIN['CONTENT_TYPE_RAW'] = $contType;
        if( str_contains($contType, 'text/html') ) $FIN['CONTENT_TYPE'] = 'HTML';
        if( str_contains($contType, 'text/plain') ) $FIN['CONTENT_TYPE'] = 'PLAIN';
        if( str_contains($contType, 'application/json') ) $FIN['CONTENT_TYPE'] = 'JSON';
        if( $contType === 'UNDEF' ) $FIN['CONTENT_TYPE'] = 'Дописать тип => '.$contType;
        #if( str_contains($contType, '') ) $FIN['CONTENT_TYPE'] = '';

        # - ###
        #$FIN['CONTENT']['HEADER'] = [substr($answer_resp,0,curl_getinfo($this->CH,CURLINFO_HEADER_SIZE))];
        #$FIN['CONTENT']['HEADER_ARR'] = explode(PHP_EOL,$FIN['CONTENT']['HEADER'][0]);

        $FIN['ANSWER_FULL_RAW'] = [$this->ANS_RESP_FULL];

        $FIN['ANSWER_HEADER_FRAMES'] = explode(PHP_EOL.PHP_EOL,substr($answer_resp,0,curl_getinfo($this->CH,CURLINFO_HEADER_SIZE)));
        foreach( $FIN['ANSWER_HEADER_FRAMES'] as $key => $frame )
            if( $FIN['ANSWER_HEADER_FRAMES'][$key] === '' ) unset( $FIN['ANSWER_HEADER_FRAMES'][$key] );

        #$FIN['CONTENT']['BODY'] = [substr($answer_resp,curl_getinfo($this->CH,CURLINFO_HEADER_SIZE))];
        #$FIN['CONTENT']['BODY_AS_JSON_DEC'] = json_decode($FIN['ANSWER_TEXT'][0],1);

        $FIN['ANSWER_TEXT'] = [substr($answer_resp,curl_getinfo($this->CH,CURLINFO_HEADER_SIZE))];
        $FIN['ANSWER_JSON'] = json_decode($FIN['ANSWER_TEXT'][0],1);

        /*foreach( $FIN['CONTENT']['HEADER_ARR'] as $i => $str )
        {
            if( $i === 0 ) continue;  # Там код ответа хттп
            $FIN['CONTENT']['HEADER_ARR_2'] []= explode(': ',$str,2);
        } */

        # - ###

        # - ###

        # Вся CURL инфа о запросе.  Есть все тайминги. Вызывать dd только после закрытия
        $FIN['INFO']['CURL'] = curl_getinfo($this->CH); # Вызывать только ДО закрытия.
        $FIN['INFO']['CLASS'] = $this->CH; #

        # - ###

        $FIN['INFO']['IP']['TARGET_IP']     = $FIN['INFO']['CURL']['primary_ip'];
        $FIN['INFO']['IP']['TARGET_PORT']   = $FIN['INFO']['CURL']['primary_port'];
        $FIN['INFO']['IP']['MY_IP']         = $FIN['INFO']['CURL']['local_ip'];
        $FIN['INFO']['IP']['MY_PORT']       = $FIN['INFO']['CURL']['local_port'];
        $FIN['INFO']['IP']['SOCKET_MY']     = $FIN['INFO']['IP']['MY_IP']    .':'.$FIN['INFO']['IP']['MY_PORT'];
        $FIN['INFO']['IP']['SOCKET_TARGET'] = $FIN['INFO']['IP']['TARGET_IP'].':'.$FIN['INFO']['IP']['TARGET_PORT'];

        # - ###

        $FIN['INFO']['LEN']['REQ_SIZE'] = curl_getinfo($this->CH,CURLINFO_REQUEST_SIZE);
        $FIN['INFO']['LEN']['HEADER'] = curl_getinfo($this->CH,CURLINFO_HEADER_SIZE);
        $FIN['INFO']['LEN']['BODY'] = strlen($FIN['ANSWER_TEXT'][0]);
        $FIN['INFO']['LEN']['FULL'] = strlen($FIN['ANSWER_FULL_RAW'][0]);

        # - ###

        $FIN['INFO']['TIME']['total_time']      = (int)($FIN['INFO']['CURL']['total_time_us']/1000);
        $FIN['INFO']['TIME']['1_DNS']           = (int)($FIN['INFO']['CURL']['namelookup_time_us']/1000);
        $FIN['INFO']['TIME']['2_connect']       = (int)($FIN['INFO']['CURL']['connect_time']/1000);
        $FIN['INFO']['TIME']['3_SSL/SSH']       = (int)($FIN['INFO']['CURL']['appconnect_time_us']/1000);
        $FIN['INFO']['TIME']['4_PreTransfer']   = (int)($FIN['INFO']['CURL']['pretransfer_time_us']/1000);
        $FIN['INFO']['TIME']['5_StartTransfer'] = (int)($FIN['INFO']['CURL']['starttransfer_time_us']/1000);
        $FIN['INFO']['TIME']['6_Download']      = (int)($FIN['INFO']['TIME']['total_time'] - $FIN['INFO']['TIME']['5_StartTransfer']);

        # - ###

        /*
        $FIN['COOKIE_OLD'] = $this->COOKIE_OLD;

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $FIN['CONTENT']['HEADER'][0], $matches);
        $cookies = array();
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        $FIN['COOKIE_NEW'] = $cookies;

        #array_merge($arrays)
        $FIN['COOKIE_FIN'] = array_replace($FIN['COOKIE_OLD'],$FIN['COOKIE_NEW']);
        foreach($FIN['COOKIE_FIN'] as $key=>$val)
            if( $val === 'delete' ) unset($FIN['COOKIE_FIN'][$key]);

        $FIN['COOKIE_FIN_JSON'] = json_encode($FIN['COOKIE_FIN']);
        $FIN['COOKIE_FIN_JSON_PR'] = json_encode($FIN['COOKIE_FIN'],JSON_PRETTY_PRINT);
        # */

        # - ###

        if( $FIN['IS_ERROR'] ) # Затираю бесполезные ключи, чтоб не мешались
        {
            unset( $FIN['HTTP_CODE'] , $FIN['LAST_URL'] , $FIN['INFO']['IP'] , $FIN['INFO']['LEN'] );
        }

        # - ###

        return $FIN;

    }



    # - ### ### ###
    #   NOTE:

    # FINAL IMPORTANT
    public function setOpt_Main_GET($urlBase, $urlParamsArr=[])
    {
        $this->setOpt('CURLOPT_HTTPGET',true); # Он и так по дефолту

        if( empty( $urlParamsArr ) )
            $this->setOpt('CURLOPT_URL',$urlBase);
        else
            $this->setOpt('CURLOPT_URL',$urlBase.'?'.http_build_query($urlParamsArr));
    }
    public function setOpt_Main_POST($urlBase, $urlParamsArr, $postDataAny)
    {
        $this->setOpt('CURLOPT_POST',true);
        $this->setOpt('CURLOPT_POSTFIELDS',$postDataAny);# Закодированная строка

        if( empty( $urlParamsArr ) )
            $this->setOpt('CURLOPT_URL',$urlBase);
        else
            $this->setOpt('CURLOPT_URL',$urlBase.'?'.http_build_query($urlParamsArr));
    }
    public function setOpt_Main_POST_MultiPart($urlBase, $urlParamsArr, $postDataAny)
    {
        $this->setOpt('CURLOPT_POST',true);
        $this->setOpt('CURLOPT_POSTFIELDS',$postDataAny);# Закодированная строка

        $this->setOpt('CURLOPT_HTTPHEADER',[ "Content-Type:multipart/form-data" ]);

        if( empty( $urlParamsArr ) )
            $this->setOpt('CURLOPT_URL',$urlBase);
        else
            $this->setOpt('CURLOPT_URL',$urlBase.'?'.http_build_query($urlParamsArr));
    }


    # - ### ### ###
    #   NOTE: Опции - Штучные
    #    https://www.php.net/manual/en/function.curl-setopt.php

    public function setOpt( string $curlNoStr , $value )
    {
        $this->OPT_ARR_NUMS[constant($curlNoStr)] = $value;
        $this->OPT_ARR_TEXT[$curlNoStr] = $value;
    }

    public function setOpt_UserAgent($ua)
    {
        $this->setOpt('CURLOPT_USERAGENT',$ua);
    }
    public function setOpt_Referer($str)
    {
        $this->setOpt('CURLOPT_REFERER',$str);
    }
    public function setOpt_Headers($headerArr)
    {
        $this->setOpt('CURLOPT_HTTPHEADER',$headerArr);
    }
    public function setOpt_TimeoutExec($sec = 10)
    {
        $this->setOpt('CURLOPT_TIMEOUT',$sec); /*  The maximum number of seconds to allow cURL functions to execute.  */
    }
    public function setOpt_TimeoutConnect($sec = 10)
    {
        $this->setOpt('CURLOPT_CONNECTTIMEOUT',$sec); /*  The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.  */
    }


    # - ### ### ###
    #   NOTE: Опции - Особые

    # WORK
    public function setOpt_Proxy($type, $ipOrSocket, $logPass)
    {
        #$proxy_ip = 'your_proxy_ip:proxy_port'; //IP адрес сервера прокси и порт
        #$loginpassw = 'login:password';  //логин и пароль для прокси

        //Указываем к какому прокси подключаемся и передаем логин-пароль
        $this->setOpt('CURLOPT_PROXY',$ipOrSocket);
        #curl_setopt($ch, CURLOPT_PROXYPORT, '8080');

        switch( $type )
        {
            case 'HTTP'  : $this->setOpt('CURLOPT_PROXYTYPE',CURLPROXY_HTTP); break;
            case 'SOCKS4': $this->setOpt('CURLOPT_PROXYTYPE',CURLPROXY_SOCKS4); break;
            case 'SOCKS5': $this->setOpt('CURLOPT_PROXYTYPE',CURLPROXY_SOCKS5); break;
            default: dd('Неверный тип',$type);
        }

        /*    */
    }

    public function setOpt_SslVerif_Disable()
    {
        $this->setOpt('CURLOPT_SSL_VERIFYPEER',false);
        $this->setOpt('CURLOPT_SSL_VERIFYSTATUS',false);
        $this->setOpt('CURLOPT_SSL_VERIFYHOST',false); /* Вообще там 0-1-2 а не бул   */
    }
    public function setOpt_SslVerif_Enable()
    {
        $this->setOpt('CURLOPT_SSL_VERIFYPEER',true);
        $this->setOpt('CURLOPT_SSL_VERIFYSTATUS',true);
        $this->setOpt('CURLOPT_SSL_VERIFYHOST',2); /* Вообще там 0-1-2 а не бул   */
    }


    #public $COOKIE_OLD = []; # ключ-знач
    public function setOpt_CookieSessionFlush()
    {
        /*
         * true to mark this as a new cookie "session". It will force libcurl to ignore all cookies it is about to load that are "session cookies" from the previous session. By default, libcurl always stores and loads all cookies, independent if they are session cookies or not. Session cookies are cookies without expiry date and they are meant to be alive and existing for this "session" only.
         * */
        $this->setOpt('CURLOPT_COOKIESESSION',true);
    }
    public function setOpt_CookieFromJson($jsonNameVal)
    {
        $arr = json_decode($jsonNameVal,true);

        if( ! is_array($arr) ) return;
        if( ! count($arr) ) return;

        $this->COOKIE_OLD = $arr;

        $stringsArr = [];
        foreach($arr as $key=>$val)
            $stringsArr []= "$key=$val";

        #dd($jsonNameVal,$arr,$stringsArr,implode(';',$stringsArr));

        /*  "login=some;password=123456")  */
        $this->setOpt('CURLOPT_COOKIE',implode('; ',$stringsArr));
    }
    public function setOpt_CookieFILE($path)
    {
        # файл, откуда читаются куки
        $this->setOpt('CURLOPT_COOKIEFILE',$path);

        # файл, куда пишутся куки после закрытия коннекта, например после curl_close()
        $this->setOpt('CURLOPT_COOKIEJAR',$path);
    }

    # - ### ### ###
    #   NOTE:





    # - ### ### ###
    #   NOTE:

    /*
        // this will produce a curl log
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        curl_setopt($curl, CURLOPT_STDERR, fopen('/your/writable/app/logdir/curl.log', 'a+')); // a+ to append...
    */

    # - ### ### ###
    #   NOTE:


    # NOTE: Весь старый код из тестов кук и сайтов, МНОГО
    public function old_code()
    {

        # 1 = Введен тлф
        # $URL = 'https://ogon.ru/v1/users/auth';  # POST   после ввода тлф   жсоны и туда и обратно
        # жсон запрос  { "phone_number": "+7 (903) 537-46-38",  "group": "USER_GROUP_CUSTOMER",   "referrer_id": null   }
        # Жсон ответ  {"id":1800771,"user":{"id":5090612,"phone":""},"type":"USER_CONFIRMATION_CREATE_SESSION","value":"+79035374638","description":"+79035374638","access_lock":{"id":2759691,"owner_type":"ACCESS_LOCK_OWNER_TYPE_SYSTEM","owner_id":0,"source_type":"ACCESS_LOCK_SOURCE_TYPE_CONFIRMATION","source_id":1800771,"user_id":5090612,"type":"ACCESS_LOCK_OPERATION_TYPE_TEMPORARY","details":{"error_message":"api.confirmation_locked_error","duration":60,"retry_count":3,"attempts_amount":0,"number_of_sms_sent":1},"status":"ACCESS_LOCK_STATUS_ACTIVE","duration":60,"start_at":"2023-04-17T23:07:49.497937940Z","expiration_at":"2023-04-17T23:08:49.497938010Z","created_at":"2023-04-17T23:07:49.476326Z","current_time":"2023-04-17T23:07:49.710490624Z","lock_reason":null,"unlock_reason":null},"details":{"personal_data_agreement":false,"adv_agreement":false,"device_id":"","device_public_key":"","call2pass_provider":"NOTIFY_PROVIDER_TYPE_UNSPECIFIED"},"expiration_at":"2023-04-18T00:07:49.491492761Z","current_time":"2023-04-17T23:07:49.710489640Z","event_type":"EVENT_TYPE_UNSPECIFIED","status":"USER_CONFIRMATION_STATUS_ACTIVE"}

        # 2 = отправил 4 цифры     неверные=код 403
        # Куда:  https://ogon.ru/v1/users/confirm/action  POST    403
        # Что:  {"code":"1234","confirmation_id":1800771,"params":{"adv_agreement":true,"personal_data_agreement":true}}
        # Ответ: {"code":7,"message":"api.wrong_confirmation_code_error","correlation_id":"262df34e-46cf-4d1f-9577-d889bc6f6c60","details":{}}
        # Заметки: confirmation_id  есть в 1 запросе

        # 3 = Посторный запрос
        # Куда: https://ogon.ru/v1/users/confirmations/1800771  POST    200
        # Что: портянка     {"id":1800771,"user":{"id":5090612,"phone":""},"type":"USER_CONFIRMATION_CREATE_SESSION","value":"","description":"","access_lock":{"id":2759691,"owner_type":"ACCESS_LOCK_OWNER_TYPE_SYSTEM","owner_id":0,"source_type":"ACCESS_LOCK_SOURCE_TYPE_CONFIRMATION","source_id":1800771,"user_id":5090612,"type":"ACCESS_LOCK_OPERATION_TYPE_TEMPORARY","details":{"error_message":"api.confirmation_locked_error","duration":60,"retry_count":3,"attempts_amount":0,"number_of_sms_sent":2},"status":"ACCESS_LOCK_STATUS_ACTIVE","duration":60,"start_at":"2023-04-17T23:16:09.379139043Z","expiration_at":"2023-04-17T23:17:09.379139712Z","created_at":"0001-01-01T00:00:00Z","current_time":"2023-04-17T23:16:09.587328340Z","lock_reason":null,"unlock_reason":null},"details":{"personal_data_agreement":false,"adv_agreement":false,"device_id":"","device_public_key":"","call2pass_provider":"NOTIFY_PROVIDER_TYPE_UNSPECIFIED"},"expiration_at":"2023-04-18T00:16:09.374281201Z","current_time":"2023-04-17T23:16:09.587327826Z","event_type":"EVENT_TYPE_UNSPECIFIED","status":"USER_CONFIRMATION_STATUS_ACTIVE"}
        # Ответ:  {"id":1800771,"user":{"id":5090612,"phone":""},"type":"USER_CONFIRMATION_CREATE_SESSION","value":"","description":"","access_lock":{"id":2759691,"owner_type":"ACCESS_LOCK_OWNER_TYPE_SYSTEM","owner_id":0,"source_type":"ACCESS_LOCK_SOURCE_TYPE_CONFIRMATION","source_id":1800771,"user_id":5090612,"type":"ACCESS_LOCK_OPERATION_TYPE_TEMPORARY","details":{"error_message":"api.confirmation_locked_error","duration":60,"retry_count":3,"attempts_amount":0,"number_of_sms_sent":2},"status":"ACCESS_LOCK_STATUS_ACTIVE","duration":60,"start_at":"2023-04-17T23:16:09.379139043Z","expiration_at":"2023-04-17T23:17:09.379139712Z","created_at":"0001-01-01T00:00:00Z","current_time":"2023-04-17T23:16:09.587328340Z","lock_reason":null,"unlock_reason":null},"details":{"personal_data_agreement":false,"adv_agreement":false,"device_id":"","device_public_key":"","call2pass_provider":"NOTIFY_PROVIDER_TYPE_UNSPECIFIED"},"expiration_at":"2023-04-18T00:16:09.374281201Z","current_time":"2023-04-17T23:16:09.587327826Z","event_type":"EVENT_TYPE_UNSPECIFIED","status":"USER_CONFIRMATION_STATUS_ACTIVE"}
        # Заметки:

        # 3 =
        # Куда:
        # Что:
        # Ответ:
        # Заметки:

        #dd(123);

        # - ####

        $URL = 'https://www.ogon.ru/';


        # - ####

        #/* # WORK
        $CH = new RequestCURL();

        $CH->setOpt_Proxy('HTTP','84.23.53.55:15830','123123123123123');

        $CH->setOpt_Referer('AUTO');
        #$CH->setOpt_Referer('yandex.ru'); # Работает    прокатил для газа.   Скорее всего палится ip



        #$CH->setOpt_UserAgent('RANDOM'); # BUG
        $CH->setOpt_UserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36');


        #$CH->setOpt_Main('GET',$URL);

        # fingerprint=&=&=


        #/*
        #  заголовок = x-fingerprint: 3f4c0e06b6d313c561361c1e80b353df
        #  заголовок = x-uuid: 38dc6fdf-f028-466f-b844-476d7408a7bf
        #  разный  x-correlation-id: a0c94559-24f8-432c-813b-b31cd77fca34

        # Походу у них все общение на закрыхых сокетах с ключами.

        # Предположительно регистрация в системе.     В заголовках пока нет иксов
        # # wss://ogon.ru:8443/ws

        $CH->setOpt_Headers([
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: ru,en;q=0.9',
            'Cache-Control: no-cache',
            'Connection: Upgrade',
            'Host: ogon.ru:8443',
            'Origin: https://ogon.ru',
            'Pragma: no-cache',
            'Sec-WebSocket-Extensions: permessage-deflate; client_max_window_bits',
            'Sec-WebSocket-Key: ElIMvbPHB6w6QI0pTR9e3A==',
            'Sec-WebSocket-Version: 13',
            'Upgrade: websocket',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36',
        ]);

        $CH->setOpt_Main_GET('https://ogon.ru:8443/ws',[
            'app_name' => 'Site',
            'fingerprint' => '3f4c0e06b6d313c561361c1e80b353d0',
            'session_id' => '78dd3eea-96d3-463b-93b5-9d72f0f04880'
        ]);








        /*
        $CH->setOpt_Main('GET',' wss://ogon.ru:8443/ws',[
            'fingerprint' => '3f4c0e06b6d313c561361c1e80b35300',  # У них свой
            #'phone_number' => '+7 (903) 537-46-38',
            'app_name' => 'Site',
            'session_id' => '38dc6fdf-f028-466f-b844-476d7408a700',  # Тоже свой
        ] ); # */
        # В норме - 101








        /*
        $CH->setOpt_Headers(['Content-Type: application/json',
        'x-app-name: Site', 'x-app-version: 1.47.243','x-support-sdk: false','x-domain: https://ogon.ru']);
        $CH->setOpt_Main('POST','https://ogon.ru/v1/users/auth',json_encode([
            'phone_number' => '+7 (968) 044-86-57',
            #'phone_number' => '+7 (903) 537-46-38',
            'group' => 'USER_GROUP_CUSTOMER',
            'referrer_id' => null,
        ]) ); # */
        # невалидный номер => {"code":7,"message":"api.no_write_permission_error","correlation_id":"48f178d2-f3a3-4821-94a8-fa9689a9e342","details":{}}

        # Выдает и так   и просто во вкладке
        #$CH->setOpt_Main('GET','https://ogon.ru/v1/users/feature-flags','');
        # https://ogon.ru/v1/users/location


        #$CH->setOpt_Main('GET',$URL,'');



        #$CH->setOpt_DisableSslVerif();

        #123  {"spid":"1681771991034_8fc4c23044fad7335ac9814529cfdf24_9uqfv8r58odr6rvj","PHPSESSID":"80ad75478dfcd0f3247398fc95bc59d0","sluid":"91aed62f866499096dfd5fd64b6e9c3d3c5c169a4a970462b621009e470d1052","new_menu":"1","scity":"18413","new_order_2":"1","new_product":"1","new_catalog":"1","adcampaign":"0","top_informer":"a:1:{s:0:\"\";i:1;}"}
        #гугл {"AEC":"AUEFqZdTE2_jDcG3LV6g1stBP0w5zaOjrGm7KUyvFr085HoGs3kX6qPQ5a0","NID":"511=t4o9tUqdERcSVD1EDyrXW0hsQtgMfxoxiJu6dBXFmgj7IVlssMbZoSjnjQC9LHp3b0OSLrAjOuwCRe_viRH2AYH7dV5YiuSfBNqerx4yQTZCy_j9Pkdu6RbJEVIta-7uH7PbFOPzAUvsz3Vnh1AyZ4aeAKqxcGdEexh5LfQdeAA"}
        #янд с id   {"is_gdpr":"0","is_gdpr_b":"CIHuMRCpsgEoAg==","_yasc":"8dzcm5QPXOXvlqzG1HIybptt49U3HP2RL8Fl1neMY72Hc5PwvUb4dtJkUIk=","i":"TIfYzLBoAkF0myT1ySU13PoY3yxfNfeL66ih16vXNAk\/qNBiwgU6vhkD1V\/M4Z2Cc3RjdJuR4SFH7T3Y7wMtIqhgSXQ=","yandexuid":"2739165891681772304","zen_sso_checked":"","mda2_beacon":"1681772225288","ys":"c_chck.2110447766","mda2_domains":""}
        /*
        */
        $cookieJson = '';
        #$CH->setOpt_CookieFromJson($cookieJson);

        #$CH->setOpt_CookieFILE(public_path('COOKIES.txt'));

        $RES = $CH->action_ExecGetAnswer();

        if( $RES['IS_ERROR'] )
        {   # Ошибка
            dd($RES);
        }

        return $RES;


        $html = $RES['ANSWER_TEXT'][0];


        EasyFront::echoTag_Iframe_HTML( $html);
        EasyFront::echoTag_TextArea($RES['COOKIE_FIN_JSON']);

        dump($RES);

        Ancii::anyTextDump($RES['HTTP_CODE']);
        dump($RES['ANSWER_HEADER_FRAMES']);
        # */

        # - ####


    }



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
