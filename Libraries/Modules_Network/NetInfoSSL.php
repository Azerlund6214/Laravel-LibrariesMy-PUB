<?php

namespace LibMy;



/**
 * Класс для любой работы с SSL.
 */
class NetInfoSSL
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###



    # - ### ### ###
    #   NOTE:

    #public static function getSslInfo_Current()
    #{ return self::getSslInfo_AnySite('https://laravel-main-proj-template/'); }

	# ПереПроверять = если стоит редирект, то чекает конечный сайт
	
    # WORK    БЕЗ отлова ошибок
    public static function getSslInfo_AnySite($URL)
    {
        $original_parse = parse_url($URL, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        
        $read = stream_socket_client("ssl://".$original_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        #$read = fopen($original_parse, "rb", false, $get); # Полный аналог, должен работать.
	    
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        return $certinfo;
    }


    # https://dnschecker.org/ssl-certificate-examination.php


    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
