<?php

namespace LibMy;

use \danog;
use Throwable;

/** Огромный класс для низкоуровневой работы с TelegramCoreApi
 * Писался постепенно, поэтому структура далеко не идеальна.
 * Был в продакшене.
 * Очень "Душный" класс
 */
class apiTGCore
{
    # - ### ### ###

    private $session = ''; # Ставится перед инитом

    public $API; # \danog\MadelineProto\API

    #private $DEF_filePartsSizes = [1024, 2048, 4096, 8192, 16384, 32768, 65536, 131072, 262144, 524288];


    # - ###
    private $allowRealRequests = false; # Разрешить ли классу делать реальные запросы.

    private $config_peer; # Текущий рабочий чат
    private $config_peer_logs; # Канал куда скидывать логи
    private $config_silent  = true;
    private $config_previewIsOn = true;
    private $config_parsemode = '';

    # - ###

    private static $configMy_sendMsg_DumpOptsArr = false;
    private static $configMy_sendMsg_DumpBegEnd  = false;
    private static $configMy_botBtnClicks_DumpBegEnd  = false;
    #if( self::$configMy_botBtnClicks_DumpBegEnd ){ dump('SendMsgLL: Запрос-Отправка'); flush(); }


    # - ###

    public $DEBUG_ARR = ['I'=>1];


    private $reqInfo_Send = [];
    private $reqInfo_Error = [];
    private $reqInfo_Input = [];

    private $reqInfo_AnswerRaw = [];
    private $reqInfo_AnswerPrep = [];
    private $reqInfo_AnswerResult = []; # Может быть что угоддно


    # Метод, который сгребет все в 1 массив с ключами.     1 поле - 1 его ключ


    # - ### ### ###
    # - ###

    public function __construct() {    }
    public function __destruct()  {    }

    # - ###
    # - ### ### ###
    #   NOTE: Авторизация и подготовка к работе


    public function init()
    {
        #$s = new \danog\MadelineProto\Settings();
        #dd($s->getDefaultDcParams());  # "datacenter" => 2
        #dd($s->getDefaultDc());  # 2
        #$s->setDefaultDc(3);
        #$s->applyChanges();

        $settings = [
            #'app_info' => [ // Эти данные мы получили после регистрации приложения на https://my.telegram.org
            #    'api_id' => XXXXX,
            #    'api_hash' => XXXXXXXXXX,
            #],
            #'logger' => [ // Вывод сообщений и ошибок
            #    'logger' => 3, // выводим сообещения через echo
            #    'logger_level' => 4, // выводим только критические ошибки.
            #],
            //для доступа может потребоваться socks5 прокси
            //если прокси не требуется, то этот блок можно удалить.
            #'connection_settings' => [
            #    'all' => [
            #        'proxy' => '\SocksProxy',
            #        'proxy_extra' => [
            #            'address' => 'xxx.xxx.xxx.xxx',
            #            'port' => 1234,
            #            'username' => '',//Можно удалить если логина нет
            #            'password' => '',//Можно удалить если пароля нет
            #        ],
            #    ],
            #],
            'serialization' => [
                'serialization_interval' => 300,
                //Очищать файл сессии от некритичных данных.
                //Значительно снижает потребление памяти при интенсивном использовании, но может вызывать проблемы
            #    'cleanup_before_serialization' => true,
            ],
        ];

        $this->API = new danog\MadelineProto\API($this->session, $settings);

        #dump($this->MP->getDataCenterConnections());
        #dump($this->MP->getCdnConfig(2));
        #dump($this->MP->getSettings()); # Много
        #dump($this->MP->cleanup());
        #dump($this->MP->getDataCenterId()); # 2
        #dd('123');


        $this->API->async(false);
        $this->API->start();

        $this->onBEGIN();
    }

    public function setConfig_Session($path)
    {
        $this->session = $path;
    }
    public function setConfig_Chat($id)
    {
        $this->config_peer = $id;
    }
    public function setConfig_ChatForLogs($id)
    {
        $this->config_peer_logs = $id;
    }
    public function setConfig_PreviewIsOn($bool)
    {
        $this->config_previewIsOn = $bool;
    }
    public function setConfig_Silent($bool)
    {
        $this->config_silent = $bool;
    }
    public function setConfig_ParseMode_Markdown()
    {
        $this->config_parsemode = 'markdown';
    }
    public function setConfig_ParseMode_HTML()
    {
        $this->config_parsemode = 'HTML'; # Работает.
    }
    public function setConfig_ParseMode_Empty()
    {
        $this->config_parsemode = '';
    }


    private function getConfig_BasicArrayForMsg()
    {
        return [
            'peer' => $this->config_peer,
            'silent' => $this->config_silent,
            'no_webpage' => ( ! $this->config_previewIsOn ),
            'parse_mode' => $this->config_parsemode,
            '' => '', # Разделитель
        ];
    }


    # - ### ### ###
    #   NOTE: Большие жирные действия

    # action_....
    # Юзают api...  и чекают результаты.


    # - ### ### ###
    #   NOTE: Отправка сообщений

    # FINAL
    public function api_msgSend_Any($text, $schDateUnix=false, $imgUrl=false)
    {
        # - ####
        $text = (string)$text;
        # - ####
        $this->responseAll_Clear();
        # - ####
        $configArr = $this->getConfig_BasicArrayForMsg();
        $configArr['message'] = $text;

        if( $schDateUnix )
            $configArr['schedule_date'] = $schDateUnix;

        if( $imgUrl !== false )
            $configArr['media'] = [ '_' => 'inputMediaUploadedPhoto', 'file' => $imgUrl ];

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);
        $this->response_WriteInput('WITH_MEDIA', isset($configArr['media']) );

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {
            if( self::$configMy_sendMsg_DumpBegEnd  ){ dump('SendMsgLL: Запрос-Отправка'); flush(); }
            if( self::$configMy_sendMsg_DumpOptsArr ){ dump($configArr); flush(); }


            if( isset($configArr['media']) )
                $res = $this->API->messages->sendMedia  ($configArr);
            else
                $res = $this->API->messages->sendMessage($configArr);


            if( self::$configMy_sendMsg_DumpBegEnd ){ dump('SendMsgLL: Запрос-Конец'); flush(); }
        }catch(Throwable $e){ $this->response_WriteError( $e,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        if( is_string($res) ) # Если вылетает отправка, то там вернули строку.
            $res = [$res];

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            #dump('Есть ошибка');
            #if( $this->response_ErrorTg() )
            #    dump('Есть ошибка телеги');

            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм

            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['updates']);
            #dump('Ошибок нет');

            return true;
        }

    }


    public function sendInLogChat($text)
    {
        if( ! empty($this->config_peer) )
        {
            $chatNormal = $this->config_peer;
            $this->setConfig_Chat($this->config_peer_logs);

            $this->api_msgSend_Any($text);

            $this->setConfig_Chat($chatNormal);
        }
        else
        {
            $this->setConfig_Chat($this->config_peer_logs);
            $this->api_msgSend_Any($text);
            $this->setConfig_Chat('');
        }

        if($this->response_ErrorAny())
            if($this->response_ErrorTg())
                Ancii::anyTextDump($this->response_ErrorTgGetMsg());
    }


    # - ### ### ###
    # NOTE: Группа - Отложеные посты
    public function api_groupGet_PostsScheduled_Count()
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->messages->getScheduledHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['count']);
            return true;
        }
    }
    public function api_groupGet_PostsScheduled_ArrPrep()
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->messages->getScheduledHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм

            $posts = $this->reqInfo_AnswerPrep['messages'];
            #dd($posts[1]);

            #if( count($posts) === 0 )

            $fin = [ ];
            foreach($posts as $p)
            {
                $buf = [];
                $buf['ID'] = $p['id'];
                $buf['DATE_U'] = $p['date'];
                $buf['DATE_T'] = date('c',$p['date']);

                $buf['TEXT'] = $p['message'];
                $buf['MEDIA_HAS'] = ( ! empty($p['media']));


                $buf['__'] = 'Вроде как это не финалка, доделывать';
                #$buf[''] = $p[''];

                #dd($p);

                $fin []= $buf;
            }

            $this->response_WriteAnswer('RES',$fin);
            return true;
        }
    }

    # - ### ### ###
    # NOTE: Группа - Изменение постов
	
	
    # WORK
    public function api_groupPost_EditMsg_TextOrPhoto( $msgId , $msgNew=false , $imgNewUrl=false )
    {
	    # - ####
	    $this->responseAll_Clear();
	    # - ####
	
	    $configArr = [];
	    $configArr['peer'] = $this->config_peer;
	    $configArr['id'] = $msgId;
	    
	    if( $msgNew    !== false ) $configArr['message'] = $msgNew;
	    if( $imgNewUrl !== false ) $configArr['media']   = [ '_' => 'inputMediaUploadedPhoto', 'file' => $imgNewUrl ];
	    
	    if( (!$msgNew) && (!$imgNewUrl) )
	        dd( __METHOD__ , 'нет аргументв, оба false' );
	    
	
	    # - ####
	
	    $this->response_WriteInput('CONFIG',$configArr);
	
	    $this->responseBeforeSend();
	
	    # - ####
	    $res = 'UNDEF';
	
	    try {
		
		    $res = $this->API->messages->editMessage($configArr );
		
	    }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }
	
	    $this->responseAfterSend();
	
	    # - ####
	    #  Разбор результата
	
	    $this->response_WriteAnswer('RAW',$res);
	    $this->response_WriteAnswer('PREP_AUTO',[ ]);
	
	    if( $this->response_ErrorAny() )
	    {
		    return false;
	    }
	    else
	    {
		    # Телега не вурнула ошибку, значит все норм
		    $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['updates'][0]['message']);
		    return true;
	    }
    
    
    }


    # - ### ### ###
    # NOTE: Группа - Создание

    public function api_groupCreateNew($title)
    {
        # - ####
        #   Дал 10 раз, с отлежкой 3сек
        #   Каналы частные, не настроенные.
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['title'] = $title;
        $configArr['about'] = '';
        $configArr['broadcast'] = true; # Канал

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->channels->createChannel($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм

            $data = $this->reqInfo_AnswerPrep;

            $this->response_WriteAnswer('RES',[
                'ID' => $data['chats'][0]['id'],
                'Title' => $data['chats'][0]['title'],
            ]);

            return true;
        }
    }





    public function api_ETALON()
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->messages->getScheduledHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['count']);
            return true;
        }
    }


    # - ### ### ###
    #   NOTE: Группа - Посты на стене

    public function action_groupGet_PostsWall_ContentALL_Last( $count , $withPic=false)
    {
        $limit = 100; # Постов зв звпрос.  Макс 100.

        # Если можно сделать за 1 запрос, то подстраиваюсь.
        if( $count <= $limit )
            $limit = $count;

        if($count%$limit !== 0)
            dd('Количество постов должно быть кратно '.$limit);

        $requestsCount = (int)($count/$limit)-1;

        $FINAL = [  ];

        foreach( range(0,$requestsCount) as $i )
        {
            #dump('==============');
            $offset = ($limit * $i);
            #dump('I = '.$i,'Сдвиг = '.$offset);

            $this->api_groupGet_PostsWall_ContentALL_BySelect(
                'DEF',
                0,
                0,
                $offset,
                0,
                $limit,
	            $withPic
            );

            if( $this->response_ErrorAny() )
            {
                #;
            }

            $FINAL += $this->getResult();

            # Для долгих делаю показ прогреса
            if( $count >= 300 )
            {
                dump('Конец цикла - '.$i.'   count(FIN)='.count($FINAL));
                flush();
            }
        }

        # NOTE: Жесткий костыль из-за число-строковых ключей.
        #$FINAL_2 = [];
        #foreach( $FINAL as $set )
        #    foreach( $set as $keyStr=>$valArr )
        #    #dd($keyStr,$valArr);
        #        $FINAL_2[ (string)$keyStr ] = $valArr;


        #dd($FINAL);
        return $FINAL;
    }




    # TODO: Убрать всю обработку самого сообщения в отдельный метод.
    public function api_groupGet_PostsWall_ContentALL_BySelect( $chatId='DEF', $minId=0 , $maxId=0 , $addOffset=0 , $offsetDateU=0 , $limit=100 , $withPicFile=false )
    {
    	# - ####

        # https://core.telegram.org/method/messages.getHistory
        # FINAL - То, что надо.

        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];

        if( $chatId === 'DEF' )
            $configArr['peer'] = $this->config_peer;
        else
            $configArr['peer'] = $chatId;

        $configArr['limit'] = $limit; # Робит, макс 100
        #$configArr['offset_id'] = 0; #
        $configArr['offset_date'] = $offsetDateU; #
        $configArr['add_offset'] = $addOffset; #
        $configArr['min_id'] = $minId; # Минимальный id поста - использую для пагинации, при  0 возвращаются последние посты.
        $configArr['max_id'] = $maxId; # Максимальный id поста




        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $resRaw = 'UNDEF';

        try {

            $resRaw = $this->API->messages->getHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$resRaw);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм

            # - ###

            # Для ботов надо отключить часть полей
            $IS_BOT = ( ! isset($this->reqInfo_AnswerPrep['count']));

            if( ! $IS_BOT )
            {
                # BUG:   Тут может быть вылет.

                if( count( $this->reqInfo_AnswerPrep['chats'] ) === 0 )
                {
                    dd(__FUNCTION__,__LINE__,'Тот самый вылет из-за ключа=0,   мой дамп',
                        $this->reqInfo_AnswerPrep);
                }

                try{
                $channelUsername = $this->reqInfo_AnswerPrep['chats'][0]['username'];
                }catch(\Throwable $e){  dd( __METHOD__,$e,'Суть: у канала нет юзернейма, поэтому вылет.   МОЖНО изи пофиксить руками, но лень,'  );  }

            }

            # - ###

            $msgArrBig = $this->reqInfo_AnswerPrep['messages'];

            $FIN = [];
            foreach($msgArrBig as $key => $msgArr)
            {
                $buf = [];

                if( $msgArr['_'] !== 'message' )
                    continue; # messageService-был бы вылет


                # - ### ### ### ###
	            
	            $buf['PHOTO_HAS'] = false;
	            $buf['PHOTO_LOAD_ENABLED'] = $withPicFile;
	            
	            #dd(json_encode($resRaw,JSON_PRETTY_PRINT));
	            #dd(json_encode($resRaw['messages'][$key]['media']['photo'],JSON_PRETTY_PRINT));
	            #dd($resRaw['messages'][$key]['media']['photo']);

	            if( isset($resRaw['messages'][$key]['media']['photo']) )
	                $buf['PHOTO_HAS'] = (bool)count($resRaw['messages'][$key]['media']['photo']);
	            
                $TIMER_LOAD = new TimerMy();
	            $buf['PHOTO_INFO'] = [];
	            if( $withPicFile && $buf['PHOTO_HAS'] )
	            {
	            	$P = $resRaw['messages'][$key]['media']['photo'];
	            	
	            	$fileName = "TEMP_PIC.png";
	            	$buf['PHOTO_INFO']['ID'] = (string)$P['id'];
	            	$buf['PHOTO_INFO']['ID_MD5'] = md5($P['id']);
		            $pathPic = $this->API->downloadToFile($resRaw['messages'][$key],$fileName);
	            	$buf['PHOTO_INFO']['TIME_LOAD'] = $TIMER_LOAD->getTimeMs();
	            	$buf['PHOTO_INFO']['TIME_FULL'] = '';
					#dd($pathPic);
		            
		            
	            	
		            foreach( $P['sizes'] as $sizes )
		                if( isset( $sizes['_'] ) )
		                    if(  $sizes['_'] === 'photoSizeProgressive' )
	            	            $buf['PHOTO_INFO']['RESOLUTION'] = "{$sizes['w']}x{$sizes['h']}";
		                    	
		            
	            	$picRaw = file_get_contents($pathPic);
	            	$buf['PHOTO_INFO']['SIZE_RAW_B'] = strlen($picRaw);
	            	$buf['PHOTO_INFO']['SIZE_RAW_KB'] = (int)($buf['PHOTO_INFO']['SIZE_RAW_B']/1024);
	            	
	            	$picBase = base64_encode($picRaw);
		            $buf['PHOTO_INFO']['SIZE_BASE_B'] = strlen($picBase);
		            $buf['PHOTO_INFO']['SIZE_BASE_KB'] = (int)($buf['PHOTO_INFO']['SIZE_BASE_B']/1024);
	            	
		            $buf['PHOTO_INFO']['PIC_BASE64'] = $picBase;
		
		            unlink($fileName);
		            
	            	$buf['PHOTO_INFO']['TIME_FULL'] = $TIMER_LOAD->getTimeMs();
	            }
	
	            # - ### ### ### ###
	            
                if( ! $IS_BOT )
                    $buf['CHANNEL_ID'] = $msgArr['peer_id']['channel_id'];

                $buf['MSG_ID'] = $msgArr['id'];

                if( ! $IS_BOT )
                    $buf['MSG_URL'] = 'https://t.me/'.$channelUsername.'/'.$msgArr['id'];
                # https://t.me/prg_memes/7960
	
	            # - ### ### ### ###
	            
                $buf['DATE_U'] = $msgArr['date'];
                $buf['DATE_T']   = date('Y-m-d H:i:s',$msgArr['date']);
                # Убрал $buf['DATE_T_v2'] = date('c',$msgArr['date']);

                if( isset($msgArr['edit_date']) )
                {
                    $buf['DATE_EDIT_U'] = $msgArr['edit_date'];
                    $buf['DATE_EDIT_T']   = date('Y-m-d H:i:s',$msgArr['edit_date']);
                    $buf['DATE_EDIT_AFTER']   = date('Hч iм sс',$msgArr['edit_date']-$msgArr['date']-10800);
                }

                if( ! $IS_BOT )
                    $buf['from_scheduled'] = $msgArr['from_scheduled'];

	            # - ### ### ### ###
	            
                # NOTE: При пустом сообщении телега ставит ключ с пустым текстом   ... = ""
                $buf['TEXT_EMPTY'] = empty($msgArr['message']);
                $buf['TEXT_RAW'] = $msgArr['message'];
                $buf['TEXT_LEN'] = strlen($msgArr['message']);
                $buf['TEXT_ARR'] = explode("\n",$msgArr['message']);
                $buf['TEXT_IN_ONE_STR'] = implode(' ',$buf['TEXT_ARR']);
                $buf['TEXT_FIRST_STR'] = trim($buf['TEXT_ARR'][0]);
				
	            # - ### ### ### ###
				
	            if( isset($resRaw['messages'][$key]['entities']) )
		            $buf['ENTITIES_HAS'] = (bool)count($resRaw['messages'][$key]['entities']);
	            
	            if( $buf['ENTITIES_HAS'] )
	            {
	            	$ENT_ARR = $resRaw['messages'][$key]['entities'];
		            $buf['ENTITIES_COUNT'] = count($ENT_ARR);
		            $buf['ENTITIES_ARR'] = $ENT_ARR;
		            
		            /* # = # = #
		            $textRawEnt = $buf['TEXT_RAW'];
		            foreach( array_reverse($ENT_ARR) as $one )
		            {
		            	$type = $one['_'];
		            	$from = $one['offset'];
		            	$len = $one['length'];
		            	
		            	$textForWork = substr($textRawEnt,$from,$len);
		             
		            	$textStyled = '';
		            	switch($type)
			            {
				            case 'messageEntityBold':  $textStyled="<b>{$textForWork}</b>"; break;
				            case 'messageEntityTextUrl':  $textStyled="<a href=\"{$one['url']}\">{$textForWork}</a>"; break;
				            #case '':  $textStyled="<i>{$textForWork}<\i>"; break;
				            messageEntityHashtag
				            default: dump($one); $textStyled = $textForWork;
			            }
						иногда кривит кодировку
		                иногда криво вставляет офсет
			            $textRawEnt = str_replace($textForWork,$textStyled,$textRawEnt);
		            }
		            $buf['TEXT_RAW_ENTITIES'] = $textRawEnt;
		            # = # = # */
		            
		            # NOTE: Предполагаю, что форматирование будет потом, в момент использования.
	            }
	            
	            # - ### ### ### ###
             
	            $buf['COMMENTS_HAS'] = ( ! empty($msgArr['replies']));
                if($buf['COMMENTS_HAS'])
                {
                    #$buf['COMMENTS_ARR'] = $msgArr['replies'];
                    #$buf['COMMENTS_JSON'] = json_encode($msgArr['replies']);
                    $buf['COMMENTS_CNT'] = $msgArr['replies']['replies'];
                }
                else
                    $buf['COMMENTS_CNT'] = 0;

                # - ###
                $buf['REACTIONS_HAS'] = ( ! empty($msgArr['reactions']));
                if($buf['REACTIONS_HAS'])
                {
                    #$buf['REACTIONS'] = $msgArr['reactions'];

                    $buf['REACTIONS_ARR'] = [];
                    foreach( $msgArr['reactions']['results'] as $one )
                    {
                        $buf['REACTIONS_ARR'][$one['reaction']['emoticon']] = $one['count'];
                        # В emoticon лежит прям смайлик
                    }

                    $buf['REACTIONS_SUMM'] = 0;
                    foreach( $buf['REACTIONS_ARR'] as $k => $count )
                        $buf['REACTIONS_SUMM'] += $count;

                }
                else
                {
                    $buf['REACTIONS_ARR'] = [];
                    $buf['REACTIONS_SUMM'] = [];
                }


                # - ###
                $buf['BUTTONS_HAS'] = ( ! empty($msgArr['reply_markup']));

                if( $buf['BUTTONS_HAS'] )
                {


                    $botBtnRows = $msgArr['reply_markup']['rows'];

                    foreach ($botBtnRows as $row)
                    {
                        #$buttonsInfoArr = [];
                        foreach ($row['buttons'] as $btn) {
                            $info = [];

                            #$info['RAW_BTN'] = $btn;
                            $info['MSG_ID'] = $msgArr['id']; # Из текущего сообщения, а не из кнопки.

                            $info['JSON'] = $btn->jsonSerialize();
                            $info['TEXT'] = $info['JSON']['text'];
                            #$info['TEXT'] = $info['JSON']['text'];

                            if( isset($info['JSON']['data']) )
                                $info['CALL_COMMAND'] = base64_decode($info['JSON']['data']->jsonSerialize()['bytes']);
                            #dd(123,$info);

                            #$info[''] = $btn[''][''];

                            unset($info['JSON']);

                            $buf['BUTTONS_ALL']['INFO_ARR'] [] = $info;
                        }# End foreach btn
                    }# End foreach row




                    if( count( $buf['BUTTONS_ALL']['INFO_ARR'] ) )
                    {
                        foreach ($buf['BUTTONS_ALL']['INFO_ARR'] as $info)
                            if( isset($info['CALL_COMMAND']) )
                                $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] []= $info['CALL_COMMAND'];
                    }
                    else
                    {
                        # Если кнопок не было
                        $buf['BUTTONS_ALL']['INFO_ARR'] = [];
                    }

                    # Если были только кнопки без колбека, то явно ставлю пустой.
                    if( ! isset( $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] ) )
                        $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] = [];



                }#End if btn has



                # - ###
                $buf['MEDIA_HAS'] = ( ! empty($msgArr['media']));

                if($buf['MEDIA_HAS'])
                    $buf['MEDIA_TYPE'] = $msgArr['media']['_'];
                else
                    $buf['MEDIA_TYPE'] = '';

                # - ###
                if( ! $IS_BOT )
                {
                    $buf['STAT_VIEWS'] = $msgArr['views'];
                    $buf['STAT_REPOST'] = $msgArr['forwards'];
                }

                # - ### Дебаговые выводы
                #if( $buf['TEXT_LEN'] <= 10 )
                #if( isset($msgArr['edit_date']) )
                #    dump($buf);

                #$buf[''] = $msgArr[''];
                # - ###

                $FIN[ 'ID='.$buf['MSG_ID']] = $buf;
            }

            $this->response_WriteAnswer('RES',$FIN);
            return true;
        }
    }

    # WORK
    # Кривая копипаста метода api_groupGet_PostsWall_ContentALL_BySelect
    public function apiBot_groupGet_PostsWall_byIdsArr( $idsArr, $chatId='DEF')
    {
        # - ####

        # https://core.telegram.org/method/channels.getMessages

        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];

        if( $chatId === 'DEF' )
            $configArr['channel'] = $this->config_peer;
        else
            $configArr['channel'] = $chatId;

        $configArr['id'] = $idsArr;

        # NOTE: Только канал и id

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            # IMPORTANT Для бота: работает
            $res = $this->API->channels->getMessages($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм

            # - ###

            # Для ботов надо отключить часть полей
            $IS_BOT = ( ! isset($this->reqInfo_AnswerPrep['count']));

            if( ! $IS_BOT )
            {
                # BUG:   Тут может быть вылет.

                if( count( $this->reqInfo_AnswerPrep['chats'] ) === 0 )
                {
                    dd(__FUNCTION__,__LINE__,'Тот самый вылет из-за ключа=0,   мой дамп',
                        $this->reqInfo_AnswerPrep);
                }

                $channelUsername = $this->reqInfo_AnswerPrep['chats'][0]['username'];

            }

            # - ###

            $msgArrBig = $this->reqInfo_AnswerPrep['messages'];

            $FIN = [];
            foreach($msgArrBig as $msgArr)
            {
                $buf = [];

                if( $msgArr['_'] !== 'message' )
                    continue; # messageService-был бы вылет


                # - ###
                if( ! $IS_BOT )
                    $buf['CHANNEL_ID'] = $msgArr['peer_id']['channel_id'];

                $buf['MSG_ID'] = $msgArr['id'];

                if( ! $IS_BOT )
                    $buf['MSG_URL'] = 'https://t.me/'.$channelUsername.'/'.$msgArr['id'];
                # https://t.me/prg_memes/7960

                # - ###
                $buf['DATE_U'] = $msgArr['date'];
                $buf['DATE_T']   = date('Y-m-d H:i:s',$msgArr['date']);
                # Убрал $buf['DATE_T_v2'] = date('c',$msgArr['date']);

                if( isset($msgArr['edit_date']) )
                {
                    $buf['DATE_EDIT_U'] = $msgArr['edit_date'];
                    $buf['DATE_EDIT_T']   = date('Y-m-d H:i:s',$msgArr['edit_date']);
                    $buf['DATE_EDIT_AFTER']   = date('Hч iм sс',$msgArr['edit_date']-$msgArr['date']-10800);
                }

                if( ! $IS_BOT )
                    $buf['from_scheduled'] = $msgArr['from_scheduled'];

                # - ###
                # NOTE: При пустом сообщении телега ставит ключ с пустым текстом   ... = ""
                $buf['TEXT_EMPTY'] = empty($msgArr['message']);
                $buf['TEXT_RAW'] = $msgArr['message'];
                $buf['TEXT_LEN'] = strlen($msgArr['message']);
                $buf['TEXT_ARR'] = explode("\n",$msgArr['message']);
                $buf['TEXT_IN_ONE_STR'] = implode(' ',$buf['TEXT_ARR']);
                $buf['TEXT_FIRST_STR'] = trim($buf['TEXT_ARR'][0]);

                # - ###
                $buf['COMMENTS_HAS'] = ( ! empty($msgArr['replies']));
                if($buf['COMMENTS_HAS'])
                {
                    #$buf['COMMENTS_ARR'] = $msgArr['replies'];
                    #$buf['COMMENTS_JSON'] = json_encode($msgArr['replies']);
                    $buf['COMMENTS_CNT'] = $msgArr['replies']['replies'];
                }
                else
                    $buf['COMMENTS_CNT'] = 0;

                # - ###
                $buf['REACTIONS_HAS'] = ( ! empty($msgArr['reactions']));
                if($buf['REACTIONS_HAS'])
                {
                    #$buf['REACTIONS'] = $msgArr['reactions'];

                    $buf['REACTIONS_ARR'] = [];
                    foreach( $msgArr['reactions']['results'] as $one )
                    {
                        $buf['REACTIONS_ARR'][$one['reaction']['emoticon']] = $one['count'];
                        # В emoticon лежит прям смайлик
                    }

                    $buf['REACTIONS_SUMM'] = 0;
                    foreach( $buf['REACTIONS_ARR'] as $k => $count )
                        $buf['REACTIONS_SUMM'] += $count;

                }
                else
                {
                    $buf['REACTIONS_ARR'] = [];
                    $buf['REACTIONS_SUMM'] = [];
                }


                # - ###
                $buf['BUTTONS_HAS'] = ( ! empty($msgArr['reply_markup']));

                if( $buf['BUTTONS_HAS'] )
                {


                    $botBtnRows = $msgArr['reply_markup']['rows'];

                    foreach ($botBtnRows as $row)
                    {
                        #$buttonsInfoArr = [];
                        foreach ($row['buttons'] as $btn) {
                            $info = [];

                            #$info['RAW_BTN'] = $btn;
                            $info['MSG_ID'] = $msgArr['id']; # Из текущего сообщения, а не из кнопки.

                            $info['JSON'] = $btn->jsonSerialize();
                            $info['TEXT'] = $info['JSON']['text'];
                            #$info['TEXT'] = $info['JSON']['text'];

                            if( isset($info['JSON']['data']) )
                                $info['CALL_COMMAND'] = base64_decode($info['JSON']['data']->jsonSerialize()['bytes']);
                            #dd(123,$info);

                            #$info[''] = $btn[''][''];

                            unset($info['JSON']);

                            $buf['BUTTONS_ALL']['INFO_ARR'] [] = $info;
                        }# End foreach btn
                    }# End foreach row




                    if( count( $buf['BUTTONS_ALL']['INFO_ARR'] ) )
                    {
                        foreach ($buf['BUTTONS_ALL']['INFO_ARR'] as $info)
                            if( isset($info['CALL_COMMAND']) )
                                $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] []= $info['CALL_COMMAND'];
                    }
                    else
                    {
                        # Если кнопок не было
                        $buf['BUTTONS_ALL']['INFO_ARR'] = [];
                    }

                    # Если были только кнопки без колбека, то явно ставлю пустой.
                    if( ! isset( $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] ) )
                        $buf['BUTTONS_ALL']['CALL_COMMANDS_ALL_ARR'] = [];



                }#End if btn has



                # - ###
                $buf['MEDIA_HAS'] = ( ! empty($msgArr['media']));

                if($buf['MEDIA_HAS'])
                    $buf['MEDIA_TYPE'] = $msgArr['media']['_'];
                else
                    $buf['MEDIA_TYPE'] = '';

                # - ###
                if( ! $IS_BOT )
                {
                    $buf['STAT_VIEWS'] = $msgArr['views'];
                    $buf['STAT_REPOST'] = $msgArr['forwards'];
                }

                # - ### Дебаговые выводы
                #if( $buf['TEXT_LEN'] <= 10 )
                #if( isset($msgArr['edit_date']) )
                #    dump($buf);

                #$buf[''] = $msgArr[''];
                # - ###

                $FIN[ 'ID='.$buf['MSG_ID']] = $buf;
            }

            $this->response_WriteAnswer('RES',$FIN);
            return true;
        }
    }






    # WORK
    public function apiBot_groupGet_FullChat(  )
    {
        # - ####

        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['channel'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->channels->getFullChannel($configArr );


        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['full_chat']);
            return true;
        }

    }

    public function api_groupGet_PostsWall_PostsCount()
    {
        # - ####

        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->messages->getHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['count']);
            return true;
        }
    }

    public function api_groupGet_PostsWall_PTS()
    {
        # - ####

        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            $res = $this->API->messages->getHistory($configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);

        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['pts']);
            return true;
        }
    }

    # Сразу, без проверок и тд
    public function api_groupGet_UsersCount()
    {
        return $this->getInfo_ANY_Full(  )['full']['participants_count'];
    }





    # - ### ### ###
    # NOTE: Ботное





    public function api_botButton_Click($msgId, $btnData, $waitMs=1500000)
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['peer'] = $this->config_peer;
        $configArr['msg_id'] = $msgId;
        $configArr['data'] = $btnData;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {
            if( self::$configMy_botBtnClicks_DumpBegEnd ){ dump('botBtnClick: Кликаю->'.$btnData); flush(); }

            #$res = $this->API->messages->getScheduledHistory($configArr );

            # IMPORTANT  Либо ставить false и ждать пока сам вылетит. Так тоже работает.
            $res = $this->API->clickInternal(true, 'messages.getBotCallbackAnswer', $configArr);

            usleep($waitMs);  # 300-20   500-13    1000-5 = идеал      время-итераций
            # NOTE:
            #  Если ждать самовылет, то фулл заказ занимает 1м40с
            #  1000мс = фулл заказ за 20сек.  Но вылетел в конце
            #  1500мс = фулл заказ за 16-21-19-- сек.  Вылетов пока не было

            $this->response_WriteAnswer('RAW',$res);
            $this->response_WriteAnswer('PREP_AUTO',[ ]);

            throw new Exception('JSON = '.json_encode($res));

        }catch(Throwable $e){

            $text = 'botBtnClick: Клик закончен => TryCatch =>'.PHP_EOL.$e->getMessage();
            if( self::$configMy_botBtnClicks_DumpBegEnd ){ dump($text); flush(); }

            $this->responseAfterSend();

            $this->response_WriteError($e ,
                'TryCatch = МОЙ = '.__FUNCTION__.'(...)',
                [$configArr,$e->getMessage()]
            );

            return '';

        }

    }



    # - ### ### ###
    #   NOTE: Парсеры полученной инфы

    # FINAL
    function parser_User($data)
    {
        /*
        2 => array:26 [▼
      "_" => "user"   # NOTE !!!
      "self" => false
      "contact" => false
      "mutual_contact" => false
      "deleted" => false
      "bot" => false
      "bot_chat_history" => false
      "bot_nochats" => false
      "verified" => false
      "restricted" => false
      "min" => false
      "bot_inline_geo" => false
      "support" => false
      "scam" => false
      "apply_min_photo" => true
      "fake" => false
      "bot_attach_menu" => false
      "premium" => false
      "attach_menu_enabled" => false
      "id" => 123
      "access_hash" => 3148909151576958904
      "first_name" => "thuong"                может не быть ключа
      "last_name" => "nguyen"                 может не быть ключа  одного этого
      "username" => "ynguyenthuongwssys"    может не быть ключа
      "photo" => "### ERASED ###"          может не быть ключа
      "status" => array:1 [ …1]             может не быть ключа
    ]
        */

        $FIN = [];

        $FIN['ID'] = $data['id'];
        $FIN['IS_DEL'] = $data['deleted'];
        $FIN['IS_FAKE'] = $data['fake'];
        $FIN['IS_PREM'] = $data['premium'];


        $FIN['NAME_HAVE'] = ( (isset($data['first_name']) || isset($data['last_name'])) );
        if( $FIN['NAME_HAVE'] )
        {
            $FIN['NAME_F_HAVE'] = isset($data['first_name']);
            $FIN['NAME_F'] = $data['first_name'] ?? '';

            $FIN['NAME_L_HAVE'] = isset($data['last_name']);
            $FIN['NAME_L'] = $data['last_name']  ?? '';

            $FIN['NAME_FULL'] = trim($FIN['NAME_F'].' '.$FIN['NAME_L']);
        }

        $FIN['USERNAME_HAVE'] = isset($data['username']);
        if( $FIN['USERNAME_HAVE'] )
        {
            $FIN['USERNAME_TEXT'] = $data['username'];
            $FIN['USERNAME_DOG'] = '@'.$data['username'];
        }

        $FIN['AVATAR_HAVE'] = isset($data['photo']);

        $FIN['STATUS_HAVE'] = isset($data['status']);

        if( $FIN['STATUS_HAVE'] )
        {
            $FIN['STATUS_INFO'] = $data['status']['_'];
        }


        return $FIN;
    }

    function parser_AdminLogEvent($data)
    {
        /*
            2 => array:5 [▼
          "_" => "channelAdminLogEvent"
          "id" => 123
          "date" => 1678584078
          "user_id" => 123
          "action" => array:1 [▼
            "_" => "channelAdminLogEventActionParticipantJoin"
          ]
        ]
        */

        $FIN = [];


        $FIN['EVENT_ID'] = $data['id'];
        $FIN['USER_ID'] = $data['user_id'];

        $FIN['DATE_U'] = $data['date'];
        $FIN['DATE_T'] = DaterUC::convertUnixToClassic($data['date']);


        #$FIN['ACTION_FULL'] = $data['action'];
        $FIN['ACTION_RAW'] = $data['action']['_'];

        switch( $data['action']['_'] )
        {
            case 'channelAdminLogEventActionParticipantJoin' : $FIN['ACTION_TYPE'] = 'JOIN'; break;
            case 'channelAdminLogEventActionParticipantLeave': $FIN['ACTION_TYPE'] = 'LEAVE'; break;
            #case '': break;

            default: $FIN['ACTION_TYPE'] = 'UNDEF = '.$data['action']['_'];
        }

        return $FIN;
    }



    # - ### ### ###
    #   NOTE: Группа - Всякое


    # WORK
    public function getGroup_UsersList_Recent200()
    {
        $opts = [
            'channel' => $this->config_peer,
            'filter' => ['_' => 'channelParticipantsRecent'],
            'limit' => 200,
            'offset' => 0 ,
        ];

        $res = $this->API->channels->getParticipants( $opts );  # Ласт 200 со всей инфой
        $res = $this->responseCleanBadKeys($res);
        #dump($res);


        if( $res['count'] === 0 ) return [];
        if( count($res['users']) === 0 ) return [];


        $FIN = [];
        foreach( range(0, count($res['users'])-1) as $i )
        {
            $u = $res['users'][$i];
            $p = $res['participants'][$i];

            $FIN[$i] = $this->parser_User($u);
            $FIN[$i]['PARTICIP_DATE_U'] = $p['date'];
            $FIN[$i]['PARTICIP_DATE_T'] = DaterUC::convertUnixToClassic($p['date']);
        }

        return $FIN;
    }


    # NOTE: Первичный
    public function getGroup_AdminLog($min=0,$max=0,$limit=100)
    {
        # https://core.telegram.org/type/ChannelAdminLogEventsFilter
        # https://core.telegram.org/method/channels.getAdminLog

        # Только ласт 48ч

        $opts = [
            'channel' => $this->config_peer,
            'events_filter' =>
                [
                '_' => 'channelAdminLogEventsFilter',
                'leave'=>true,
                'join'=>true,
            ],
            'q' => '', # Влияет
            #'admins' => [$this->config_peer],
            #'offset' => -100, # Не работает ни + ни -
            #'offset_id' => 94512570632, #
            #'add_offset' => 0, #

            # 94407447752 есть  94209037120
            'min_id' => $min, #  94 624 371672 - 94 505 224456   внизу старые
            'max_id' => $max, # Сегодня 94969228672
            # Два нуля работают.
            'limit' => $limit, # Макс 100
        ];


        $res = $this->API->channels->getAdminLog( $opts );
        $res = $this->responseCleanBadKeys($res);

        #dd($res);
        if( count($res['events']) === 0 ) return [];


        $FIN = [];
        foreach( range(0, count($res['events'])-1) as $i )
        {
            $e = $res['events'][$i];
            $u = $res['users'][$i];

            $FIN[$i]['EVENT'] = $this->parser_AdminLogEvent($e);
            $FIN[$i]['USER'] = $this->parser_User($u);
        }

        return $FIN;
    }

    public function getGroup_AdminLog_Full()
    {
        $FIN = [];

        $lastLog = $this->getGroup_AdminLog(0,0,1);
        $lastLogId = $lastLog[0]['EVENT']['EVENT_ID']; # 94 971 208 800

        #$lastLogIdUpper = ((int)ceil( $lastLogId / 1000000000 ))*1000000000; # 95 000 000 000

        $opt_DownStep = 50000000; # 50 000 000
        $opt_SleepSec = 1.0;
        $opt_end_i = 60;

        # 334   348  352
        # 94 975 281 380 -> 94 513 760 096 = 100


        $max = $lastLogId + 100000000;
        $min = $max - $opt_DownStep;

        foreach( range(1, $opt_end_i) as $i )
        {

            #if($i === 4) break;
            #if($i === $opt_end_i) break;


            $res = $this->getGroup_AdminLog($min,$max);
            $resCount = count($res);

            $FIN = array_merge($FIN,$res);
            #$FIN += $res;

            $text = '';
            if( $resCount )
            {
                #$res
                $text = '  '.end($res)['EVENT']['DATE_T'];
            }
            dump("I=$i  #  ID: $max -> $min  #  Логов: $resCount  #".$text);

            if( $resCount === 100 )
                Ancii::anyTextDump("100");




            Sleeper::sleeper($opt_SleepSec,'',true);


            $maxOld = $max;
            $minOld = $min;

            $min = $maxOld-1;  # -1 чтоб не было дублей
            $max = $minOld - $opt_DownStep;
        }

        # Добавить проверку даты на старость


        return $FIN;
    }




    # Спорно
    public function getGroup_UsersList()
    {
        # https://core.telegram.org/method/channels.getParticipant
        # https://core.telegram.org/method/channels.getParticipants
        # https://docs.madelineproto.xyz/API_docs/methods/channels.getParticipant.html
        # https://docs.madelineproto.xyz/API_docs/methods/channels.getParticipants.html

        dump('Думает секунд 10 для 200шт',
            'Для своего канала все норм',
            'Для чужого канала - пустой массив');
        dump('Видит всех 2500, но сфетчить смог только ~1400.  Чекать лог');
        flush();

        # Не более 200 за раз.  Лимит.

        dd('Лучше не юзать');

        $usersCnt = $this->api_groupGet_UsersCount(  );

        $limit = 200;

        $cntRequests = ((int)floor($usersCnt / $limit)) + 1;
        #dd($usersCnt);

        $res = $this->API->getPwrChat( $this->config_peer );  # Делает перебор по поиску.  Только те, кто с никами.
        $res = $this->responseCleanBadKeys($res);
        dd($res);


        $fullArrRaw = [];

        foreach( range(0, $cntRequests) as $i )
        {
            # channelParticipantsRecent = Max 200
            $opts = [
                'channel' => $this->config_peer,
                'filter' => ['_' => 'channelParticipantsSearch', 'q' => 'a'],
                #'filter' => ['_' => 'channelParticipantsRecent'],
                #'events_filter' => ['_' => 'channelAdminLogEventsFilter', 'leave'=>true,'join'=>true],
                #'min_id' => 0,  'max_id' => 10000,
                #'q' => '',
                'limit' => $limit,
                'offset' => 0 + ( $limit*$i ),
            ];

            $res = $this->API->channels->getParticipants( $opts );  # Ласт 200 со всей инфой

            $res = $this->responseCleanBadKeys($res);
            #dump($res);

            $fullArrRaw['i='.$i.'='.$opts['offset']] = $res;

            #sleep(1);

            if( $i === 2 ) break;
        }


        # Робит, нл это не совсем то.
        #$res = $this->API->getPwrChat( $this->config_peer )['participants']; #

        dd($fullArrRaw);

        return $res;
    }



    # - ### ### ###
    #   NOTE: Тестовая

    public function testing()
    {
        $arr = [
            'channel' => $this->config_peer ,
            #'limit' => 5 ,
            #'filter' => false,
            #'offset' =>  123 ,
        ];

        $res = $this->API->getPwrChat( $this->config_peer ); #
        return $this->responseCleanBadKeys($res);

    }


    # - ### ### ###
    #   NOTE:






    # - ### ### ###
    #   NOTE: Пробив любой инфы о канале или юзере

    public function getInfo_ANY_Short()
    {
        $res = $this->API->getInfo( $this->config_peer ); #
        return $this->responseCleanBadKeys($res);
    }
    public function getInfo_ANY_Full()
    {
        $res = $this->API->getFullInfo( $this->config_peer ); #
        return $this->responseCleanBadKeys($res);
    }
    public function getInfo_ANY_Chat()
    {
        $res = $this->API->getPwrChat( $this->config_peer ); #
        return $this->responseCleanBadKeys($res);
    }

    public function getInfo_User_SelfArr()
    {
        return $this->API->getSelf( );
    }






    # - ### ### ###
    #   NOTE: Работа с ответом



    public function getResult (  )
    {
        return $this->reqInfo_AnswerResult;
    }
    public function getResultDump (  )
    {
        dump($this->reqInfo_AnswerResult);
    }
    public function getResultDD (  )
    {
        dd($this->reqInfo_AnswerResult);
    }


    
    public function responseRaw_Get( $asJson = false )
    {
    	$RAW =  $this->reqInfo_AnswerRaw;
	    if( $asJson )
		    return json_encode($RAW,JSON_PRETTY_PRINT);
	    else
		    return $RAW;
    }
	public function responsePrep_Get( $asJson = false )
	{
		$RAW =  $this->reqInfo_AnswerPrep;
		if( $asJson )
			return json_encode($RAW,JSON_PRETTY_PRINT);
		else
			return $RAW;
	}
	public function responseAll_Get ( $asJson = false )
    {
        $arr = [
            'SEND'  => $this->reqInfo_Send,
            'INPUT' => $this->reqInfo_Input,
            'ERROR' => $this->reqInfo_Error,

            #'ANS_RAW'  => $this->reqInfo_AnswerRaw,
            'ANS_PREP'  => $this->reqInfo_AnswerPrep,
            'ANS_RESULT' => $this->reqInfo_AnswerResult,
        ];

        #if( $noRaw )
        #    $arr['ANS_RAW'] = 'Erased  =>  noRaw = true';

        if( $asJson )
            return json_encode($arr);
        else
            return $arr;
    }
    public function responseAll_Dump(  )
    {
        dump( $this->responseAll_Get() );
    }
    public function responseAll_DD  (  )
    {
        dump( $this->responseAll_Get() );
        $this->onENDING_DD();
    }

    public function responseAll_Clear(  )
    {
        $this->reqInfo_Send = [];
        $this->reqInfo_Input = [];
        $this->reqInfo_Error = [];

        $this->reqInfo_AnswerRaw = [];
        $this->reqInfo_AnswerPrep = [];
        $this->reqInfo_AnswerResult = [];
    }




    public function responseBeforeSend(  )
    {
        $this->reqInfo_Send['SENDED'] = true; # NOTE: По идее полный юзлесс
        $this->reqInfo_Send['SEND_START_DT_T'] = date('Y-m-d H:i:s');
        $this->reqInfo_Send['SEND_START_DT_UM'] = microtime(true);


    }
    public function responseAfterSend(  )
    {
        $this->reqInfo_Send['SEND_END_DT_T'] = date('Y-m-d H:i:s');
        $this->reqInfo_Send['SEND_END_DT_UM'] = microtime(true);

        $time = floor((microtime(true) - ($this->reqInfo_Send['SEND_START_DT_UM']) )*1000);
        $this->reqInfo_Send['SEND_TIME_MS'] = (int) $time;
        $this->reqInfo_Send['SEND_TIME_S']  = number_format(($time/1000),2);
    }


    public function response_WriteAnswer( $type , $data )
    {
        if( $type === 'RAW' ) $this->reqInfo_AnswerRaw = $data;

        if( $type === 'PREP' ) $this->reqInfo_AnswerPrep = $data;
        if( $type === 'PREP_AUTO' ) $this->reqInfo_AnswerPrep = $this->responseCleanBadKeys($this->reqInfo_AnswerRaw);

        if( $type === 'RES' ) $this->reqInfo_AnswerResult = $data;

    }







    public function response_WriteInput( $key , $val )
    {
        $this->reqInfo_Input[$key] = $val;
    }

    public function response_WriteError( $e , $comment='...' , $dataAny='...' )
    {
        $this->reqInfo_Error['_COMMENT'] = $comment;
        $this->reqInfo_Error['_DATA_ANY'] = $dataAny;

        $this->reqInfo_Error['IS_ERROR_TG'] = ( ! empty($e->rpc));
        #$this->req_info['IS_TG_ERR'] = ctype_upper(str_replace('_', '', $error->getMessage()));

        $this->reqInfo_Error['RAW']  = $e;
        $this->reqInfo_Error['MSG']  = $e->getMessage();
        $this->reqInfo_Error['CODE'] = $e->getCode();
        #$this->reqInfo_Error[''] = 1;

    }
    public function response_ErrorAny(  ):bool
    {
        return isset($this->reqInfo_Error['RAW']);
    }
    public function response_ErrorTg(  ):bool
    {
        return ($this->reqInfo_Error['IS_ERROR_TG'] ?? false);
    }
    public function response_ErrorTgGetMsg(  ):string
    {
        return ($this->reqInfo_Error['MSG'] ?? 'UNDEF');
    }
    public function response_ErrorTgGetCode(  ):string
    {
        return ($this->reqInfo_Error['CODE'] ?? 'UNDEF');
    }


    # УСТАРЕЛО
    public function getResponse_full($dd=false)
    {
        $respAll = $this->responseAll_Get();
        if($dd)
            dd(json_encode($respAll),$respAll,__FUNCTION__);
        else
            return $respAll;
    }


    # WORK
    public function responseCleanBadKeys( $R )
    {
        # - ###
        #  - Циклическая очистка при апдейтах

        if( is_string($R) )
            return [$R];

        # file_reference = не всегда робит
        $keysForClean = ['sizes','file_reference','stripped_thumb','thumbs','document','bytes'];
        $keysForClean []= 'photo';
        $text = '### ERASED ###';

        # - ###
        # Рекурсивный проход по всем ключам.
        # До 6 уровней вложенности, любые ключи.
        # TODO: Надо бы переписать на рекурсию.

        foreach ($R as $key1 => &$e1) # WORK
        {
            if( in_array($key1, $keysForClean) ){ $e1 = $text; continue; }
            if( ! is_array($e1) ) continue;
            foreach($e1 as $key2 => &$e2)
            {
                if( in_array($key2, $keysForClean) ){ $e2 = $text; continue; }
                if( ! is_array($e2) ) continue;
                foreach($e2 as $key3 => &$e3)
                {
                    if( in_array($key3, $keysForClean) ){ $e3 = $text; continue; }
                    if( ! is_array($e3) ) continue;
                    foreach($e3 as $key4 => &$e4)
                    {
                        if( in_array($key4, $keysForClean) ){ $e4 = $text; continue; }
                        if( ! is_array($e4) ) continue;
                        foreach($e4 as $key5 => &$e5)
                        {
                            if( ! is_array($e5) ) continue;
                            if( in_array($key5, $keysForClean) ){ $e5 = $text; continue; }
                            foreach($e5 as $key6 => &$e6)
                            {
                                if( ! is_array($e6) ) continue;
                                if( in_array($key6, $keysForClean) ){ $e6 = $text; continue; }
                                foreach($e6 as $key7 => &$e7)
                                {
                                    if( ! is_array($e7) ) continue;
                                    if( in_array($key7, $keysForClean) ){ $e7 = $text; continue; }
                                    foreach($e7 as $key8 => &$e8)
                                    {
                                        if( ! is_array($e8) ) continue;
                                        if( in_array($key8, $keysForClean) ){ $e8 = $text; continue; }
                                        # 8 уровней
                                        # Больше пока не требовалось.  8 было нужно.
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /*
        if( count($R['updates']) )
        {
            foreach( $R['updates'] as $i => $upd )
            {
                if( ! empty($upd['message']['media']['photo']['sizes']) )
                    @$upd['message']['media']['photo']['sizes'] = '### ERASED ###';

                if( ! empty($upd['message']['media']['photo']['file_reference']) )
                    @$upd['message']['media']['photo']['file_reference'] = '### ERASED ###';

            }
        }
        */

        # Старое - удалить
        #if( ! empty($res['updates'][2]['message']['media']['photo']['sizes']) ) @$res['updates'][2]['message']['media']['photo']['sizes'] = '### ERASED ###';
        #if( ! empty($res['updates'][2]['message']['media']['photo']['file_reference']) ) @$res['updates'][2]['message']['media']['photo']['file_reference'] = '### ERASED ###';

        return $R;
    }


    # - ### ### ###
    #   NOTE:


    public function debugWrite($func,$data)
    {
        $i = $this->DEBUG_ARR['I'];

        $this->DEBUG_ARR[$i.' = '.$func.'()'] = $data;

        $this->DEBUG_ARR['I'] += 1;


        #dump($data); flush();
    }
    public function debugDump()
    {
        dump($this->DEBUG_ARR);
        flush();
    }
    public function debugClear()
    {
        $this->DEBUG_ARR = ['I' => 1];
    }
    public function debugGet()
    {
        return $this->DEBUG_ARR;
    }


    # - ### ### ###
    #   NOTE:


	# - ### ### ###
    #   NOTE: Рандом чтоб под рукой

    public function getRand_Int()
    {
        return random_int(100, 999);
    }
    public function getRand_ImgUrl()
    {
        $arr = [
            #'https://decovar.dev/blog/2018/03/31/csharp-dotnet-core-publish-telegram/images/dotnet-core-telegram-logo.png',
            'https://www.hackingwithswift.com/uploads/matrix.jpg',
            'https://as2.ftcdn.net/v2/jpg/02/51/82/25/1000_F_251822542_qFYUeiPrOHWZaW8TAbPwbgnsgFqfxsNe.jpg',
            'https://pibig.info/uploads/posts/2022-12/1670660369_1-pibig-info-p-podelki-po-fizike-oboi-1.jpg',
            'https://tlt.ru/wp-content/uploads/2023/03/1647644330_2-amiel-club-p-fizika-krasivie-kartinki-2.jpg',
            'https://polaris-adygea.ru/images/programs/nauka/kruzhki_2021-2022/fizika_kruzhok.jpg',
            'https://sitekid.ru/imgn/48/28.jpg',
            'https://kipmu.ru/wp-content/uploads/vsln.jpg',
            'https://s0.rbk.ru/v6_top_pics/media/img/7/46/756584762646467.jpg',
            'https://s9.travelask.ru/system/images/files/000/328/932/wysiwyg_jpg/00000.jpg',
            'https://sunplanets.info/wp-content/uploads/2020/03/galaktika-tumannost-andromedy-970x606.jpg',
        ];
        return $arr[array_rand($arr)];
    }


    # - ### ### ###
    #   NOTE: Дебаговые вызовы и логи

    public function log($what)
    {
        $this->API->logger($what);
    }

    public function echo($what)
    {
        $this->API->echo($what);
    }


    # - ### ### ###
    #   NOTE: Всякие дампы


    public function onBEGIN()
    {
        $this->dump_onBegin();
        $this->log_begin();
    }
    public function onENDING()
    {
        $this->dump_onEnd();
        $this->log_end();
    }
    public function onENDING_DD()
    {
        $this->onENDING();
        dd('TGC Ending');

    }

    private function log_begin()
    {
        $this->API->logger(str_pad('',3,PHP_EOL));
        $this->API->logger(str_pad('',10,'#='));
        $this->API->logger(str_pad('',50,'#='));
        $this->API->logger('START = '.date("Y-m-d H:i:s"));
        $this->API->logger(str_pad('',25,PHP_EOL));
    }
    private function log_end()
    {
        $this->API->logger('END = '.date("Y-m-d H:i:s"));
        $this->API->logger(str_pad('',50,'#='));
        $this->API->logger(str_pad('',10,'#='));
        $this->API->logger(str_pad('',3,PHP_EOL));
    }

    private function dump_onBegin()
    {
        dump(date("Y-m-d H:i:s"));

        #dump([$this->API]);
        #dump([$this->API->get_all_methods()]);   # Arr 673 метода

        dump('============= Beg ================');
        echo '<hr color="red"><br><br><br>';

        flush();
    }
    private function dump_onEnd()
    {
        echo '<br><br><br><hr color="red">';
        dump('============= End ================');
        dump(date("Y-m-d H:i:s"));
        dd('DD onEnd');
    }

    # - ### ### ###
    #   NOTE:  По сути свалка.   Рабочие, но бесполезные методы


    # Спорная применимость, но пусть.
    # Если ID нет, то вернет "_" => "messageEmpty"
    # FINAL
    public function api_groupGet_WallPosts_ByIdArr($idArr)
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        $configArr = [];
        $configArr['channel'] = $this->config_peer;
        $configArr['id'] = $idArr;
        $configArr['increment'] = false;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            # NOTE: Фулл робит, но хз что с ласт ид
            $res = $this->API->channels->getMessages( $configArr );

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);


        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['messages']);
            return true;
        }
    }


    # NOTE: ЮЗЛЕСС + криво работает
    #  ID не совпадают с ид постов.   хз почему, не везде 30к+ просмотров
    public function api_groupGet_Views_ByIdArr($idArr)
    {
        # - ####
        $this->responseAll_Clear();
        # - ####

        # https://core.telegram.org/method/messages.getMessagesViews

        $configArr = [];
        $configArr['channel'] = $this->config_peer;
        $configArr['id'] = $idArr;
        $configArr['increment'] = false;

        # - ####

        $this->response_WriteInput('CONFIG',$configArr);

        $this->responseBeforeSend();

        # - ####
        $res = 'UNDEF';

        try {

            # NOTE: Норм робит.
            $res = $this->API->messages->getMessagesViews( $configArr ); # как было

        }catch(Throwable $e){  $this->response_WriteError( $e ,'TryCatch = '.__FUNCTION__.'(...)',$configArr);  }

        $this->responseAfterSend();

        # - ####
        #  Разбор результата

        $this->response_WriteAnswer('RAW',$res);
        $this->response_WriteAnswer('PREP_AUTO',[ ]);


        if( $this->response_ErrorAny() )
        {
            return false;
        }
        else
        {
            # Телега не вурнула ошибку, значит все норм
            $this->response_WriteAnswer('RES',$this->reqInfo_AnswerPrep['views']);
            return true;
        }
    }


    # - ### ### ###
    #   NOTE:




    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
