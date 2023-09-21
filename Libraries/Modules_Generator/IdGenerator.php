<?php

namespace LibMy;


use Illuminate\Support\Facades\Request;

/**
 * Важный класс для генерации уникальных идентификаторов для юзеров и запросов.
 * Используется постоянно.
 * Все ID генерятся тут.
 */
class IdGenerator
{
    # - ### ### ###
    #   NOTE:

    # public static function getGeneratedUid()
    # { return Stringer::sliceTo(Session::getId(),8,true); }


    public static function getRaw_MD5_IP_UA()
    {
        # NOTE: Пишу через сервер тк надежнее.
        #  - Отлов отсутствия работает.

        $IP = $_SERVER['REMOTE_ADDR']     ?? 'EMPTY_IP';
        $UA = $_SERVER['HTTP_USER_AGENT'] ?? 'EMPTY_UA';

        $STR_RAW = "{$IP} <===> {$UA}";

        $STR_MD5 = md5($STR_RAW);  # 32симв =  0d189ea80ddc3f55101a820dc4356d43

        $STR_MD5 = strtoupper($STR_MD5); # Для читаемости

        return $STR_MD5;
    }
	
	public static function getRaw_MD5_IP_UA_ReqTime()
	{
		# NOTE: Пишу через сервер тк надежнее.
		#  - Отлов отсутствия работает.
		
		$IP = $_SERVER['REMOTE_ADDR']     ?? 'EMPTY_IP';
		$UA = $_SERVER['HTTP_USER_AGENT'] ?? 'EMPTY_UA';
		$T  = (string)$_SERVER['REQUEST_TIME_FLOAT'] ?? 'EMPTY_TIME';
		
		$STR_RAW = "{$IP} <===> {$UA} <===> {$T}";
		
		$STR_MD5 = md5($STR_RAW);  # 32симв =  0d189ea80ddc3f55101a820dc4356d43
		
		$STR_MD5 = strtoupper($STR_MD5); # Для читаемости
		
		return $STR_MD5;
	}
	
	
	
	# - ###

    # FINAL
    # Дробит любую строку на блоки
    public static function prepareText_UNIV($text,$blocks,$chars,$delimiter='-')
    {
        $blocksArr = str_split($text,$chars);

        $blocksArrFin = [];
        foreach( range(0,$blocks-1 ) as $i )
            $blocksArrFin []= $blocksArr[$i];

        return implode($delimiter,$blocksArrFin);
    }

    # ВРЕМЯ РАБОТЫ: Около ~0.20мс
    # Для отслеживания посещений юзера
    public static function methodMD5_FromIpUa_Parts3x3()
    {
        return self::prepareText_UNIV(self::getRaw_MD5_IP_UA() ,3,3,'-');
    }
    public static function methodMD5_FromIpUa_Parts3x4()
    {
        return self::prepareText_UNIV(self::getRaw_MD5_IP_UA() ,3,4,'-');
    }
    public static function methodMD5_FromIpUa_Parts4x3()
    {
        return self::prepareText_UNIV(self::getRaw_MD5_IP_UA() ,4,3,'-');
    } # NOTE: Основной
    public static function methodMD5_FromIpUa_Parts4x4()
    {
        return self::prepareText_UNIV(self::getRaw_MD5_IP_UA() ,4,4,'-');
    }
    public static function methodMD5_FromIpUa_Parts5x4()
    {
        return self::prepareText_UNIV(self::getRaw_MD5_IP_UA() ,5,4,'-');
    }
	
    # Уникальный ID запроса к серверу = UA+IP+SrvTime
	public static function methodMD5_FromIpUaTime_Parts4x3()
	{
		return self::prepareText_UNIV(self::getRaw_MD5_IP_UA_ReqTime() ,4,3,'-');
	} # NOTE: Основной
    
    # ВРЕМЯ РАБОТЫ: Около ~0.55мс
    # Для UID при регистраци юзера
    public static function methodRandom_Parts4x4()
    {
        $text = Stringer::generateRandom(20,Stringer::$alphabet_eng_big_nums);
        return self::prepareText_UNIV($text ,4,4,'-');
    }


    # - ### ### ###
    #   NOTE:

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:





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
