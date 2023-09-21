<?php

namespace LibMy;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;


/**
 * Сбор и агрегация ВСЕЙ возможной полезной инфы о текущем запросе (из реквеста и роутов + окружения).
 * КРАЙНЕ важный класс - Вообще ВСЯ инфа о реквесте получается ЗДЕСЬ.
 * Используется 100% времени, постоянно.
 * Оттестирован вдоль и поперек в продакшене. Все что могло сломаться уже сломалось и починено.
 */ # ДатаВремя создания: 160921 - Готово
class Requester
{
    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Получение первичных объектов, защищенные от вылетов.

    /**
     * Получить объект Request.
     * @return bool|object Вернет ЛИБО объект, ЛИБО false
     */
    public static function getObjectRequest()
    {
        try{
            $current = Request::capture();
        }catch( \Throwable $e ){
            return false;
        }

        if( empty($current) )
            return false;
        else
            return $current;
    }

    /**
     * Получить объект Route.
     * @return bool|object Вернет ЛИБО объект, ЛИБО false
     */
    public static function getObjectRoute()
    {
        try{
            $current = Route::current();
        }catch( \Throwable $e ){
            return false;
        }

        if( empty($current) )
            return false;
        else
            return $current;
    }

    # - ### ### ###
    #   NOTE: Главный метод для получения всего и сразу одним вызовом.
	
	
	/** IMPORTANT: Получает вообще ВСЕ что можно вытащить из запроса. Отказоустойчив.
	 * @param bool $needDataJson Сжимать ли параметры запросы в JSON
	 * @return array Большой подробный массив
	 * @example ВРЕМЯ РАБОТЫ: AVG из 500 - 7.3мс(Ноут) | 150923 | Инфа устаревает.
	 * @version WORK FINAL
	 */
    public static function getFullRequestAndRouteInfo($needDataJson=true)
    {
        $INFO = array(
            'EXIST' => ['REQUEST'=>false,'ROUTE'=>false],

            'IP' => [],
            'UA'  => [],
            'REQUEST' => [],
            'ROUTE' => [],
            'DATE' => [],
            'PARAMS' => [],
            'COOKIE' => [],
            'HEADERS' => [],
            'USER'  => [],
            'ORIGIN' => [],
        );

        # - ### ### ###

        # Объект либо false
        $currentRequest = self::getObjectRequest();
        $currentRoute   = self::getObjectRoute();

        $INFO['EXIST']['REQUEST'] = (bool) $currentRequest;
        $INFO['EXIST']['ROUTE']   = (bool) $currentRoute;

        # - ### ### ###

        $INFO['IP']     = self::getIpInfo();
        $INFO['UA']     = self::getUserAgentInfo();
        $INFO['REQUEST']= self::getRequestInfo();
        $INFO['ROUTE']  = self::getRoutingInfo();
        $INFO['DATE']   = self::getDates();
        $INFO['PARAMS'] = self::getDataInfo($needDataJson);
        $INFO['COOKIE'] = self::getCookieInfo();
        $INFO['HEADERS']= self::getHeadersInfo($needDataJson);
        $INFO['USER']   = self::getUserAccountInfo();
        $INFO['ORIGIN'] = self::getUserOriginUrl();
        
        #dd($INFO, $currentRoute, $currentRequest);

        return $INFO;
    }

    
	/** Дубликат с удобным названием.
	 * @param bool $needDataJson Сжимать ли параметры запросы в JSON
	 * @return array
	 * @version WORK FINAL
	 */
    public static function getAllInfo($needDataJson=true)
    {
        return self::getFullRequestAndRouteInfo($needDataJson);
    }

    # - ### ### ###
    #   NOTE: Получение отдельных частей информации.

    /**
     * Получить всю информацию о IP + Доп инфа + Предобработанный варианты.
     * @return array Всегда массив.
     */
    public static function getIpInfo()
    {
        $fin = [
            'EXIST'   => false,
            'RAW'     => 'EMPTY',
            'RAW_LEN' => 0,
            'IS_PROXY' => false,
            'IS_IPv4' => false,
            'IS_IPv6' => false,
            'SUB_16'   => 'EMPTY', # Всегда длина 16 или меньше - IPv4
            'SUB_39'   => 'EMPTY', # Всегда длина 39 или меньше - IPv6
            'SUBPAD_16' => 'EMPTY', # Всегда длина 16 - для ровного вывода во фронт

            'IPv4_MASK' => [
                '32' => '',
                '24' => '',
                '16' => '',
                '8' => '',
            ],
        ];

        # - ###

        $currentRequest = self::getObjectRequest();

        # Если нет объекта реквеста
        if( $currentRequest === false )
            return $fin;

        # Реквест есть, получаю IP
        $ip = $currentRequest->ip();
        #$ip = '2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d';

        /* NOTE: Аналог
        $ipRaw =    filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP',       FILTER_VALIDATE_IP)
                 ?: filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_VALIDATE_IP)
                 ?: $_SERVER['REMOTE_ADDR']
                 ?? $textIfEmpty;  */


        # Если юзер не прислал свой IP
        if( empty($ip) )
            return $fin;

        # - ###

        $fin['EXIST'] = true;

        $fin['RAW']     = $ip;
        $fin['RAW_LEN'] = strlen($ip);

        $fin['IS_PROXY'] = (! empty($_SERVER['HTTP_X_REQUESTED_WITH']));
        
        $fin['IS_IPv4'] = substr_count($ip, '.') === 3; # 111.111.111.111 = 15симв 3точки
        $fin['IS_IPv6'] = (bool) strstr($ip, ':'); # 2001:0db8:11a3:09d7:1f34:8a2e:07a0:765d = 39симв
        # Можно использовать php метод filter_var с ключами на ip

        # Обрезаю только если надо. Иначе прсто присваиваю.
        if( $fin['RAW_LEN'] > 16 )
            $fin['SUB_16'] = substr($ip, 0,15);
        else
            $fin['SUB_16'] = $ip;

        if( $fin['RAW_LEN'] > 39 )
            $fin['SUB_39'] = substr($ip, 0,38);
        else
            $fin['SUB_39'] = $ip;

        $fin['SUBPAD_16'] = str_pad($fin['SUB_16'], 15, ' ');

        # - ###

        if( $fin['IS_IPv4'] )
        {
            $buf = explode('.', $ip);
            $fin['IPv4_MASK']['32'] = $buf[0] . '.' . $buf[1] . '.' . $buf[2] . '.' . $buf[3];
            $fin['IPv4_MASK']['24'] = $buf[0] . '.' . $buf[1] . '.' . $buf[2] . '.xxx';
            $fin['IPv4_MASK']['16'] = $buf[0] . '.' . $buf[1] . '.xxx.xxx';
            $fin['IPv4_MASK']['8']  = $buf[0] . '.xxx.xxx.xxx';
        }

        # - ###

        return $fin;

        # - ###

        /*
            #$_SERVER['REMOTE_ADDR'] не всегда содержит реальный адрес, если клиент зашел через прокси-сервер, то будет адрес прокси-сервера.

            //содержат реальные адреса если клиент зашел через прокси-сервер. Адресов может быть несколько, разделенны запятыми.

            #$_SERVER['HTTP_CLIENT_IP'] ; // хранится глобальный IP пользователя, т.е. его адрес в сети Интернет.
            #$_SERVER['HTTP_X_FORWARDED_FOR'] ; // если прокси, и если вообще разрешает видить реальный ип
        */
    }

    /**
     * Получить всю информацию о UserAgent + Предобработанные варианты.
     * @param string $ua По умолчанию. 'DEF' либо свой UA
     * @return array Всегда массив.
     */
    public static function getUserAgentInfo($ua='DEF')
    {
        $INFO = [
            'EXIST'   => false,
            'RAW'     => 'EMPTY',
            'RAW_LEN' => 0,
            'SUB_64'  => 'EMPTY',
            'SUB_128' => 'EMPTY',
            'SUB_256' => 'EMPTY',
            'EXT_TEXT' => 'EMPTY',
            'EXT_TEXT_PAD' => 'EMPTY',
            'EXT_IS_MOBILE' => false,
        ];

        # - ### ### ###
        # NOTE: Могут быть длиннющие -> 500+ и 2000+
        #  Обычно около 100-150

        if( $ua === 'DEF' )
            $ua = UserAgenter::getUa_Server();

        # - ###

        $basicInfo = UserAgenter::getBasicInfo($ua);

        $INFO = array_merge($INFO,$basicInfo);

        if( ! $INFO['EXIST'] )
            return $INFO;

        # - ###

        try{
            # Сложный метод, может вылететь.
	        $UA_INFO = UserAgenter::getFullParsedInfo($ua);
            
            $INFO['EXT_TEXT'] = $UA_INFO['TEXT'];
            $INFO['EXT_TEXT_PAD'] = $UA_INFO['TEXT_PAD'];
	        $INFO['EXT_IS_MOBILE'] = $UA_INFO['IS_MOB'];

        }catch(\Exception $e){
	        $INFO['EXT_TEXT'] = 'PARSING ERROR - Exception. MSG: '.$e->getMessage();
	        $INFO['EXT_TEXT_PAD'] = $INFO['EXT_TEXT'];
        }

        # - ###

        return $INFO;
    }
    
    /**
     * Получить всю информацию о текущем роуте и ссылке.
     * @return array Всегда массив.
     */
    public static function getRoutingInfo()
    {
        $INFO = [
            'METHOD' => 'EMPTY',

            'CONTROLLER_FULL' => 'EMPTY',
            'CONTROLLER'     => 'EMPTY',
            'ACTION'        => 'EMPTY',
            'ACTION_FULL'  => 'EMPTY',
            'ACTION_FULL_2'  => 'EMPTY',

            'URL_FULL' => 'EMPTY', # Есть ВСЕГДА.
            'URL_FULL_SUB32' => 'EMPTY',
            'URL_FULL_SUB64' => 'EMPTY',
            'URL_FULL_SUB80' => 'EMPTY',
            'URL_FULL_SUB128' => 'EMPTY',

            'URI_PATTERN' => 'EMPTY',
            'URI_PATH' => 'EMPTY',

            'URI_PARAMS_RAW' => 'EMPTY',
            'URI_PARAMS_COUNT' => 0,
            'URI_PARAMS_JSON' => 'EMPTY',
            'URI_PARAMS_JSON_LEN' => 0,

            'URI'  => 'EMPTY', # Есть ВСЕГДА
            'NAME' => 'EMPTY',
        ];

        # - ### ### ###
        # NOTE: Реквеста или роута может и не быть!!

        $currentRoute   = self::getObjectRoute();
        $currentRequest = self::getObjectRequest();

        # Если объекта реквеста есть
        if( $currentRoute !== false )
        {
            $contrFull = Route::current()->getActionName();
            $INFO['CONTROLLER_FULL'] = $contrFull; # App\Http\Controllers\TestController@test
            $INFO['CONTROLLER'] = explode('@', explode('\\', $contrFull)[substr_count($contrFull, '\\')])[0];

            $INFO['ACTION'] = Route::current()->getActionMethod(); # actionNewDonate;
            $INFO['ACTION_FULL'] = $INFO['CONTROLLER'].'@'.$INFO['ACTION'];

            $INFO['NAME'] = Route::current()->getName(); # payments.new-donate; TestC.Test1
            $INFO['URI_PATTERN'] = $currentRoute->uri; # test/{var1}

            $INFO['URI_PARAMS_RAW'] = $currentRoute->parameters; # Массив подставленных в URI параметров.
            $INFO['URI_PARAMS_COUNT'] = count($currentRoute->parameters);
            $INFO['URI_PARAMS_JSON'] = json_encode($currentRoute->parameters);
            $INFO['URI_PARAMS_JSON_LEN'] = strlen($INFO['URI_PARAMS_JSON']);
        }

        if( $currentRequest !== false )
        {
        	# BUG: Был вылет Call to undefined function /test()  но не смог отловить надежно.
            $INFO['URL_FULL'] = Request::url(); # С гетом; https://s-script-gen-3/test/123
            
	        
	        $INFO['URL_FULL_SUB32']  = substr($INFO['URL_FULL'],0,31);
            $INFO['URL_FULL_SUB64']  = substr($INFO['URL_FULL'],0,63);
            $INFO['URL_FULL_SUB80']  = substr($INFO['URL_FULL'],0,79);
            $INFO['URL_FULL_SUB128'] = substr($INFO['URL_FULL'],0,127);
	
	        #$INFO['URL_FULL_SUBPAD80'] = str_pad($INFO['URL_FULL_SUB80'],80,' ');
	        # 80 = 'https://lara.1111111111.site/s/035313e2737313e2633323e27383/2633323e27381113/_/;'
            
            $INFO['URI_PATH'] = $currentRequest->getPathInfo(); # /
            $INFO['URI'] = $currentRequest->getRequestUri(); # /test/123?aaa=123
            #$INFO['URI_123'] = Request::path(); # artisan; # /test/123  вроде дубль
            $INFO['METHOD'] = Request::method(); # POST/GET/...   # Дубль из DATA

            $INFO['ACTION_FULL_2'] = $INFO['METHOD'].':'.$INFO['CONTROLLER'].'@'.$INFO['ACTION'];
        }

        return $INFO;
    }

    /**
     * Получить дату совершенного запроса по UTC + MSK.
     * @todo В идеале переписать на получение из реквеста, но не критично.
     * @return array Всегда массив с 2 датами.
     */
    public static function getDates()
    {
        $INFO = [
            'UTC' => 'EMPTY',
            'MSK' => 'EMPTY',
        ];

        # - ### Дата

        $INFO['UTC'] = CarbonDT::getNow();
        $INFO['MSK'] = CarbonDT::getNowMsk();

        return $INFO;
    }

    /**
     * Получить входные данные запроса + Доп информация + JSON.
     * @param bool $needJson Сжимать ли данные запроса в JSON.
     * @return array Всегда массив.
     */
    public static function getDataInfo($needJson=true)
    {
        # NOTE: В теории может прийти ддосный мусорный запрос на 100к символов и тд.
        #  Считаю, что такое блочится на первом этапе проверок и сюда дойдет только адекватный запрос
        # BUG: Если пришлют запрос на 50к символов и тп, то все ляжет.

        $INFO = [
            'EXIST'    => false,
            'IS_EMPTY' => true,
            'METHOD'   => 'EMPTY',
            'ARR_RAW'  => [],
            'ARR_COUNT' => 0,
            'JSON_DATA'  => 'EMPTY',
            'JSON_LEN'   => 0,
        ];

        # - ###

        $currentRequest = self::getObjectRequest();

        # Если нет объекта реквеста
        if( $currentRequest === false )
            return $INFO;


        # Реквест есть, получаю все входящие данные
        $dataArr = Request::toArray();

        # - ###

        $INFO['EXIST']  = true;
        $INFO['METHOD'] = Request::method(); # POST/GET/...

        if( ! empty($dataArr['_token']) ) unset($dataArr['_token']);

        $INFO['ARR_RAW'] = $dataArr; # С гетом
        $INFO['ARR_COUNT'] = count($dataArr); # С гетом

        if( $INFO['ARR_COUNT'] !== 0 )
            $INFO['IS_EMPTY'] = false;

        if( $needJson )
        {
            $INFO['JSON_DATA']  = json_encode($dataArr); # С гетом
            $INFO['JSON_LEN']   = strlen($INFO['JSON_DATA']); #
        }
        else
        {
            $INFO['JSON_DATA']  = "SKIP";
        }

        return $INFO;
    }

    /**
     * Получить всю информацию о куках.
     * @return array Всегда массив.
     */
    public static function getCookieInfo()
    {
        $INFO = [
            'IS_EMPTY' => true,
            'HAVE_NULL_KEYS' => false,
            'SERVER_RAW' => '',
            'ARR_DECODED'  => [],
            'ARR_KEYS'  => [],
            'ARR_COUNT' => 0,
            'JSON_DATA'  => 'EMPTY',
            'JSON_LEN'   => 0,
        ];

        $INFO['SERVER_RAW'] = @ $_SERVER['HTTP_COOKIE']; #

        $DATA = request()->cookie(); # Array|null
        if(empty($DATA))
            return $INFO;

        $INFO['IS_EMPTY'] = false; #
        $INFO['ARR_DECODED'] = $DATA; #
        $INFO['ARR_KEYS'] = array_keys($DATA); #
        $INFO['ARR_COUNT'] = count($DATA); #

        $INFO['JSON_DATA']  = json_encode($DATA); #
        $INFO['JSON_LEN']   = strlen($INFO['JSON_DATA']); #

        # Проверка на предмет ручного вмешательства юзера в файлы кук.
        foreach( $INFO['ARR_KEYS'] as $key )
            if( is_null($INFO['ARR_DECODED'][$key]) ) # Именно null
            {
                $INFO['HAVE_NULL_KEYS'] = true;
                break;
            }

        return $INFO;
    }

    /**
     * Получить базовые данные о аккаунте пользователя. Только если он залогинен.
     * @todo Потом дописать, пока сойдет
     * @return array Всегда массив.
     */
    public static function getUserAccountInfo()
    {
        $INFO = [
            'LOGGED' => false,
            'UID'     => 'EMPTY',
            'USERNAME' => 'EMPTY',

            'SSID_RAW' => 'EMPTY',
            'SSID_L10' => 'EMPTY',
            'SSID_L16' => 'EMPTY',
        ];

        # - ###

        # IMPORTANT: Если БД не подключена, то может быть вылет. Должен ловится исключением.
        if( Auth::check() )
        {
            try{
                #$uid = Auth::id();
                $INFO['LOGGED']   = true;
                $INFO['UID']      = Auth::user()->uid;
                $INFO['USERNAME'] = Auth::user()->username;

                #TODO: $ = User::field_Username('GET', $uid); #

                $INFO['SSID_RAW'] = Session::getId();
                $INFO['SSID_L10'] = Stringer::sliceTo(Session::getId(),10,true);
                $INFO['SSID_L16'] = Stringer::sliceTo(Session::getId(),16,true);

            }catch (\Exception $e){
                # TODO: Запись в логи об ошибке
                return $INFO;
            }

        }

        # - ###

        return $INFO;
    }

    /**
     * Получить URL откуда пришел пользователь + Предобработанные данные.
     * @todo Доработать парсинг адреса - нужны реальные примеры
     * @todo Спорное = Добавить парсинг get параметров из ссылки в массив.
     * @return array Всегда массив.
     */
    public static function getUserOriginUrl()
    {
        $INFO = [
            'EXIST'   => false,
            'DOMAIN_SHORT' => 'EMPTY',
            'DOMAIN_FULL' => 'EMPTY',
            'RAW'     => 'EMPTY',
            'RAW_LEN' => 0,
            'SUB_64'  => 'EMPTY',
            'SUB_128' => 'EMPTY',
            'SUB_256' => 'EMPTY',
        ];

        # Реальные примеры = https://www.google.com/

        # - ###

        # NOTE: Бывают юзеры без этого поля.

        if( empty($_SERVER['HTTP_REFERER']) )
            return $INFO;

        $url = $_SERVER['HTTP_REFERER'];
        #$url = 'https://site/test/123/543/123//?rew=3213'; # Тестовый.

        # - ###

        $INFO['EXIST'] = true;

        $domainParts = explode('/', $url,5);
        $INFO['DOMAIN_SHORT'] = @$domainParts[2]; # BUG: Тут реально вылетало пару раз. Из-за несущ индекса=2.  Вылетали эти:  www.google.com  www.ask.com
        $INFO['DOMAIN_FULL']  = implode('/',array_slice($domainParts,0,3)).'/';

        $INFO['RAW']     = $url;
        $INFO['RAW_LEN'] = strlen($url);

        $INFO['SUB_64']  = substr($url, 0,63);
        $INFO['SUB_128'] = substr($url, 0,127);
        $INFO['SUB_256'] = substr($url, 0,255);

        # - ###

        return $INFO;
    }
	
	
	/**
	 * Сгенерировать уникальные ID для этого юзера и этого запроса.
	 * ID юзера уникален, но не меняется между запросами. ID запроса всегда разный.
	 * Уникальность не проверяется явно, совпадение возможно но исчезающе маловероятно.
	 *
	 * Посчитать время работы скрипта от LARAVEL_START + Промежуточные данные.
	 * @return array Всегда массив.
	 */
	public static function getRequestInfo()
	{
		# - ###
		$TIME = [
			'START' => LARAVEL_START,
			'NOW' => microtime(true),
		]; # Вычисляется не все разом - чтоб было фиксированное(везде одно) время NOW
		
		$TIME['DIFF_RAW'] = $TIME['NOW']-$TIME['START'];
		$TIME['DIFF_MS'] = $TIME['DIFF_RAW']*1000;
		$TIME['MS'] = (string)(int)$TIME['DIFF_MS'];
		# - ###
		
		# TESTING
		# Return true if request was sent by javascript
		$AJAX = ( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest') );
		
		# - ###
		
		return [
    		'ID_USER' => IdGenerator::methodMD5_FromIpUa_Parts4x3(),
    		'ID_CURRENT' => IdGenerator::methodMD5_FromIpUaTime_Parts4x3(),
			'TIME_MS' => $TIME['MS'],
			'IS_AJAX' => $AJAX,
		];
		
		# - ###
	}
    
	
	/** 100723 TESTING
	 * Получить первичные заголовки запроса + Промежуточные данные.
	 * @param bool $needJson Сжимать ли данные запроса в JSON.
	 * @return array Всегда массив.
	 */
	public static function getHeadersInfo($needJson=true)
	{
		$INFO = [
			'IS_EMPTY' => true,
			
			'ARR_RAW'  => [],
			'ARR_KEYS'  => [],
			'ARR_COUNT' => 0,
			'ARR_ASOC'  => [],
			
			'JSON_DATA'  => 'EMPTY',
			'JSON_LEN'   => 0,
		];
		
		$DATA = Request::header(); # Array|null
		if(empty($DATA))
			return $INFO;
		
		# - ###
		
		$INFO['IS_EMPTY'] = false;
		$INFO['ARR_RAW'] = $DATA;
		$INFO['ARR_KEYS'] = array_keys($DATA);
		$INFO['ARR_COUNT'] = count($DATA);
		
		# NOTE: Предполагаю, что там всегда 1 элемент. Больше не может быть по логике заголовков. 1ключ-1значение.
		foreach( $DATA as $key=>$val )
			$INFO['ARR_ASOC'][$key] = $val[0];
		
		
		if( $needJson )
		{
			$INFO['JSON_DATA'] = json_encode($INFO['ARR_ASOC']); #
			$INFO['JSON_LEN']  = strlen($INFO['JSON_DATA']); #
		}
		else
		{
			$INFO['JSON_DATA']  = "SKIP";
		}
		
		return $INFO;
	}
	
    
    # - ### ### ###
	
	
    /*      */

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
