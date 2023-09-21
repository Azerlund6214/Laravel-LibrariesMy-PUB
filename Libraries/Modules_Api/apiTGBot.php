<?php

namespace LibMy;

use Illuminate\Support\Facades\Log;
use App\ProjectDefine;


/* Инструкция:

    1) Заходим на @BotFather
    /newbot
    Вводим публичное имя(любое)
    Вводим адрес бота (уникальный)
    Сохраняем выданный ключ формата "123:abcdef"

    Теперь ищем созданного бота по его адресу и жмем "Старт".

    2) Идем в бота @myidbot
    /getid
    Записываем его. Это id текущей учетки телеги.(на неё будем слать)

    3) Вставляем полученные данные в соответствующие методы класса.
    (либо пишем значения в бд и получаем в методах)

    Теперь можно вызывать метод отправки и будет приходить сообщение.
    Вставляем вызов метода отправки в нужные места в коде. Все.
*/

/**
 *  Отправка сообщений в телеграм бот.
 *
 *  ДатаВремя создания: Придумано 060221 0342 / Написано 060221 1007 / Переписано с нуля 290921 фулл
 *      Переписано с нуля 2 раз - 160123       Переписано с нуля 3 раз - 040523
 */
class apiTGBot
{
    # - ### ### ###
    
    public function __construct(  ) {  }
    public function __destruct()  {    }
    
    # - ### ### ###

	public $SMILES = [
		'DIAMOND'  => "\u{1F48E}",
		'KLUBNIKA' => "\u{1F353}",
		'KLEVER'   => "\u{1F340}",
		'KLEN'   => "\u{1F341}",
		'ZAMOK'   => "\u{1F512}",
		'KEY'   => "\u{1F511}",
		'SOTA'   => "\u{1F4F6}", # ПалкиСвязи
		#''   => "\u{}",
	];
	# NOTE: На довстройку:  1F551   1F504  #
	
	
    public $OPT_KEY;
    public $OPT_ID;
    #public $OPT_MODE;
    
    public $OPT_MSG;
	
	
    public $RESULT;
    
    # https://core.telegram.org/bots/api
    
    # - ### ### ###
    #   NOTE:
	
	public function setAuth_Key_Id_fromStr( string $key_id ){ $arr = explode('|',$key_id); $this->OPT_KEY = $arr[0]; $this->OPT_ID = $arr[1]; }
	public function setAuth_Key_Id( string $key , string $id ){ $this->OPT_KEY = $key; $this->OPT_ID = $id; }
	public function setAuth_BotKey( string $key )         { $this->OPT_KEY = $key; }
	public function setAuth_IdOne( string $tgUserId ){ $this->OPT_ID = $tgUserId;  }
	
	public function setAuth__ENV_DEV(  )
	{
		$arr = explode('|',env('TG_AUTH__DEV_TEST'));
		$this->setAuth_Key_Id($arr[0],$arr[1]);
	}
	public function setAuth__ENV_ERR_HANDLER(  )
	{
		$arr = explode('|',env('ERR_HANDLER_TG_AUTH'));
		$this->setAuth_Key_Id($arr[0],$arr[1]);
	}
	public function setAuth__PD(  )
	{
		$this->setAuth_BotKey   (ProjectDefine::getValue('TELEGRAM_KEY'));
		$this->setAuth_IdOne(ProjectDefine::getValue('TELEGRAM_ID'));
	}
	
	# - ### ### ###
	
	public function setMessage_RawString( string $msgOneStr )
	{
		$this->OPT_MSG = $msgOneStr;
	}
	
	# - ### ### ###
	
	public static function getEntity_Bold( $text ){   return "<b>{$text}</b>";   }
	public static function getEntity_Cursive( $text ){   return "<i>{$text}</i>";   }
	public static function getEntity_ForCopy( $text ){   return "<code>{$text}</code>";   }
	public static function getEntity_CrossOut( $text ){   return "<del>{$text}</del>";   }
	public static function getEntity_Underline( $text ){   return "<u>{$text}</u>";   }
	public static function getEntity_SPOILERED( $text ){   return "<tg-spoiler>{$text}</tg-spoiler>"; /* ТожеРобит = <span class="tg-spoiler"> text123123 </span> */  }
	public static function getEntity_URL( $URL , $text ){   return "<a href='{$URL}'>{$text}</a>";   }
	
    # - ### ### ###
    #   NOTE:
	
	public function execGetResult(  )
	{
		# - ###
		
		$URL = "https://api.telegram.org/bot{$this->OPT_KEY}/sendMessage";
		
		$PARAMS = [
			'chat_id'  => $this->OPT_ID,
			'text'      => $this->OPT_MSG,
			'parse_mode' => 'html', # Что бы работало форматирование через html-теги
		];
		
		# - ###
		
		$RES = RequestCURL::GET($URL,$PARAMS);
		
		$FIN = [];
		$FIN['INFO_CURL'] = $RES;
		$FIN['INFO_ANSWER'] = $RES['ANSWER_JSON'];
		$FIN['TIME_MS'] = $RES['INFO']['TIME']['total_time'];
		
		
		if( $RES['IS_ERROR'] )
		{
			$ARR = [  '',   '*** АХТУНГ!!! - вылет курла при отправке телеги ***',
				'CURL_JSON_FULL = '.json_encode($FIN),   '', '', '',   ];
			Log::channel('tg_errors')->info( implode(PHP_EOL , $ARR) );
			
			dd('АХТУНГ!!! - вылет курла при отправке телеги, см. лог файл');
		}
		
		
		# NOTE: Дальше считаю что ответ телеги есть.
		
		
		$this->RESULT = $FIN;
		return $FIN;
	}
	
	public function parseAnswer()
	{
		$FIN = $this->RESULT;
		
		$A = $FIN['INFO_ANSWER'];
		
		if( $A['ok'] === true )
		{
			$FIN['SUCCESS'] = true;
			$FIN['ANSWER_SENDED_TEXT'] = [ $A['result']['text'] ];
		}
		else
		{
			$FIN['SUCCESS'] = false;
			$FIN['ERROR'] = [ ];
			
			# TODO:  Нет инета
			# TODO:  Недопустимые символы
			# TODO:  Слишком часто
			# OK => {"ok":true,"result":{"message_id":1077,"from":{"id":123123123,"is_bot":true,"first_name":"Bot Test","username":"name_test_bot"},"chat":{"id":12312320,"first_name":"USERNAME","username":"user3435","type":"private"},"date":1632942780,"text":"123test"}}
			# ERR Кривой получатель => {"ok":false,"error_code":400,"description":"Bad Request: chat not found"}
			# ERR Кривой токен => {"ok":false,"error_code":401,"description":"Unauthorized"}
			
			
			$FIN['ERROR']['ERR_CODE'] = '0';
			$FIN['ERROR']['ERR_CODE_MY'] = '-';
			$FIN['ERROR']['ERR_DESC'] = '...';
			
			# - ###
			
			switch( $A['error_code'] ){
				case 400:
					$FIN['ERROR']['ERR_CODE'] = $A['error_code'];
					$FIN['ERROR']['ERR_DESC'] = $A['description'];
					$FIN['ERROR']['ERR_CODE_MY'] = 'BAD_CHAT';
					break;
				case 401:
					$FIN['ERROR']['ERR_CODE'] = $A['error_code'];
					$FIN['ERROR']['ERR_DESC'] = $A['description'];
					$FIN['ERROR']['ERR_CODE_MY'] = 'BAD_KEY';
					break;
				
				case 400000001:
					$FIN['ERROR']['ERR_CODE'] = $A['error_code'];
					$FIN['ERROR']['ERR_DESC'] = $A['description'];
					$FIN['ERROR']['ERR_CODE_MY'] = 'WAIT_DELAY';
					break;
				
				default:
					$FIN['ERROR']['ERR_CODE_MY'] = 'UNDEFINED';
			}
			
		}
		
		$this->RESULT = $FIN;
		
		return $FIN['SUCCESS'];
	}
	
	# Лог в файл если ошибка отправки
	public function makeLogIfNeed()
	{
		# - ###
		
		if( $this->RESULT['SUCCESS'] )
			return;
		
		# - ###
		
		$R = $this->RESULT;
		
		$ARR = [
			'',
			'*** Ошибка при отправке телеги ***',
			"OPT_BOT_ID = ".explode(':',$this->OPT_ID)[0],
			"OPT_ID = {$this->OPT_ID}",
			"OPT_MSG RAW = {$this->OPT_MSG}",
			"OPT_MSG BASE64 = ".base64_encode($this->OPT_MSG),
			"ERR_CODE    = {$R['ERROR']['ERR_CODE']}",
			"ERR_CODE_MY = {$R['ERROR']['ERR_CODE_MY']}",
			"ERR_DESC    = {$R['ERROR']['ERR_DESC']}",
			'ANSWER_JSON = '.json_encode($R['INFO_ANSWER']),
			'INFO_CURL_JSON = '.json_encode($R['INFO_CURL']),
			'', '', '',
		];
		
		$log = implode(PHP_EOL , $ARR);
		
		Log::channel('tg_errors')->info($log);
		
		#dd($this,$log);
	}
	
	
	#public function sendToReserveIfNeed()
	#{
		# CONCEPT: енв запасной бот. - ключ ид + вклюенность    возможно добавить метод-опцию для включения
	#}
	
	# - ### ### ###
	
	
	public static function sendFast( $oneStr , $key_and_id)
	{
		if( env( 'TG_PERMANENT_SEND_DISABLE' ) )
			return 'TG_PERMANENT_SEND_DISABLE';
		
		$TG = new self();
		$TG->setAuth_Key_Id_fromStr($key_and_id);
		
		if( env('TG_PERMANENT_SEND_ALL_TO_DEVBOT') )
			$TG->setAuth__ENV_DEV();
		
		$TG->setMessage_RawString($oneStr);
		$TG->execGetResult();
		$TG->parseAnswer();
		$TG->makeLogIfNeed();
		return $TG->RESULT;
	}
	
	
	# CONCEPT: метод гет апдейтс ласт
	
    # - ### ### ###
    #   NOTE:
	
	
	# - ### ### ###
	#   NOTE:
	
	/*  Альтернатива. По сути одно и то же.  НЕ УДАЛЯТЬ
		$token = ""; #
		$data = [
			'text' => 'your message here',
			'chat_id' => '-123123', #
		];
		$result = file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );
		dd($result, json_decode($result, true));
	*/
	
	# - ### ### ###
	#   NOTE:
	
	
    # - ### ### ###
    #   NOTE:
	
	# TESTING
	public static function debug()
	{
		$key_id = env('TG_AUTH__DEV_TEST');
		self::sendFast("====== ====== ======",$key_id);
		
		$msg = array(
			'123test123',
			'МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_МногаБукаф_',
			'aaa'.PHP_EOL.'bbb'.PHP_EOL.'ccc',
			"aaa\nbbb\nccc", # Двойные кавычки
			'aaa\nbbb\nccc', #
			'url test: https://laravel.ru/docs/v3/database/eloquent',
			'https://laravel.ru/docs/v3/database/eloquent',
			'<b>BOLDbold</b>__<i>Курсив</i>',
			'<pre>test123</pre>__123 <i>How are you?</i> 123',
		); # Все норм отправляются.
		
		$resArr = array();
		
		foreach($msg as $one)
			$resArr []= self::sendFast($one,$key_id);
		
		dd($resArr);
	}
	

    # - ### ### ###
    #   NOTE:

	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
