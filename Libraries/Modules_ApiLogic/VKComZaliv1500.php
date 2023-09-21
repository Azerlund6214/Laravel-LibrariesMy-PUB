<?php

namespace LibMy;

# Из Laravel

# Библиотеки

# Модели

/**
 * Массовый залив отложки группы. 1500 постов на 1 год. Данные для залив уже есть в жсоне.
 */ # ДатаВремя создания: Перепись в отд класс 170923
class VKComZaliv1500
{
	# - ### ### ###
	
	public function __construct() {    }
	public function __destruct()  {    }
	
	# - ### ### ###
	
	public $VK;
	
	public $pathDate;
	public $pathCont;
	
	public $picsCount;
	public $GROUP_KEY; # Мой код группы
	public $GROUP_ID;
	
	public $POST_DTmsk_t_key;
	public $POST_DTmsk_t_val;
	public $POST_TextBasic;
	public $POST_PicsArr;
	
	# - ### ### ###
    
    
    # - ### ### ###
    #   NOTE:
	
	public static function MAIN_MANUAL()
	{
		$ME = new self();
		$ME->VK = apiVkCom::InitUniv();
		
		dump($_GET,'Dump от случ',__FUNCTION__); return;
		
		$ME->setSettings_Manual();
		
		# - ###
		# - ### ### ###
		# - ###
		
		$lastSuccDate = 'NONE';
		foreach(range(1,$ME->LIMIT) as $i)
		{
			#EasyFront::echoTag_HRv2_Gradient();
			
			$ME->setSettings_FromJson_DateNext();
			$ME->setSettings_FromJson_PicsArr();
			
			# - ###
			
			if($ME->dumpDD)
			{
				dump(["I={$i}" , "key = {$ME->POST_DTmsk_t_key}" , $ME->POST_DTmsk_t_val , $ME->POST_PicsArr , $ME->POST_TextBasic , 'Отправляю' ]);
				dd(__LINE__,__METHOD__,$ME->GROUP_ID,$ME->POST_TextBasic,$ME->POST_PicsArr,$ME->POST_DTmsk_t_val);
			}
			
			# - ###
			
			$RES = $ME->VK->apiWallPost_MakePost($ME->GROUP_ID,$ME->POST_TextBasic
				,$ME->POST_PicsArr,$ME->POST_DTmsk_t_val);
			
			if( ! $RES['IS_SUCCESS'] )
			{
				# "RESP_JSON" => array:3 [   "error_code" => 214   "error_msg" => "Access to adding post denied: a post is already scheduled for this time"
				
				if( $RES['IS_CAPTCHA'] )
				{
					$ME->VK->captcha_echoSelfSendForm($RES);
					
					dump('Ласт успешный пост'.$lastSuccDate);
					dd( $RES,$RES['RESP_JSON']);
				}
				
				dump('Ласт успешный пост'.$lastSuccDate);
				dump($RES );
				return;
			}
			
			
			#dd($RES);
			
			$resUrl = "https://vk.com/wall{$ME->GROUP_ID}_{$RES['RESP_JSON']}";
			$ii = $i;  if($ii<=9) $ii = '0'.$ii;
			EasyFront::echoTag_A_Button($resUrl,true,"{$resUrl} (I={$ii})");
			
			/*return [  'IS_ERROR' => false,  'IS_SUCCESS' => true,   'IS_CAPTCHA' => false,
						'RESP_CURL' => $RES,
						'RESP_JSON' => $RES['ANSWER_JSON']['response']['post_id'],   ]; # */
			
			$lastSuccDate = $ME->POST_DTmsk_t_val;
			FileJsoner::action_deleteKey($ME->pathDate,$ME->POST_DTmsk_t_key);
			#dump('Дата удалена - '.$ME->POST_DTmsk_t_key);
			
			#dd(123);
			
			Sleeper::sleeper($ME->TIME_SLEEP,'Между постами',true);
		}
		
		dump('Ласт успешный пост'.$lastSuccDate);
		dump('Цикл кончился по лимиту');
		
	}
	
	
	public $LIMIT;
	public $dumpDD;
	public $TIME_SLEEP;
	
	public function setSettings_Manual(  )
	{
		# - ###
		
		$buf = ['.json'  , '.json'];
		$buf = ['KOVER = FINAL-CONTENT = PIC = 176шт.json' , 'KOVER = FINAL-DATES = 4 = 8-12-16-20 = 2023-2024-2025 =#.json'];
		$buf = [ 'UGOL = FINAL-CONTENT = PIC = 591шт.json' ,  'UGOL = FINAL-DATES = 4 = 8-12-16-20 = 2023-2024-2025 =#.json'];
		$buf = ['PANEL = FINAL-CONTENT = PIC = 704шт.json' , 'PANEL = FINAL-DATES = 4 = 8-12-16-20 = 2023-2024-2025 =#.json'];
		$buf = ['GENSH = FINAL-CONTENT = PIC = АртыХор + МемыХорНорм 492шт.json'  , 'GENSH = FINAL-DATES = 4 = 8-12-16-20 = 2023-2024-2025 =#.json'];
		
		$pathFolder = 'D:\\ГЛОР = API Группы (Новое)\\MASS\\';
		$this->pathCont = $pathFolder.$buf[0];  $this->pathDate = $pathFolder.$buf[1];
		
		$this->GROUP_KEY = 'GENSH'; # AWC MIA MLP   KOVER   SPACE      MIA_P
		$this->GROUP_ID = authTokenStrings::$VK_GROUPS[$this->GROUP_KEY]['ID'];
		
		# - ###
		
		$this->LIMIT = 1;
		$this->LIMIT = 1000;
		
		$this->picsCount = 4;
		$this->POST_TextBasic = '';
		
		$this->dumpDD = true;
		$this->dumpDD = false;
		
		# - ###
		
		#$this->VK->setCaptchaAnswer('',''); # NOTE
		
		# - ###
		
		$this->TIME_SLEEP = $this->VK->makePosts_sleepSec;
		
		
		dump([
			'1500шт' => '~'.floor((1500*$this->TIME_SLEEP)/60).'мин',
			'1000шт' => '~'.floor((1000*$this->TIME_SLEEP)/60).'мин',
			'500шт'  => '~'.floor(( 500*$this->TIME_SLEEP)/60).'мин',
			'300шт'  => '~'.floor(( 300*$this->TIME_SLEEP)/60).'мин',
			'100шт'  => '~'.floor(( 100*$this->TIME_SLEEP)/60).'мин',
		]);
		
		
		# - ###
		# - ### ### ###
		# - ###
		
		# - ###
	}
	
	public function setSettings_FromJson_DateNext(  )
	{
		if( ! file_exists($this->pathDate) )
			dd(__FILE__,__FUNCTION__,$this->pathDate,$this,'Файл не существует');
		
		$dateElem = FileJsoner::getElem_First($this->pathDate);
		
		if( $dateElem === 'EMPTY_JSON' )
			dd('Жсон дат кончился');
		
		$this->POST_DTmsk_t_key = $dateElem['KEY'];
		$this->POST_DTmsk_t_val = $dateElem['VAL'];
	}
	public function setSettings_FromJson_PicsArr (  )
	{
		if( ! file_exists($this->pathCont) )
			dd(__FILE__,__FUNCTION__,$this->pathCont,$this,'Файл не существует');
		
		if( $this->picsCount <= 0 ) return;
		
		$this->POST_PicsArr = FileJsoner::getValueS_Rand($this->pathCont,$this->picsCount);
	}
	
	
	# - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####   ʕ•ᴥ•ʔ  \(★ω★)/  (^=◕ᴥ◕=^)   ####
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
