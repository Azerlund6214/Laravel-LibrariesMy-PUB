<?php

namespace LibMy;

# Из Laravel

# Библиотеки

# Модели
use Illuminate\Support\Facades\Log;

/**
 *
 */ # ДатаВремя создания: Концепция-180923 / Реализация-
class VKComChatBot
{
    # - ### ### ###
    
    public function __construct() {    }
    public function __destruct()  {  $this->makeAllLogs_Main( );  }
    
    # - ### ### ###
	# NOTE: Эталоны
	
	public $REQ;
	public $EVENT_FULL;
	public $EVENT_MSG = [
		'FILLED' => false,
		'RAW_FULL' => '',
		'TEXT_RAW' => '',
		'TEXT_PREP' => '',
		'USER_ID' => '',
		'CHAT_ID' => '',
	];
	
    # - ### ### ###
	
    
    public function verifySecret(  ):bool
    {
	    return ((isset($ME->EVENT_FULL['secret']))
		    &&
		    ( $ME->EVENT_FULL['secret'] === authTokenStrings::$VK_CHATBOT_SECRET ));
    }
	
	public $VK_SEND_RES = [];
	public function vkApi_SendMessage()
	{
		$VK = apiVkCom::InitUniv();
		
		$this->VK_SEND_RES = $VK->apiMessagesSend_SendOneForGroupBot($this->EVENT_MSG['CHAT_ID'],
			$this->DECISION_CONTENT['TEXT'],$this->DECISION_CONTENT['ATT']);
	}
    
	public $RULES_CHATS = [ ];
	public function setRulesArr_Chats(  )
	{
		$this->RULES_CHATS = include public_path('VSEBOT=RULES_CHATS.php');
	}
 
	public $RULES_CONTENT = [ ];
	public function setRulesArr_Content(  )
	{
		$this->RULES_CONTENT = include public_path('VSEBOT=RULES_CONTENT.php');
	}
	
	public $ANSWERED_TEXT = '';
	public function sendAnswer( $text )
	{
		$this->ANSWERED_TEXT = $text;
		
		echo $text;
	}
	
	# - ### ### ###
	
    # WORK
	public function makeAllLogs_Main( )
	{
		$eolBig = str_repeat(PHP_EOL,6);
		$eolSml = str_repeat(PHP_EOL,3);
		
		
		$this->EVENT_FULL['object']['client_info'] = '### ERASED ###';
		
		$TEXT  = $eolBig;
		$TEXT .= '#########################################'.PHP_EOL;
		$TEXT .= '###### RAW ######'.PHP_EOL . json_encode($this->EVENT_FULL,JSON_PRETTY_PRINT).$eolSml;
		$TEXT .= '###### EVENT_MSG ######'.PHP_EOL . json_encode($this->EVENT_MSG,JSON_PRETTY_PRINT).$eolSml;
		$TEXT .= '###### DECISION_CHAT ######'.PHP_EOL . json_encode($this->DECISION_CHAT,JSON_PRETTY_PRINT).$eolSml;
		$TEXT .= '###### DECISION_CONTENT ######'.PHP_EOL . json_encode($this->DECISION_CONTENT,JSON_PRETTY_PRINT);
		$TEXT .= ''.PHP_EOL . implode(' --> ',[$this->EVENT_MSG['TEXT_RAW'],$this->EVENT_MSG['TEXT_PREP']]).$eolSml;
		
		if( ! empty($this->VK_SEND_RES) )
		{   # Была попытка отправки
			if( $this->VK_SEND_RES['IS_ERROR'] )
			{
				$targetFile = 'CHBOT-WITH-SEND=ERROR-ANY.log';
				$TEXT .= '###### VK_SEND_RES FULL ######'.PHP_EOL . json_encode($this->VK_SEND_RES,JSON_PRETTY_PRINT).$eolSml;
				
			}
			else
			{
				$targetFile = 'CHBOT-WITH-SEND=GOOD.log';
				$TEXT .= '###### VK_SEND_RES Ответ ######'.PHP_EOL . json_encode($this->VK_SEND_RES['RESP_JSON'],JSON_PRETTY_PRINT).$eolSml;
			}
		}
		else
		{
			$targetFile = 'CHBOT-NO-SEND=ALL.log';
		}
		
		
		Filer::writeToEnd_NewLine(Patcher::getPath_LaraStorageLogs().$targetFile , $TEXT);
	}
	public function makeAllLogs_BadSecret( )
	{
		$eolBig = str_repeat(PHP_EOL,3);
		$eolSml = str_repeat(PHP_EOL,3);
		
		$this->EVENT_FULL['object']['client_info'] = '### ERASED ###';
		
		$TEXT  = $eolBig;
		$TEXT .= '#########################################';
		$TEXT .= json_encode($this->EVENT_FULL,JSON_PRETTY_PRINT).$eolSml;
		
		Filer::writeToEnd_NewLine(Patcher::getPath_LaraStorageLogs().'CHBOT-SECRET-BAD.log' , $TEXT);
	}
	
	# - ### ### ###
	
	public static function HANDLE_CALLBACK(  )
	{
		# - ###
		$ME = new self();
		$ME->REQ = Requester::getAllInfo();
		$ME->EVENT_FULL = $ME->REQ['PARAMS']['ARR_RAW']; # ... уже раскодированный
		
		
		if( $ME->verifySecret() )
		{
			$ME->makeAllLogs_BadSecret();
			$ME->sendAnswer('ok'); return;
		}
		
		# - ###
		# NOTE: Switch тут не очень удобен.
		
		
		if( $ME->EVENT_FULL['type'] === 'confirmation' )
		{   # "{"group_id":123123,"event_id":"123123","v":"5.131","type":"confirmation","secret":"13123"}"
			$ME->sendAnswer('confirmation'); return;
		}
		
		if( $ME->EVENT_FULL['type'] === 'message_new' )
		{
			# - ###
			$ME->EVENT_MSG['RAW_FULL'] = $ME->EVENT_FULL['object']['message'];
			$ME->EVENT_MSG['TEXT_RAW'] = $ME->EVENT_FULL['object']['message']['text'];
			$ME->EVENT_MSG['USER_ID']  = $ME->EVENT_FULL['object']['message']['from_id'];
			$ME->EVENT_MSG['CHAT_ID']  = $ME->EVENT_FULL['object']['message']['peer_id'];
			$ME->EVENT_MSG['FILLED'] = true;
			
			$ME->setRulesArr_Chats();
			$ME->setRulesArr_Content();
			# - ###
			
			# Проверка разрешенности чата. Запрещен=завершение.
			$ME->makeDecision_Chat();
			if( ! $ME->DECISION_CHAT['ALLOWED'] )
			{
				$ME->sendAnswer('ok'); return; # Чат запрещен, в игноре.
			}
			
			# Проверка необходимости действия. Не нужно=выход.
			$ME->makeDecision_Content();
			if( $ME->DECISION_CONTENT['SEND'] )
			{
				$ME->vkApi_SendMessage(  );
			}
			
			# - ###
			$ME->sendAnswer('ok'); return;
			# - ###
		}
		
		
		# - ###
		$ME->sendAnswer('Unsupported event');
		# - ###
	}
	
	
	public $DECISION_CHAT = [ ];
	public function makeDecision_Chat( ):void
	{
		# - ###
		
		$RULES_CHATS = $this->RULES_CHATS;
		
		if( $RULES_CHATS['WHITELIST_MODE'] )
		{
			if( in_array($this->EVENT_MSG['CHAT_ID'],$RULES_CHATS['WHITELIST_IDS']) )
			{
				# Только для моей же тестовой группы
				$this->DECISION_CHAT = [ 'ALLOWED' => true, 'DESCR' => 'WHITELIST - Есть в списке', ];
				return;
			}
			else
			{
				$this->DECISION_CHAT = [ 'ALLOWED' => false, 'DESCR' => 'WHITELIST - Нет в списке', ];
				return;
			}
		}
		
		if( $RULES_CHATS['BLACKLIST_MODE'] )
		{
			if( ! in_array($this->EVENT_MSG['CHAT_ID'],$RULES_CHATS['BLACKLIST_IDS']) )
			{
				$this->DECISION_CHAT = [ 'ALLOWED' => true, 'DESCR' => 'BLACKLIST - Нет в списке', ];
				return;
			}
			else
			{
				$this->DECISION_CHAT = [ 'ALLOWED' => false, 'DESCR' => 'BLACKLIST - Есть в списке', ];
				return;
			}
		}
		# - ###
	}
	
	public $DECISION_CONTENT = [ ];
	public function makeDecision_Content( ):void
	{
		$FIN = [
			'DESCR' => 'Дефолт',
			
			'SEND' => false,
			'TEXT' => '',
			'ATT' => [],
		];
		# - ###
		$MSG_Text = $this->EVENT_MSG['TEXT_RAW'];
		
		$MSG_Text = trim($MSG_Text);
		$MSG_Text = strtolower($MSG_Text); # Только для англ
		$MSG_Text = mb_strtolower($MSG_Text, 'utf-8'); # Унив
		
		$this->EVENT_MSG['TEXT_PREP'] = $MSG_Text;
		# - ###
		
		$RULES_CONTENT = $this->RULES_CONTENT;
		
		# - ###
		
		# Кастом обработка команд
		if( $MSG_Text === 'всебот команды' )
		{
			$FIN['SEND'] = true; $FIN['DESCR'] = 'VSEBOT = Commands => '.$MSG_Text;
			$FIN['TEXT'] = '*** Команды бота ***'.PHP_EOL.PHP_EOL;
			foreach( $RULES_CONTENT as $group=> $R )
			{
				$FIN['TEXT'] .= 'Группа: '.$group.' ('.count($R['PICS_URLS']).' картинок)'.PHP_EOL;
				
				foreach( $R as $ruleGroup=>$arr )
				{
					if( str_contains($ruleGroup,'WORDS_') && (! empty($arr)) )
					{
						if( $ruleGroup === 'WORDS_SOLO'  ) $FIN['TEXT'] .= 'Фразы: ' .implode(' | ',$arr).PHP_EOL;
						if( $ruleGroup === 'WORDS_BEGIN' ) $FIN['TEXT'] .= 'Начало текста: '.implode('% | ',$arr).'%'.PHP_EOL;
						if( $ruleGroup === 'WORDS_PART'  ) $FIN['TEXT'] .= 'Часть текста: %' .implode('% | %',$arr).'%'.PHP_EOL;
					}
				}
				$FIN['TEXT'] .= PHP_EOL.PHP_EOL;
			}
			$this->DECISION_CONTENT = $FIN; return;
		}
		
		
		
		# - ###
		foreach( $RULES_CONTENT as $group=> $R )
		{
			# - ### Одним словом
			if( in_array($MSG_Text ,$R['WORDS_SOLO']) )
			{
				$FIN['SEND'] = true; $FIN['DESCR'] = 'RULE-GOOD = WORDS_SOLO => '.$MSG_Text;
			}
			
			# - ### В начале текста
			foreach( $R['WORDS_BEGIN'] as $targTextBegin ){
				if( str_starts_with($MSG_Text , $targTextBegin) )
				{
					$FIN['SEND'] = true; $FIN['DESCR'] = 'RULE-GOOD = WORDS_BEGIN => '.$targTextBegin;
					break;
				}
			}
			
			# - ### В любом месте
			foreach( $R['WORDS_PART'] as $targTextPart ){
				if( str_contains($MSG_Text , $targTextPart) )
				{
					$FIN['SEND'] = true; $FIN['DESCR'] = 'RULE-GOOD = WORDS_PART => '.$targTextPart;
					break;
				}
			}
			
			# - ### Если правило совпало и нужны действия.
			
			if( $FIN['SEND'] )
			{
				
				$FIN['ATT'] = [  $R['PICS_URLS'][array_rand($R['PICS_URLS'],1)]  ];
				#$R['PICS_CNT']
				
				$this->DECISION_CONTENT = $FIN; return;
			}
			
			# - ###
		}
		# - ###
		$FIN['DESCR'] = 'RULES-NOONE';
		#$FIN['RULES'] = $RULES_CONTENT;
		$this->DECISION_CONTENT = $FIN; return;
	}
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####   ʕ•ᴥ•ʔ  \(★ω★)/  (^=◕ᴥ◕=^)   ####
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
