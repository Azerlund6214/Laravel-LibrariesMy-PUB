<?php

namespace LibMy;



/**
 * Класс для любой работы с доменами.
 */
class NetInfoDomain
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }

    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    /** WORK
     * Получить подробную информацию о домене и протоколе.
     * @return array
     */
    public static function getCurrentDomainInfo()
    {
        $INFO = [
            'WARNING' => 'АХТУНГ!!!  Может работать криво.  Тестить на реал хостинге с реал доменом.' ,

            'PROTOCOL' => [
                'IS_HTTPS' => (request()->getScheme() === 'https') ,
                'IS_HTTP' => (request()->getScheme() === 'http') ,
                'VERSION' => request()->getProtocolVersion() , # HTTP/2.0
                'PORT' => request()->getPort() , # 443(https) / 80(http)
            ] ,
            'DOMAIN' =>
                [
                    'FULL' => request()->getSchemeAndHttpHost() , # https://processing
                    'SHORT' => request()->getHost() , # processing     лара, защищенный метод
                    'SHORT2' => request()->getHttpHost() , # processing   # добавит порт, если он не стандартный
                    # TODO: Выяснить какой из них лучше

                    'ZONE' => 'EMPTY' ,
                    'PROTOCOL' => request()->getScheme() . '://' ,
                    #'SOCKET' => request()->getSchemeAndHttpHost().':'.request()->getPort(),
                ] ,
        ];
        # - ###

        # Надо чекать на реал хостинге.
        #dd(request()->getUri()); # https://processing/test
        #dd(url('/')); # https://processing
        #dd($_SERVER['HTTP_HOST'], $_SERVER['SERVER_NAME']); # оба можно подменить   processing   processing

        #$host = 'https://www.ads.processing.com';
        $host = request()->getHost();
        $cntDots = substr_count($host , '.');
        if( $cntDots ){
            $INFO['DOMAIN']['ZONE'] = '.' . explode('.' , $host)[$cntDots];
        }

        # - ###

        return $INFO;
    }


    # - ### ### ###
    #   NOTE:

	# CONCEPT: Ооооочень древний.   На полную переработку.
	/**
	 * Получить строку c доменом этого сервера - "https://www.yandex123.ru:80", "http://localhost:80"
	 * @param bool $Protocol = Добавлять ли протокол
	 * @param bool $Port = Добавлять ли порт
	 * @return string
	 */
	public static function Get_This_Server_Domain( $Protocol=true, $Port=false)
	{
		$Domain = $_SERVER['HTTP_HOST'];
		
		if ( $Protocol === true)
		{
			$Prot = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http://' : 'https://';
			
			$Domain = $Prot.$Domain;
		}
		
		
		if ( $Port === true)
			$Domain = $Domain.":".$_SERVER['SERVER_PORT'];
		
		
		return $Domain;
	}

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
