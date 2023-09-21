<?php

namespace LibMy;

use Illuminate\Support\Facades\Log;

/**
 * Крайне важный класс. Полностью, на 100% отвечает за логи любых вылетов скрипта.
 * Вообще все пишется здесь, вся логика и обработки.
 * Используются соседние хелперы.
 * Предельно отказоустойчив.
 *
 */ # Переписан с 0 на основе кривых прошлых. 020523.  Ушло 7 часов подряд.
class LogExceptionHandlerMy
{
    # - ### ### ###

    public function __construct($ERR) {  $this->BASE_ERROR = $ERR;  $this->TIMER = new TimerMy(); }
    public function __destruct()  {    }

    # - ### ### ###

    public $OPTS_ENV = [];
    public static $OPTS_CHANNELS = [
    	# Сопоставление моего кода уровня ошибки и имени канала лога в ларе.
        'POSSIBLE'  => 'possible',
        'HTTP_404'  => 'http-404',

        'SUSPECT'   => 'suspect',
        'CRITICAL'  => 'critical',
        'UNDEFINED' => 'undefined',

        'TRY_CATCH_1' => 'bugsInExpHandler_1',
        'TRY_CATCH_2' => 'bugsInExpHandler_2',
    ];
	
	public $OPTS_LOG_FILES_FOLDER = 'storage-logs\\LOGS_SINGLE'; # Имя папки куда писать одиночные логи. От корня.
	public $OPTS_LOG_FILES_FOLDER_MakeIfNeed = true; # Создать если нет.
	
	
    
    # - ### ### ###
	# - ### ### ###
	#   NOTE:  Главный метод
	
	
	# CONCEPT:
	#  -
	#  - Дописать телегу
	#  - Пересорт порядка методов в классе.
	#  -
	
	
	#  NOTE: !!! НЕ прерывать здесь работу !!!  -  ВСЕГДА возвращать просто return;
	#   Иначе не будет ни экрана ошибки, ни файла логов.
	# IMPORTANT - Вся логика в 1 методе.
	public static function MakeMagic( $ERROR )
	{
		# - ###
		# NOTE: Этот метод должен всегда, в 100% случаев штатно отрабатывать.
		#  - Ошибки и вылетут тут недопустимы иначе не будет лога ошибки.
		# - ###
		
		# IMPORTANT
		set_time_limit(600); # 10мин
		ignore_user_abort(true);
		ini_set('memory_limit','512M');
		# Чтоб точно не было сюрпризов при генерации.
		
		# - ###
		$MM = new self($ERROR); # И сразу таймер
		# - ###
		try{
			# - ###
			
			$MM->setOpts_ENV();
			
			if( $MM->OPTS_ENV['ERR_HANDLER_PERMANENT_USE_DEFAULT'] === true )
				return;
			
			
			# - ###
			# Вытаскиваю вообще всю базовую инфу что есть и пишу в первичные поля класса.
			# Получаю ВСЕ первичные данные. ВСЕ тут. Дальше только использование.
			$MM->setAllBaseVars();
			
			
			# - ###
			# Все 404 пишутся в 1 файл
			
			if( $MM->BASE_EXP_INFO['ERROR_HTTP']['HTTP_CODE'] === 404 )
			{
				# Браузеры запрашивают фавикон из корня сайта. А у меня он кастомный. Это лишняя ошибка.
				if( $MM->BASE_REQUESTER['ROUTE']['URI'] === '/favicon.ico' )
					return;
				
				$TEXT = $MM->generateLogText_Http404_OneStr();
				$MM->actionMakeLogInChannel('HTTP_404' ,$TEXT);
				
				return;
			}
			
			# - ###
			# Разобр прочих ошибок HTTP + POSSIBLE
			# Пока все в 1 файл. В 1 строчку.
			
			# IMPORTANT = Однострочный лог.
			# Либо распознан как POSSIBLE, Либо распознан как HTTP, Либо если пустое сообщение.
			if(    $MM->BASE_EXP_INFO['LEVEL_MY']['LEVEL']==='POSSIBLE'
				|| $MM->BASE_EXP_INFO['ERROR_HTTP']['IS_HTTP_EXP']
				|| $MM->BASE_EXP_INFO['BASIC_INFO']['MSG_EMPTY']  )
			{
				$TEXT = $MM->generateLogText_PossibleOther_OneStr();
				
				$MM->actionMakeLogInChannel('POSSIBLE' ,$TEXT);
				
				return; # Нужен, Сделает редирект либо отобразит страницу 404 и тд.
			}
			
			# - ###
			# Генерирую большую портянку текста и собираю в итоговую строку
			
			$MM->generateLogEdges();
			
			$TEXT_ArrStr = $MM->generateLogText_UnivBIG_StrArray();
			
			$TEXT = PHP_EOL . $MM->TEXT_EDGES['BEG'] . implode(PHP_EOL,$TEXT_ArrStr) . $MM->TEXT_EDGES['END'];
			
			# - ###
			
			#$a=1/0;  # NOTE:
			
			# Лог в общий файл нужного уровня
			$MM->actionMakeLogInChannel($MM->BASE_EXP_INFO['LEVEL_MY']['LEVEL'] ,$TEXT);
			
			# Лог в одиночный файл в спец папку
			$MM->actionMakeLogInFileSingle($TEXT);
			
			# - ###
			
			$MM->sendTelegramIfNeed();
			
			# - ###
			#
			
			
			# - ###
		}catch( \Throwable $ERROR_TC){ $MM->MakeMagic_TryCatch_1($ERROR , $ERROR_TC); }
	}
	
	# NOTE: Если произошел вылет во время обработки.
	public function MakeMagic_TryCatch_1( $ERROR , $ERROR_TC )
	{
		try{
			# NOTE: Все еще пробую использовать уже простейшую автоматизацию сбора инфы.
			
			$ARR = [ '',
			         '======================================================',
			         'Вылет в MakeMagic() в генерации основных строк отчета',
			         '',
			         'JSON: Исходный лог (зачем вызвали report)',
			         json_encode(ExceptionInfoCollector::getFullExceptionInfo($ERROR)),
			         'MSG: '.$ERROR->getMessage(),
			         '',
			         'JSON: Этот непредвиденный вылет (в момент создания отчета)',
			         json_encode(ExceptionInfoCollector::getFullExceptionInfo($ERROR_TC)),
			         'MSG: '.$ERROR_TC->getMessage(),
			         '',
			         'REQUEST INFO: '.json_encode(Requester::getAllInfo(true)),
			         PHP_EOL.PHP_EOL.PHP_EOL,
			];
			#$a=1/0;  # NOTE:
			self::actionMakeLogInChannel('TRY_CATCH_1' , implode(PHP_EOL,$ARR));
			
		}catch( \Throwable $ERROR_TC_TC ){  $this->MakeMagic_TryCatch_2($ERROR , $ERROR_TC , $ERROR_TC_TC);  }
		
		return;
		
	}
	public function MakeMagic_TryCatch_2( $ERROR , $ERROR_TC , $ERROR_TC_TC )
	{
		# NOTE: Использую только стандартные методы.
		
		$ARR = [ '',
		         '========================= *** ЖОПА *** ==========================',
		         'Двойной вылет. Сначала в MakeMagic(), потом в трайкатче.',
		         '',
		         'Исходный  MSG: '.$ERROR->getMessage(),
		         'Исходный File: '.$ERROR->getFile(),
		         'Исходный Line: '.$ERROR->getLine(),
		         'Исходный JSON-TraceAsString: '.json_encode($ERROR->getTraceAsString() ),
		         'Исходный Class: '.get_class($ERROR),
		
		         '',
		         'Первый TryCatch  MSG: '.$ERROR_TC->getMessage(),
		         'Первый TryCatch File: '.$ERROR_TC->getFile(),
		         'Первый TryCatch Line: '.$ERROR_TC->getLine(),
		         'Первый TryCatch JSON-TraceAsString: '.json_encode($ERROR_TC->getTraceAsString() ),
		         'Первый TryCatch Class: '.get_class($ERROR_TC),
		
		         '',
		         'Этот второй TryCatch  MSG: '.$ERROR_TC_TC->getMessage(),
		         'Этот второй TryCatch File: '.$ERROR_TC_TC->getFile(),
		         'Этот второй TryCatch Line: '.$ERROR_TC_TC->getLine(),
		         'Этот второй TryCatch JSON-TraceAsString: '.json_encode($ERROR_TC_TC->getTraceAsString() ),
		         'Этот второй TryCatch Class: '.get_class($ERROR_TC_TC),
		
		         PHP_EOL.PHP_EOL.PHP_EOL,
		];
		#$a=1/0;  # NOTE:
		self::actionMakeLogInChannel('TRY_CATCH_2' , implode(PHP_EOL,$ARR));
		
		# NOTE: Если вылет и тут, то будет просто белое полотно,  без логов.
		#  Дальше траить не буду
		
		return;
	}
	
	
	# - ### ### ###
	#   NOTE: Все настройки и все получение всех нужных данных. Все в 1 методе. Централизация.
	
	public $TIMER; # Таймер из начала конструктора
	public $BASE_ERROR; # Основная рабочая ошибка. Пишется в конструкторе
	
	public $BASE_REQUESTER;  # TODO: Потом лучше разделить на части
	public $BASE_EXP_INFO;
	public $BASE_DATABASE;
	public $BASE_TRACE_INFO_MY = [];  # TESTING
	public $BASE_CODE_NEAR_STR = [];
	
	public $BASE_COOKIE;
	public $BASE_SESSION;
	
	public function setAllBaseVars()
	{
		# - ###
		# Стабильное 100%
		
		$this->BASE_REQUESTER = Requester::getAllInfo(true);
		$this->BASE_EXP_INFO = ExceptionInfoCollector::getFullExceptionInfo($this->BASE_ERROR);
		$this->BASE_DATABASE = DataBaseInfoCollector::getFullDbInfo();
		
		$this->BASE_SESSION = SessionMy::sessionGetAllJson();
		$this->BASE_COOKIE = CookieMy::cookieGetAllJson();
		
		# - ###
		# Стабильное, но в теории могут быть косяки.
		
		try{ # WORK
			$this->BASE_CODE_NEAR_STR = Filer::readLineNear($this->BASE_EXP_INFO['BASIC_INFO']['FILE'],
				$this->BASE_EXP_INFO['BASIC_INFO']['LINE'],5);
		}catch(\Throwable $e){ $this->BASE_CODE_NEAR_STR = ['TryCatch @setAllBaseVars()',$e->getMessage()]; } # WORK
		#dd($this->BASE_ERROR,$this->BASE_EXP_INFO);
		
		# - ###
		# TESTING - Будут вылеты.
		
		try{ # WORK
			$traceGood = StackTraceUntangler::stackEraseUselessFrames($this->BASE_ERROR->getTrace());
			
			foreach( $traceGood as $one )
				$this->BASE_TRACE_INFO_MY []= "{$one['class']} {$one['type']} {$one['function']}( ?шт )";
			
			if( ! count($this->BASE_TRACE_INFO_MY) )
				$this->BASE_TRACE_INFO_MY []= 'Пусто, не было полезных фреймов.';
			
		}catch(\Throwable $e){ $this->BASE_TRACE_INFO_MY = ['TryCatch @setAllBaseVars() = '.$e->getMessage()]; } # WORK
		
		# - ###
		
		
		
		# - ###
	}
	
	public function setOpts_ENV()
	{
		# - ###
		
		# Тут проврки енв
		
		# - ###
		
		# BUG - В теории может подтянуться дефолтный .env где нет этого ключа. Поэтому есть дефолт.
		$this->OPTS_ENV['ERR_HANDLER_PERMANENT_USE_DEFAULT' ] = env('ERR_HANDLER_PERMANENT_USE_DEFAULT',false);
		
		#$this->OPTS_ENV['PROJECT_NAME'] = env('ERR_HANDLER_PROJECT_NAME');
		
		# - ###
		
		$this->OPTS_ENV['TG_ENABLED' ] = env('ERR_HANDLER_TG_ENABLED',false);
		
		$this->OPTS_ENV['TG_AUTH_RAW'] = env('ERR_HANDLER_TG_AUTH','|');
		$tgA = $this->OPTS_ENV['TG_AUTH_RAW'];
		
		if( ! strstr( $tgA , '|' ) ) $tgA = '|'; # Защита
		
		$this->OPTS_ENV['TG_AUTH_KEY'] = explode('|',$tgA)[0];
		$this->OPTS_ENV['TG_AUTH_ID' ] = explode('|',$tgA)[1];
		
		# - ###
	}
	# CONCEPT: Убрать сюда проверку что енв есть + что это реал енв, мой.
	
	
	# - ### ### ###
	#   NOTE:
	
	
	
	
	# - ### ### ###
	#   NOTE: Запись итоговых текстов в нужные файлы
	
	public function actionMakeLogInChannel( $chCode , string $logTextAll )
	{
		# - ###
		
		$this->onTestMode($logTextAll); # NOTE: Если сейчас тест мод, то сделает dd(). Иначе ничего.
		
		# - ###
		
		Log::channel( self::$OPTS_CHANNELS[$chCode] )->info($logTextAll);
		
		# - ###
	}
	public function actionMakeLogInFileSingle( string $logTextAll )
	{
		# - ###
		
		$this->onTestMode($logTextAll); # NOTE: Если сейчас тест мод, то сделает dd(). Иначе ничего.
		
		# - ###
		
		file_put_contents( $this->GetFullFilePathForLogSingle(),$logTextAll);
		
		# - ###
	}
	
	
	# CONCEPT: Временное имя метода - переименовать. Код уже норм.
	public function GetFullFilePathForLogSingle()
	{
		
		$level = $this->BASE_EXP_INFO['LEVEL_MY']['LEVEL'];
		$date = date("Y-m-d H-i-s",time()+10800);
		
		$method = $this->BASE_REQUESTER['ROUTE']['METHOD']     ?? 'UNDEF';
		$contr  = $this->BASE_REQUESTER['ROUTE']['CONTROLLER'] ?? 'UNDEF';
		$action = $this->BASE_REQUESTER['ROUTE']['ACTION']     ?? 'UNDEF';
		
		# - ###
		
		$Class = Stringer::sliceTo($this->BASE_EXP_INFO['BASIC_INFO']['CLASS'] ,30);
		$Class = str_replace('\\','-',$Class);
		# NOTE: Если кастом ошибка, то там будут слеши  Symfony\Component\Err...
		
		# NOTE: В сообщении могут быть спецсимволы. Их надо вычищать и есть шанс недочистить. Поэтому убрал MSG из имени.
		
		# - ###
		
		$level = str_pad($level , strlen('UNDEFINED'),' ');
		# NOTE: Работает, но по сути юзлесс тк разная длина букв.
		#dd($level);  # UNDEFINED   CRITICAL
		
		$fileName = "{$date} = {$level} = {$method}-{$contr}-{$action} = {$Class}.log";
		$pathFolder = base_path($this->OPTS_LOG_FILES_FOLDER); # IMPORTANT
		
		if( base_path($this->OPTS_LOG_FILES_FOLDER_MakeIfNeed) )
			if( ! is_dir($pathFolder) ) # Создать папку если ее нет и это включено
				mkdir($pathFolder);
		
		return $pathFolder.'\\'.$fileName;
	}
	
	
	# - ### ### ###
	#   NOTE: Вся генерация итоговых строк логов.
	
	# FINAL
	public function generateLogText_Http404_OneStr()
	{
		# - ###
		
		$REQ = $this->BASE_REQUESTER;
		
		#$IP   = $REQ['IP']['SUBPAD_16'];
		#$UA   = $REQ['UA']['EXT_TEXT_PAD'];
		#$UID   = $REQ['REQUEST']['ID_USER'];
		#$RID   = $REQ['REQUEST']['ID_CURRENT'];
		#$URL  = str_pad($REQ['ROUTE']['URL_FULL_SUB80'],80,' ');
		
		# - ###
		
		$JSON = json_encode($REQ);
		$JSON_len = strlen($JSON);
		
		#$TEXT = "HTTP_404 => {$IP} : {$UA} => {$URL} => UID:{$UID} => RID:{$RID} => JSON_REQ (Len={$JSON_len}): Не пишу тк много. См в БД.";
		$TEXT =  implode(' => ',[  # Взято из унив логгера
                   "HTTP_404",
                   #"{$REQ['DATE']['MSK']}",
                   "{$REQ['IP']['SUBPAD_16']}",
                   "{$REQ['UA']['EXT_TEXT_PAD']}",
                   str_pad($REQ['ROUTE']['URL_FULL_SUB80'],80,' '),
                   "ORIG: ".str_pad($REQ['ORIGIN']['SUB_64'],16,' '),
                   "RID-U/R {$REQ['REQUEST']['ID_USER']} {$REQ['REQUEST']['ID_CURRENT']}",
                   "JSON_REQ (Len={$JSON_len}): Смотреть в бд",
                   "{$REQ['UA']['SUB_256']}",
                   #"{}",
		]);
		
		
		return $TEXT;
		# - ###
	}
	public function generateLogText_PossibleOther_OneStr()
	{
		# - ###
		
		$REQ = $this->BASE_REQUESTER;
		$EXP = $this->BASE_EXP_INFO;
		
		$CODE = str_pad($EXP['ERROR_HTTP']['HTTP_CODE'],3,' ');
		$IP   = $REQ['IP']['SUBPAD_16'];
		$URL  = $REQ['ROUTE']['URL_FULL_SUB64'];
		
		# - ###
		
		switch( $EXP['BASIC_INFO']['MSG'] )
		{
			case 'Unauthenticated.'    : $TYPE = 'MSG_AUTH '; break; # Если без логина зашел на роут с посредником авторизации.
			case 'CSRF token mismatch.': $TYPE = 'MSG_CSRF '; break; # Протухла форма.
			case ''                    : $TYPE = 'MSG_EMPTY'; break; #
			default: $TYPE = 'MSG_DEFAULT'; break;
		}
		
		$TEXT = "{$TYPE} => HTTP:{$CODE} => {$IP} => {$URL}";
		
		if($TYPE === 'MSG_DEFAULT') # Пишу доп инфу тк это что-то новенькое.
		{
			$TEXT  = PHP_EOL.$TEXT;
			$TEXT .= PHP_EOL.'Requester => '.json_encode([$REQ]);
			$TEXT .= PHP_EOL.'ExpInfo   => '.json_encode([$EXP]);
			$TEXT .= PHP_EOL;
		}
		
		return $TEXT;
		# - ###
	}
	public function generateLogText_UnivBIG_StrArray()
	{
		# - ###
		# В явном виде выношу все в переменые. Чтоб все было в 1 месте в начале.
		
		$EXP = $this->BASE_EXP_INFO;
		$REQ = $this->BASE_REQUESTER;
		$DB  = $this->BASE_DATABASE;
		
		$COOK = $this->BASE_COOKIE;
		$SESS = $this->BASE_SESSION;
		
		$TRACE_INFO = $this->BASE_TRACE_INFO_MY;
		$CODE_NEAR  = $this->BASE_CODE_NEAR_STR;
		
		$TEXT   = [];
		
		# - ###
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Общая инфа (Обзорная)';
		$TEXT []= 'My Level : '.$EXP['LEVEL_MY']['LEVEL'];
		$TEXT []= 'Exp Class: '.$EXP['BASIC_INFO']['CLASS'];
		$TEXT []= 'Exp Msg  : '.$EXP['BASIC_INFO']['MSG'];
		
		$TEXT []= '';
		$TEXT []= 'Date MSK: '.$REQ['DATE']['MSK'];
		$TEXT []= 'Have Request: '.($REQ['EXIST']['REQUEST'] ? 'true' : 'false') ;
		$TEXT []= 'Have Route  : '.($REQ['EXIST']['ROUTE'] ? 'true' : 'false') ;
		$TEXT []= 'ENV_MY_CONNECTED: '.((env('APP_MY_ENV_DEFINED',false)===true) ? 'true' : 'false') ;
		
		$TEXT []= '';
		$TEXT []= 'SERVER_DOMAIN: '.($_SERVER['HTTP_HOST'  ] ?? 'UNDEF');
		$TEXT []= 'SERVER_ADDR  : '.($_SERVER['SERVER_ADDR'] ?? 'UNDEF');
		$TEXT []= 'SERVER_PORT  : '.($_SERVER['SERVER_PORT'] ?? 'UNDEF');
		
		$TEXT []= '';
		$TEXT []= 'CONFIG__DB_NAME : '.$DB['CONFIG__DB_NAME'];
		$TEXT []= 'PDO_CONNECTED   : '.($DB['PDO_CONNECTED'] ? 'true' : 'false') ;
		if( ! $DB['PDO_CONNECTED'] ) $TEXT []= 'PDO_ERROR_JSON_ALL : '.json_encode($DB) ;
		
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### О вылете подробно';
		$TEXT []= 'My Level : '.$EXP['LEVEL_MY']['LEVEL'];
		$TEXT []= 'Exp Class: '.$EXP['BASIC_INFO']['CLASS'];
		$TEXT []= 'Exp Msg  : '.$EXP['BASIC_INFO']['MSG'];
		$TEXT []= 'File+Line: '.$EXP['BASIC_INFO']['FILE_LINE'];
		$TEXT []= 'Trace JSON: '.$EXP['BASIC_INFO']['TRACE_JSON'];
		$TEXT []= 'Trace JSON Len: '.$EXP['BASIC_INFO']['TRACE_JSON_LEN'];
		#$TEXT []= 'Trace JSON B64: '.base64_encode($EXP['BASIC_INFO']['TRACE_JSON']); # Он будет 14к при жсоне 11к  юзлесс
		
		$TEXT []= '';
		$TEXT []= 'Это ошибка HTTP ? -> '.($EXP['ERROR_HTTP']['IS_HTTP_EXP'] ? 'true' : 'false') ;
		$TEXT []= 'Это ошибка  PHP ? -> '.($EXP['ERROR_PHP']['IS_PHP_EXP'] ? 'true' : 'false') ;
		$TEXT []= 'HTTP Код: '.$EXP['ERROR_HTTP']['HTTP_CODE'];
		$TEXT []= 'PHP LVL : '.$EXP['ERROR_PHP']['RESOLVED_NAME'];
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### О реквесте подробно';
		$TEXT []= 'Параметры-метод: '.$REQ['PARAMS']['METHOD'];
		$TEXT []= 'Параметры-count: '.$REQ['PARAMS']['ARR_COUNT'];
		$TEXT []= 'Параметры-JSON    : '.$REQ['PARAMS']['JSON_DATA'];
		$TEXT []= 'Параметры-JSON Len: '.$REQ['PARAMS']['JSON_LEN'];
		$TEXT []= 'Заголовки-JSON    : '.$REQ['HEADERS']['JSON_DATA'];
		$TEXT []= 'Заголовки-JSON Len: '.$REQ['HEADERS']['JSON_LEN'];
		$TEXT []= 'ID USER IP+UA     : '.$REQ['REQUEST']['ID_USER'];
		$TEXT []= 'ID REQ  IP+UA+Time: '.$REQ['REQUEST']['ID_CURRENT'];
		$TEXT []= 'Время работы скрипта: '.$REQ['REQUEST']['TIME_MS'].'ms (От старта до лога)';
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### О роуте подробно';
		$TEXT []= 'Метод: '.$REQ['ROUTE']['METHOD'];
		$TEXT []= 'Контр фулл : '.$REQ['ROUTE']['CONTROLLER_FULL'];
		$TEXT []= 'Контр+экшен: '.$REQ['ROUTE']['ACTION_FULL'];
		$TEXT []= 'URL Full 128: '.$REQ['ROUTE']['URL_FULL_SUB128'];
		$TEXT []= 'URI: '.$REQ['ROUTE']['URI'];
		$TEXT []= 'URI Параметры JSON: '.$REQ['ROUTE']['URI_PARAMS_JSON'];
		$TEXT []= 'Origin-256: '.$REQ['ORIGIN']['SUB_256'];
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Особое';
		$TEXT []= 'Вся сессия: '.$SESS;
		$TEXT []= 'Все куки  : '.$COOK;
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Юзер';
		$TEXT []= 'Auth: '.($REQ['USER']['LOGGED'] ? 'true' : 'false') ;
		$TEXT []= 'UID   : '.$REQ['USER']['UID'];
		$TEXT []= 'Username: '.$REQ['USER']['USERNAME'];
		$TEXT []= '';
		$TEXT []= 'IP 39 : '.$REQ['IP']['SUB_39'];
		$TEXT []= 'UA 256 : '.$REQ['UA']['SUB_256'];
		$TEXT []= 'Computer: '.$REQ['UA']['EXT_TEXT'];
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Стек вызовов (Только полезное)';
		foreach( $TRACE_INFO as $key => $str ) # WORK
			$TEXT []= "$key:   $str";
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Строки рядом +- 5';
		foreach( $CODE_NEAR as $key => $str ) # WORK
			$TEXT []= "$key:   ".trim($str); # Трима достаточно для уборки всех \n и \t
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '### Отладка логера';
		$TEXT []= 'Время от старта конструктора до генерации массива строк = '.$this->TIMER->getTimeMs();
		$TEXT []= 'Исп памяти сейчас  (real) = '.(((double)memory_get_usage(true))/1024/1024).'Mb';
		$TEXT []= 'Исп памяти пиковое (real) = '.(((double)memory_get_peak_usage(true))/1024/1024).'Mb';
		$TEXT []= 'Общая длина текста этого лога = ~'.strlen(implode('',$TEXT)).'симв';
		
		$TEXT []= '';
		$TEXT []= '### ### ### ### ###';
		$TEXT []= '';
		
		# - ###
		
		return $TEXT;
		
		# - ###
	}
	# FINAL
	
	
	# - ### ### ###
	#   NOTE:
	
	
	
	
	# - ### ### ###
	#   NOTE: Тесты и дебаг
	
	# IMPORTANT
	public $TEST_MODE = false;
	public function onTestMode($TEXT_FINAL)
	{
		# - ###
		if( ! $this->TEST_MODE ) return;
		# - ###
		
		EasyFront::echoBackground_Vendor_DEV();
		
		dd(
			'TEST_MODE = '.__METHOD__,
			$this->BASE_ERROR,
			$this,
			$TEXT_FINAL
		);
		
		# - ###
	}
	
	# - ### ### ###
	#   NOTE: Все про телегу
	
	# TODO
	public function sendTelegramIfNeed()
	{
		# - ###
		if( ! $this->OPTS_ENV['TG_ENABLED'] ) return;
		# - ###
		$BOT_KEY = $this->OPTS_ENV['TG_AUTH_KEY'];
		$TARG_ID = $this->OPTS_ENV['TG_AUTH_ID'];
		
		# - ###
		
		
		# - ###
	}
	
	
	
	# - ### ### ###
    #

    

	
	# - ### ### ###

    
    public $TEXT_EDGES = [];
    public function generateLogEdges()
    {
        $char = '=';
        $lenReport  = 60; # Длина краев у отчета

        # NOTE: На локалке будет в 2 раза меньше строк. В продакшене как надо.
        $cntRealEmptyRows = 6; # Пустых строк между отчетами
        if( env('APP_DEBUG') === true ) $cntRealEmptyRows *= 2; # Решение для локалки

        $textReportBeg = ' Main report - BEGIN '; # !!! Одинаковой длины у парных
        $textReportEnd = ' Main report -  END  ';

        $this->TEXT_EDGES['BEG']  = str_pad('',$lenReport-2,$char).PHP_EOL;
        $this->TEXT_EDGES['BEG'] .= self::generateTextWithEdges($char,$textReportBeg,$lenReport);
        $this->TEXT_EDGES['BEG'] .= PHP_EOL;


        $this->TEXT_EDGES['END']  = PHP_EOL;
        $this->TEXT_EDGES['END'] .= self::generateTextWithEdges($char,$textReportEnd,$lenReport);
        $this->TEXT_EDGES['END'] .= PHP_EOL.str_pad('',$lenReport-2,$char);
        $this->TEXT_EDGES['END'] .= str_pad('', $cntRealEmptyRows, PHP_EOL);
    }
	public static function generateTextWithEdges($char, $text, $len)
	{
		$textLen = strlen($text);
		$edgeLen = floor(($len - $textLen) / 2)-1;
		
		$edgeText = str_pad('', $edgeLen,$char);
		$final = $edgeText . $text . $edgeText;
		
		# Нечетное
		if($textLen % 2 != 0)
			$final .= $char;
		
		return $final;
	}


    # - ### ### ###

    
	
	
    # - ### ### ###


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
