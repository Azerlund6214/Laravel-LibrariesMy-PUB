<?php

namespace LibMy;



# CONCEPT: Надо бы переписать на объектный класс
/**
 *
 *
 */
class UserAgenter
{
    # - ### ### ###
    #   NOTE:

    # ТОННЫ примеров https://developers.whatismybrowser.com/useragents/explore/operating_system_name/

    /*public static function getUa_Request()
    {
            $currentRequest = self::getObjectRequest();

            # Если нет объекта реквеста
            if( $currentRequest === false )
                return $INFO;

            # Реквест есть, получаю UA
            $ua = $currentRequest->userAgent();
    }*/


    /**
     * Получить UA из переменной сессии
     * @return bool|mixed Либо FALSE Либо строка
     */
    public static function getUa_Server()
    {
        if( ! isset($_SERVER['HTTP_USER_AGENT']) )
            return false;

        return $_SERVER['HTTP_USER_AGENT'];
    }

    # - ### ### ###
    #   NOTE:

    /**
     * Получить базовую информацию о UserAgent
     * @param string $ua 'DEF' либо свой
     * @return array Предопределенный асоц массив
     */
    public static function getBasicInfo( $ua = 'DEF' )
    {
        $INFO = [
            'EXIST'   => false,
            'RAW'     => 'EMPTY',
            'RAW_LEN' => 0,
            'SUB_64'  => 'EMPTY',
            'SUB_128' => 'EMPTY',
            'SUB_256' => 'EMPTY',
        ];

        # - ###

        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        if( ! $ua  ||  ! is_string($ua) )
            return $INFO;

        # - ###

        $INFO['EXIST'] = true;

        $INFO['RAW']     = $ua;
        $INFO['RAW_LEN'] = strlen($ua);

        $INFO['SUB_64']  = substr($ua, 0,63);
        $INFO['SUB_128'] = substr($ua, 0,127);
        $INFO['SUB_256'] = substr($ua, 0,255);

        return $INFO;
    }


    /**
     * Получить полную расшифровку UserAgent. Используется черная магия.
     * @param string $ua Исходный агент
     * @return array Всегда массив.
     */
    public static function getFullParsedInfo( $ua = 'DEF' ):array
    {

        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        # - ###
        # Система

        $platform = self::getPlatformOS($ua);

        # - ###
        # Тип системы

        $isMob = self::checkIsMobile($ua,$platform);
        $isPC  = self::checkIsPC($ua,$platform);

        # Моб версии в приоритете, их проще проверить.
        if( $isMob && $isPC )
            $isPC = false;

        # - ###
        # Браузер

        $versArr = self::getBrowserAndVersion($ua);

        $browserName = $versArr[0];
        $browserVers = $versArr[1];
	    $browserFull = $browserName.' '.$browserVers;
        
        # - ###

        $platfType = '';

        if($isMob) $platfType = 'MOB';
        if($isPC)  $platfType = 'PC ';
        if( ! $isMob && ! $isPC)  $platfType = '???';

        
        $text = $platfType.' / '.$platform.' / '.$browserFull;
	
	    # Эталон самый длинный = "PC  / Windows 10 x64 / Firefox 108"
	    $textPad = implode(' / ',[
		    $platfType,
		    str_pad($platform,14,' '),
		    str_pad($browserFull,11,' '),
	    ]);
        
        # - ###

        return [
            'TEXT' => $text,
            'TEXT_PAD' => $textPad,
            'OS' => $platform,
            'IS_MOB' => $isMob,
            'IS_PC' => $isPC,
            'B_NAME' => $browserName,
            'B_VERS' => $browserVers,
            'B_FULL' => $browserFull,
        ];
    }

    # Прокладка
    public static function getFullParsedInfoOnlyText( $ua = 'DEF' ):string
    {
        return self::getFullParsedInfo($ua)['TEXT'];
    }

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE: Модульные методы (по кусочкам)


    # TODO: Версии айфонов и мак
    public static function getPlatformOS( $ua = 'DEF' ):string
    {
        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        # IMPORTANT: Следить за порядком.
        $arrPlatforms = [
            'iPhone' => '/iphone|ipad/i', # Вписал iPad сюда
            'Android' => '/android/i',
            'Windows' => '/windows|win32/i',
            'Mac' => '/macintosh|mac os x/i',
            #'WindowsPhone' => '/windows phone/i', Должно стоять перед виндой, иначе юзлесс
            'Xbox' => '/xbox/i',
            'PlayStation' => '/playstation/i',
            #'Ubuntu' => '/ubuntu/i',
            'Linux' => '/linux/i', # NOTE: !!! У андроидов это слово будет в UA !!!
        ];

        // Определяем платформу
        $platform = 'UnknownOS';
        foreach( $arrPlatforms as $p=>$regExp )
        {
            if( preg_match($regExp, $ua) )
            {
                $platform = $p;
                break;
            }
        }

        # - ###
        # Доп определение

        # Дополнительно определяю версию винды. Не проверяю серверные сборки.
        if( $platform === 'Windows' )
        {
            $arrWinNT = [
                'Windows 10'    => 'Windows NT 10.0', # 2015
                'Windows NT 9.0'   => 'Windows NT 9.0', # крайне редкий Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US) AppEngine-Google; (+http://code.google.com/appengine; appid: s~virustotalcloud)
                'Windows 8.1'   => 'Windows NT 6.3', # 2013
                'Windows 8'     => 'Windows NT 6.2', # 2013
                'Windows 7'     => 'Windows NT 6.1', # 2009
                'Windows Vista' => 'Windows NT 6.0', # 2006
                'Windows XP'    => 'Windows NT 5.1', # 2001
                'Windows 2000'  => 'Windows NT 5.0', # 2000
            ];

            foreach( $arrWinNT as $nameWin=>$nt )
            {
                if( str_contains($ua,$nt ) )
                {
                    $platform = $nameWin;
                    break;
                }
            }

            if( str_contains($ua,'Win64; x64' ) || str_contains($ua,'WOW64') )
                $platform .= ' x64';

            if( str_contains($ua,'Win32; x32' ) )
                $platform .= ' x32';
        }

        # Дополнительно пишу версию андроида
        if( $platform === 'Android' )
        {
            # Mozilla/5.0 (Linux; Android 10; SM-A920F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.74 Mobile Safari/537.36
            $bufUa = $ua;

            $res = explode('; ', $ua);

            # Ищу часть ..."Android ???"...
            $partAndroid = '';
            foreach( $res as $one )
            {
                if(str_contains($one, 'Android') )
                {
                    $partAndroid = $one;
                    break;
                }
            }

            if( ! empty($partAndroid) )
                $platform .= ' '.explode(' ',$partAndroid)[1];

            $platform = str_replace(')','', $platform); # Было 1 раз =>  MOB / Android 11) / Chrome 88

        }


        # TODO: Тут айфоны

        # - ###

        return $platform;
    }

    public static function checkIsMobile( $ua = 'DEF', $os='DEF' ):bool
    {
        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        if( $os === 'DEF' )
            $os = self::getPlatformOS($ua);

        # - ###

        if( in_array($os, ['iPhone','WindowsPhone']) )
            return true;

        if( str_contains($os,'Android') ) # Тк есть приписки
            return true;

        # Во всех мобильных ближе к концу есть отдельный блок со словом Mobile.   Крайне редко без них.(около 1%)
        if( str_contains($ua,' Mobile ') )  #|| str_contains($ua,'Mobile') )
            return true;

        # - ###

        return false;
    }
    public static function checkIsPC    ( $ua = 'DEF', $os='DEF' ):bool
    {
        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        if( $os === 'DEF' )
            $os = self::getPlatformOS($ua);

        # - ###

        if( in_array($os, ['Android','iPhone','WindowsPhone']) )
            return false;

        # Во всех мобильных ближе к концу есть отдельный блок со словом Mobile
        if( str_contains($ua,' Mobile ') ) # || str_contains($ua,'Mobile') )
            return false;


        if( in_array($os, ['Linux','Mac']) )
            return true;

        if( str_contains($os,'Windows') ) # Тк есть приписки
            return true;

        # - ###

        return false;
    }

    # Рефакторить определение версии
    public static function getBrowserAndVersion($ua = 'DEF' ):array
    {
        if( $ua === 'DEF' )
            $ua = self::getUa_Server();

        # - ###

        $ub = "UnknownBr";
        $version = "?";

        # Порядок важен. Кроме IE.
        $arrBrowsers = [
            'Firefox'  => '/Firefox/i' , # Mozilla Firefox
            'Chrome'   => '/Chrome/i'  , # Google Chrome
            'Safari'   => '/Safari/i'  , # Apple Safari
            'Opera'    => '/Opera/i'   , # Opera
            'Netscape' => '/Netscape/i', # Netscape
        ];

        foreach( $arrBrowsers as $bname=>$regExp )
        {
            if( preg_match($regExp, $ua) )
            {
                $ub = $bname;
                break;
            }
        }

        # Отдельная проверка на IE. Должна идти самой первой.
        if(preg_match('/MSIE/i',$ua) && !preg_match('/Opera/i',$ua))
            $ub = 'IE'; # Internet Explorer;

        if( $ub === 'UnknownBr' )
            return [$ub,$version]; # Возвращаю дефолт

        # - ###
        # Версия браузера

        // в конце получаем корректный номер версии
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!preg_match_all($pattern, $ua, $matches)) {
            // совпадающие номера не были найдены, просто продолжаем
        }

        // смотрим, сколько у нас есть
        $i = count($matches['browser']);

        if ($i !== 1)
        {
            # Мы получим два раза, так как еще не использовали аргумент 'other'

            # Проверяем указана ли версия до или после имени
            if (strripos($ua,"Version") < strripos($ua,$ub))
                $version = $matches['version'][0];
            else
                @ $version = $matches['version'][1];
        }
        else
        {
            $version= $matches['version'][0];
        }


        // проверяем, получили ли мы номер
        if ( empty($version) )
        {
            $version = "?";
        }
        else
        { # Версия есть.
            if( substr_count($version,'.') )
            {
                $version = explode('.',$version)[0];
            }
        }


        # - ###

        return [$ub,$version];
    }


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
