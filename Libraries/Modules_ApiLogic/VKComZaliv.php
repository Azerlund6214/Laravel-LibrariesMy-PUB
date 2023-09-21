<?php

namespace LibMy;


/** Важный класс для работы через VK-API.
 * Предназначен для полу-автоматической заливки постов в группы вк. С ручным контролем за процессом.
 * В отличие от массового залива тут каждый пост делается индивидуально, по 1 за раз, но в 1 клик вышкой.
 * Даты и контент выставляются автоматически. При необходимости редактируется текст.
 *
 * Постоянно используется на практике.
 * Технически можно ужать до 1 простого метода, но переделан под класс для простоты доработки.
 */
class VKComZaliv
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
	public $POST_PicsArr; # ссылкаВк => ссылка.jpg
	
	# - ### ### ###
    
    public static function MAIN_MANUAL()
    {
        $ME = new self();
	    $ME->VK = apiVkCom::InitUniv();
		
	    #dump($_GET,'Dump от случ',__FUNCTION__); return;
	    /* #  # */
	    
	    
	    $ME->setSettings_Manual();
	    $ME->setSettings_FromJson_DateNext();
	    
	    if( isset($_GET['WITH_GET']) )
	        $ME->ACTION_MAKE_POST_DD();
	    
	    $ME->setSettings_FromJson_PicsArrAndUrl(); # Внутри сразу пробив
	    
	    # - ### ### ###
	    
	    dump($ME);
		
	    EasyFront::echoTag_HrROW_PreDef_GradDEV();
		
	    if( isset($_GET['resUrl']) )
		    EasyFront::echoTag_A_Button($_GET['resUrl']);
		
	    EasyFront::echoTag_HrROW_PreDef_GradDEV();
		
	    
	    echo '<br><br><br>';
	    $tagsArr = [
		    '#Nanachi','#Mitty','#Nanachi #Mitty','#Nanachi #Reg','br',
		    '#Riko','#Reg','#Riko #Reg','#Nanachi #Riko #Reg','br',
		    '#Ozen','#Maruruk','#Ozen #Maruruk','#Ozen #Lyza','br',
		    '#Bondrewd','#Prushka','#Bondrewd #Prushka','br',
		    '#Faputa','#Faputa #Reg','br',
		    '#Lyza','#Torka','#Lyza #Torka'
	    ];
	    if($ME->GROUP_KEY==='MIA')
		    foreach( $tagsArr as $one )
			    if($one==='br')
			    	echo '<br>';   else   EasyFront::echoTag_Button_Copy($one);
	    
		
	    echo '<br><br><br>';
	    echo '<form target="_self" method="GET" action="/test">';
	    EasyFront::echoTagForm_Input_Text('WITH_GET','Any',50,false,true);
	    EasyFront::echoTagForm_Input_Text('12312312','Не юзается = '.$ME->POST_DTmsk_t_val,50,2);
	    EasyFront::echoTagForm_Input_TextArea('TEXT',$ME->POST_TextBasic,3,50,2);
	    EasyFront::echoTagForm_Input_Text('PICS_JSON',implode('|',array_keys($ME->POST_PicsArr)),120);
	    EasyFront::echoTagForm_BtnSend(); EasyFront::echoTagForm_BtnSend(); EasyFront::echoTagForm_BtnSend();
	    echo '</form>';
		
		
	    echo '<br>';
	    foreach( $ME->POST_PicsArr as $urlVk=>$urlPic )
		    EasyFront::echoTag_IMG($urlPic,'',300); # 600 1000
		
		
	    echo '<br><br><br>';
	    #dd(json_encode($picsUrlsArr));
	    foreach( $ME->POST_PicsArr as $urlVk=>$urlPic )
	    {
		    EasyFront::echoTag_A_Button($urlVk); # Фреймы не катят
	    }
	    EasyFront::echoTag_Button_Reload();
	
	
	
	    EasyFront::echoTag_HrROW_PreDef_GradDEV();
	    # - ###
    }
    
    
    
	public function setSettings_Manual(  )
	{
		# - ###
		
		$buf = ['.json'  , '.json'];
		
		$buf = ['MIA_MAIN = ART = FINAL-CONTENT = PIC = Арты Хор 1337шт.json'  , 'MIA_MAIN = DONUT = FINAL-DATES = 1 = 3 = 2023-2024-2025 =#.json'];
		
		$pathFolder = 'D:\\ГЛОР = API Группы (Новое)\\';
		$this->pathCont = $pathFolder.$buf[0];  $this->pathDate = $pathFolder.$buf[1];
		
		# - ###
		
		$this->picsCount = 9;
		
		$this->GROUP_KEY = 'AWC'; # AWC MIA MLP
		$this->GROUP_ID = authTokenStrings::$VK_GROUPS[$this->GROUP_KEY]['ID'];
		
		#$this->VK->setPostIsDonut_Day1(); # NOTE
		#$this->VK->setCaptchaAnswer('sid','key'); # NOTE
		
		# - ###
		
		$this->POST_TextBasic = last( [
			'#Ozen #Maruruk',
			'#Ozen',
			'#Nanachi',
			'#Memes',
			'#Comic',
			$this->VK->postDonutText,
			'',
		] );
		
		# - ###
	}
    
    # - ### ### ###
    #   NOTE:
	
	
	# IMPORTANT
	public function ACTION_MAKE_POST_DD()
	{
		$TEXT = $_GET['TEXT'];
		$PICS_ARR = explode('|',$_GET['PICS_JSON']);
		
		
		#dd($_GET,$PICS_ARR,'DD от случ');
		dump($_GET);
		
		$RES = $this->VK->apiWallPost_MakePost($this->GROUP_ID , $TEXT , $PICS_ARR , $this->POST_DTmsk_t_val);
		
		if( ! $RES['IS_SUCCESS'] )
		{
			# "RESP_JSON" => array:3 [   "error_code" => 214   "error_msg" => "Access to adding post denied: a post is already scheduled for this time"
			
			if( $RES['IS_CAPTCHA'] )
			{
				$this->VK->captcha_echoSelfSendForm($RES);
				
				dd( $RES);
			}
			
			dd($RES );
		}
		
		
		$resUrl = "https://vk.com/wall{$this->GROUP_ID}_{$RES['RESP_JSON']}";
		EasyFront::echoTag_A_Button($resUrl,true,"{$resUrl}");
		
		FileJsoner::action_deleteKey($this->pathDate,$this->POST_DTmsk_t_key);
		dump('Дата удалена - '.$this->POST_DTmsk_t_val);
		
		dump($RES);
		
		
		EasyFront::echoTag_A_Button('/test',false);
		EasyFront::echoTag_A_Button('/test',false);
		EasyFront::echoTag_A_Button('/test',false);
		
		EasyFrontJS::echoJS_Redirect('/test?resUrl='.($resUrl));
		dd('dd');
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
	
	public function setSettings_FromJson_PicsArrAndUrl(  )
	{
		if( ! file_exists($this->pathCont) )
			dd(__FILE__,__FUNCTION__,$this->pathCont,$this,'Файл не существует');
		
		if( $this->picsCount <= 0 ) return;
		
		$vkPicsArr = FileJsoner::getValueS_Rand($this->pathCont,$this->picsCount);
		
		$vkPicsStr = [];
		foreach( $vkPicsArr as $picUrlVk )
			$vkPicsStr []= str_replace('https://vk.com/photo' , '' ,$picUrlVk);
		$vkPicsStr = implode(',',$vkPicsStr);
		
		# Запрос сразу всех пикч за раз.
		$RESP = $this->VK->apiPhotoGet_InfoWithSizesArr( $vkPicsStr );
		if($RESP['IS_ERROR']) dd($this,__FUNCTION__,$RESP);
		
		foreach( $vkPicsArr as $i=>$picUrlVk )
		{
			$imgMaxedArr = $this->VK->parsePictureSizesArr_GetMaxImgArr($RESP['RESP_JSON'][$i]['sizes']);
			#dump("{$imgMaxedArr['height']} x {$imgMaxedArr['width']}");
			
			$this->POST_PicsArr[$picUrlVk] = $imgMaxedArr['url'];
		}
	}
	
	
	
	
	# - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
