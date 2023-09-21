<?php

namespace LibMy;



/** Огромный класс для низкоуровневой работы с API VK.
 * Был в продакшене.
 */
class apiVkCom
{
    # - ### ### ###

    public function __construct() {    }
    public function __destruct()  {    }

    # - ### ### ###

    /* токен для запросов */
    public $token;

    public $versionVk = "5.131"; #

    public $loadPosts_sleepMs = 1000000; #
	
    public $makePosts_sleepSec = 0.5; #
	# NOTE:
	#  Время: 1500 ===> 0.8=1200сек==20мин   1=1500сек==25мин
	#  0.1 = ошибкаМного
	#  0.4 = капчи каждые около 100
	#  0.5 = Подряд ===> (не факт, тк тупит)300->300->300.
	#  0.6 = хз = капча на 76шт    2 раза
	#  0.7 =
	#  0.8 = Подряд ===> 300->300->300. | Подряд=300->300->300->300->169+.
	#  1.0 = хз = капча на ~450шт    После отлежки ~5м капчи нет = +300постов   СразуВвел->+300  СразуВвел->+300  СразуВвел->+
	
	
    # - ### ### ###
	# NOTE: Заметки
	#  - ВК офф: Макс 3 запроса в сек
    #  - https://habr.com/ru/articles/657569/  про загрузку пикч

    # - ### ### ###
    #   NOTE: Опции
	
    public function setOpt_ParseSleepTime(int $ms )
    {
        $this->loadPosts_sleepMs = $ms;
    }

    public function setOpt_Token( string $token )
    {
        $this->token = $token;
    }
	public function setOpt_Token_fromENV(  )
	{
        $this->token = env('TOKEN__VK_API__REAL');
	}
    public function setOpt_Token_fromAUTH(  )
    {
		$this->token = authTokenStrings::getVkAuthToken_FromDomainsRoot();
    }


    # - ### ### ###
	
	public static function InitUniv(  )
	{
		# - ### ### ### ###
		# - ### VK-Api Все
		
		set_time_limit(6000); # 100мин
		ignore_user_abort(false);
		ini_set('memory_limit','256M');
		
		date_default_timezone_set("UTC");
		
		$VK = new self();
		$VK->setOpt_Token_fromAUTH();
		$VK->captcha_setAnswerIfNeed_GET();
		
		return $VK;
		
		# - ###
		# - ### ### ### ###
	}
	
	# Работает и для ключа сообщества
	public static function DEV_FastWorkTest()
	{
		# NOTE: Быстрый тест живости токена и класса в целом.
		# NOTE: Норм даст даже если нужна капча.
		dump(self::InitUniv()->apiGroupGet_AllInfo('business'));
	}
	
    # - ### ### ###
    #   NOTE: Крупные цельные экшены

	
    # Чужая = 4000 по 100 = интервал 3 = норм
    # Чужая = 3000 по 100 = интервал 2 = норм
	# NOTE: Выкачиватьпо 1-2к через офсеты
    public static function actionBig_getLastPosts($maxPosts,$idOrDomain,$fileNameJson,$offsetShift=0,$retMode='FILE')
    {
        set_time_limit(600); # 10мин
        ignore_user_abort(false);

        # - ###

        $VK = new self();
        $VK->setOpt_ParseSleepTime(2000000);
        $VK->setOpt_Token_fromAUTH();

        # - ###

        $RESULT = $VK->apiWallGet_PostsLast($idOrDomain,100,$maxPosts,$offsetShift);

        # - ###
		dump('Скачал рав посты.  Теперь цикл подготовки инфы.');
        # - ###
	    #dump(1111);
	    
	    try{
	    	# ERROR = Тут может быть безлоговый вылет, ничем не ловится.
		    #  98% чот дело в нехватке памяти или конфликтах записи и тд.
		    #  Ибо 1000 разом не дал.  а 500+500 норм.
	        # file_put_contents('raw='.$fileNameJson,json_encode($RESULT, JSON_PRETTY_PRINT));
	    }catch(\Throwable $e){ TryCatcher::ddOnTryCatch($e,'ТС при записи резервного рава в файл'); }
	    
	    #dump(2222);
	    try{
	    	
	        $FIN = [];
	        foreach( $RESULT as $key => $val )
	        {
	            # - ###
	            $FIN[$key] = $VK->prepareOnePostInfo($val);
	            #foreach( $FIN[$key]['ATTACH']['IMAGES_MAXED']??[] as $one ) EasyFront::echoTag_IMG($one['url'],300,300);
	            # - ###
	        }
        
	    }catch(\Throwable $e){ TryCatcher::ddOnTryCatch($e,'ТС в цикле обработки равов'); }
		
	    dump('Цикл кончился, пишу в файл.');

        # - ###
	    # Ручная доп обработка
		
	    
	    
	    
        # - ###

	    if( $retMode === 'FILE' )
            file_put_contents($fileNameJson,json_encode($FIN, JSON_PRETTY_PRINT));
		else
			return $FIN;
	    
        dump('Всего постов в жсоне: '.count($FIN));
        dump(current($FIN));
        #dd(array_keys($FIN));
        #dd($FIN,json_encode($FIN));
    }

    # NOTE: Папку создавать руками, в public
    public static function actionBig_downloadPics($fileNameJson, $folderForPics,$onlyWithOnePic=false)
    {
        set_time_limit(1800); # 30мин

        $intervalMSec = 200000;  # 200 - норм    100-тормозит курл
        

        $ARR = json_decode(file_get_contents($fileNameJson),true);

        # - ###

        $allImgCount = 0;
        foreach( $ARR as $post )
        {
            if( ! $post['ATTACH_HAS'] ) continue;
            if( ! isset($post['ATTACH']['IMAGES_MAXED']) ) continue;
            if( ! count($post['ATTACH']['IMAGES_MAXED']) ) continue;
            if( $onlyWithOnePic ) if( count($post['ATTACH']['IMAGES_MAXED']) !== 1 ) continue;

            $allImgCount += count($post['ATTACH']['IMAGES_MAXED']);
        }
	
	    $loadedCnt = count(Patcher::getFilePathsArr_JPG($folderForPics));
        $loadTodo = ($allImgCount-$loadedCnt);
        $loadTodoTimeSec = ($loadTodo*$intervalMSec/1000000);
	    
        dump("Всего пикч в массиве (С учетом параметра одиночных): {$allImgCount}");
        dump("Уже скачано: {$loadedCnt}");
        dump("Осталось: {$loadTodo}");
        dump("Ожид время: ".$loadTodoTimeSec.' сек');
        dump("Ожид время: ".($loadTodoTimeSec/60).' мин');
		#dd();
  
  
		#dd('dd');
        # - ###
		
	    dump(current($ARR));
	    
        $iLoaded = 0;
        foreach( $ARR as $post )
        {

            if( ! $post['ATTACH_HAS'] ) continue;
            if( ! isset($post['ATTACH']['IMAGES_MAXED']) ) continue;
            if( ! count($post['ATTACH']['IMAGES_MAXED']) ) continue;
            if( $onlyWithOnePic ) if( count($post['ATTACH']['IMAGES_MAXED']) !== 1 ) continue;
            
            if( in_array($post['META_POST_URL_NUMS'],
                [ # NOTE: Ручной скип ссылок с битыми пикчами.
                    '111514239_428712',
                    '111514239_359349',
                    '111514239_359348',
                    '111514239_353605',
                    '111514239_353602',
                    '111514239_353600',
                    '',
                    '',
                ]) )
            {
                dump('Ручной скип - '.$post['META_POST_URL_WALL']);
                continue;
            }

            $fileName_begin = $post['META_FILE_NAME'];

            $imgAmount = (count($post['ATTACH']['IMAGES_MAXED']) >= 2);

            foreach( $post['ATTACH']['IMAGES_MAXED'] as $id => $imgInfo )
            {
                #EasyFront::echoTag_HR_Red();

                $imgWH  = str_pad($imgInfo['WxH'],9,'_');
                $imgURL = $imgInfo['url'];

                # - ###

	            $cntAll = count($post['ATTACH']['IMAGES_MAXED']);
	            $cntCurr = $id+1; # TESTING
	            
                if( $imgAmount )
                    $imgFullName = $fileName_begin.$imgWH." {$cntCurr}из{$cntAll} #.jpg";
                else
                    $imgFullName = $fileName_begin.$imgWH." #.jpg";

                $fullPath = "{$folderForPics}/$imgFullName";

                #dd($post,$fullPath);

                # - ###

                if( file_exists($fullPath) ) # WORK
                {
                	EasyFront::echoTag_A_Button(public_path($fullPath),true,''.$post['META_POST_URL_WALL']);
                	#dd(123);
                    #echo('Файл уже есть = '.$fullPath.'<br>');
                    continue;
                }

                # - ###

                #echo "Скачиваю = ";
                #EasyFront::echoTag_A($imgURL,true,'Ссылка');
                #echo '<br>';
                #flush();

                # - ###

                try {
                    $CH = new RequestCURL();

                    $CH->setOpt_Main_GET($imgURL);


                    $RES = $CH->action_ExecGetAnswer();

                    if( $RES['IS_ERROR'] )
                    {   # Ошибка
                        dump("iLoaded = {$iLoaded}");
                        
                        dump('Вылет в курле при скачке',
	                        'Если по лимиту времени, это завис инет => релоад и заработает'
	                        ,$RES['INFO']['CURL']['total_time']);
	                    
                        #TryCatcher::dumpOnTryCatch($e,'Вылет при скачке');
	
	                    if( $RES['INFO']['CURL']['total_time'] > 9 )
		                    EasyFrontJS::echoJS_ReloadPage_PHP_WithDump(5);

	                    dd($RES);
                    }


                    # Запрос успешен, но вк послал
                    if( $RES['HTTP_CODE'] !== '200' )
                    {
                        dump("iLoaded = {$iLoaded}");
                        dump('При скачке код !== 200',$post,$imgURL,$RES);
                        
                        if( $RES['HTTP_CODE'] === '404' )
                        {
	                        dump('404 = Скипаю и иду дальше');
	                        dump($post['META_POST_URL_NUMS']);
	                        continue;
                        }
                        else
                        {
	                        dd('НЕ 404 = ДД');
                        }
                    }


                    #$data = file_get_contents($imgURL);


                    file_put_contents($fullPath,$RES['ANSWER_TEXT'][0]);


                    #$localPath = "\\{$folderForPics}\\$imgFullName";
                    #dd(public_path($localPath));
                    #$localPath = public_path($localPath);
                    #$localPath = urlencode($localPath);
                    #$localPath = str_replace('%23','#',$localPath);
                    #$localPath = str_replace('#','%23',$localPath);
                    #EasyFront::echoTag_IMG($localPath,300,300);

                    EasyFront::echoTag_IMG_RawData_RawText($RES['ANSWER_TEXT'][0],
                        'jpeg',300);

                    #dump(base64_encode($RES['ANSWER_TEXT'][0]));
                    #dd($RES);

                    #dd($RES);

                }catch(\Throwable $e)
                    {
                    	dump("iLoaded = {$iLoaded}");
	                    TryCatcher::dumpOnTryCatch($e,'Вылет при скачке');
	                    
	                    if( $RES['INFO']['CURL']['total_time'] > 9 )
	                        EasyFrontJS::echoJS_ReloadPage_PHP_WithDump(5);
                    }

                $iLoaded++;

                #echo ("Сплю {$intervalMSec}мс <br>");
                usleep($intervalMSec);
                flush();

                #EasyFront::echoJS_ScrollToBottomFast();

            } # Пикчи



        } # Посты

    }

    # WORK
    public static function actionBig_rePrepareJson($fileNameJson)
    {
        $ARR = json_decode(file_get_contents($fileNameJson),true);


        $VK = new self();

        $FIN = [];
        foreach( $ARR as $key=>$post )
        {

            $res = $VK->prepareOnePostInfo(json_decode($post['RAW_JSON'],true));
            $FIN[$key] = $res;
        }

        file_put_contents('REG = '.$fileNameJson, json_encode($FIN,JSON_PRETTY_PRINT));

        dd('done');
    }
	
	
	/** Вытащить из поста ID всех лайкнувших юзеров. В цикле, по 100шт за раз.
	 * @param int $groupIdOnly ID целевой группы
	 * @param int $postId ID целевого поста
	 * @return array Массив с ID юзеров ИЛИ с ошибкой вк
	 * @version WORK
	 */
	public function actionBig_getLikesListFull( int $groupIdOnly , int $postId)
	{
		#$maxLimit = 600;
		
		
		$arrIds = [];
		
		$offset = 0;
		#$countLoaded = 0;
		for( ; ; )
		{
			$RES = $this->apiLikes_GetList_ByPost($groupIdOnly,$postId,100,$offset);
			
			if($RES['IS_ERROR'])
				return $RES;
			
			$ids = $RES['RESP_CURL']['ANSWER_JSON']['response']['items'];
			$countAll = $RES['RESP_CURL']['ANSWER_JSON']['response']['count'];
			
			$arrIds = array_merge($arrIds,$ids);
			
			if( $countAll <= 100 ) # Если в посте меньше 100шт, то 2 запрос уже не нужен.
				break;
			
			if( count( $arrIds ) >= ($countAll-10) )
				break;  # -10 чтоб наверняка
			
			
			$offset += 100;
			
			dump('Много лайков - '.$countAll);
			
			Sleeper::sleeper(0.2,__METHOD__,true);
			#usleep(0.2);
		}
		
		
		return $arrIds;
	}
	
	
	# WORK
	public function captcha_echoSelfSendForm($RES)
	{
		if( $RES['IS_CAPTCHA'] )
		{
			$c_img = $RES['RESP_JSON']['captcha_img'];
			$c_sid = $RES['RESP_JSON']['captcha_sid'];
			
			EasyFront::echoTag_HrROW_PreDef_GradDEV();
			EasyFront::echoTag_IMG($c_img,'125');
			EasyFront::echoTag_Button_Copy($c_sid);
			
			$target = $_SERVER['REQUEST_URI'] ?? '/';
			echo '"Куда='.$target;
			echo '
                <form method="GET" action="'.$target.'"><br>
                    <input type="text" name="VK_C_SID" value="'.$c_sid.'"><br>
                    <input type="text" name="VK_C_KEY" value="" placeholder="Ответ"><br>
                    <button type="submit">Отправить</button>
                </form>';
		}
	}
	
	# WORK
	public function captcha_setAnswerIfNeed_GET()
	{
		$sid = $_GET['VK_C_SID'] ?? '';
		$key = $_GET['VK_C_KEY'] ?? '';
		
		$this->setCaptchaAnswer($sid , $key);
	}
	
	
	
	
	# - ### ### ###
    #   NOTE: Вторичное
	
	/** Выкачка любого количества постов из группы, циклчески.
	 * @param $idOrDomain int|string Целевая группа
	 * @param $step int По сколько постов качат за запрос 1-100
	 * @param $offsetMax int До какого максимального внутреннего номера поста качать (Например до 500)
	 * @param $offsetShift int С какого внутреннего номера поста начать (Например с 300)
	 * @return array Массив готовых постов или ДД с ошибкой+лог в файл
	 * @example func(ID,20,100,60) = Выкачать посты с 60 по 100 с шагом 20. = 60-80 и 80-100, 2 запроса.
	 * @version WORK
	 */
	public function apiWallGet_PostsLast($idOrDomain, $step, $offsetMax, $offsetShift)
    {
        $FIN_POSTS = [];

        $iMax = ceil($offsetMax/$step);
        dump("iMax = $iMax");


        # is_pinned" => 1   может не быть


        for( $i=0 ; $i < $iMax ; $i++ )
        {
            #EasyFront::echoTag_BR_3(); flush();
            EasyFront::echoTag_HrROW_GradRand(); flush();
            $offset  = $i*$step;
            $offset += $offsetShift;

            dump("I={$i}  Offset=$offset   Count=$step  ==> Посты {$offset}-".($offset+$step) );

            # translation_for_the_soul
            $RESULT = $this->apiWallGet_Posts($idOrDomain,$step,$offset);
            #dump($RESULT);
            dump($RESULT['RESP_CURL']);


            $respItemsCount = count($RESULT['RESP_JSON']);
            if( $respItemsCount === 0 )
            {
                dump('Число вернувшихся итемов = 0    Выхожу из цикла');
                break;
            }

            foreach($RESULT['RESP_JSON'] as $val)
                $FIN_POSTS['ID='.$val['id']] = $val;   # TODO: Потом тут сразу подготовщик поста

            if($RESULT['IS_ERROR'])
            {
                # NOTE: Обязательно спасать данные ибо там могут быть тысячи уже готовых постов.
                file_put_contents('VK=OnErrorDump=Posts.json',json_encode($FIN_POSTS,JSON_PRETTY_PRINT));
                file_put_contents('VK=OnErrorDump=LastResultWithError.json',json_encode($RESULT));
                dd('IS_ERROR',$RESULT,$FIN_POSTS,json_encode($FIN_POSTS));
                # NOTE: Вкладка может вылететь из-за много инфы.
            }


            dump("Сплю {$this->loadPosts_sleepMs} мс"); flush();
            Sleeper::sleeper($this->loadPosts_sleepMs/1000000);
            #usleep($this->loadPosts_sleepMs);
        }

        EasyFront::echoTag_BR_3(); flush();
        EasyFront::echoTag_HR_Red(); flush();
        Ancii::success();
        return $FIN_POSTS;
    }


    
	/** Вытащить из сырого массива с информацией о посте максимум информации и красиво оформить.
	 * @param $postArr array Сырой массив инфы о 1 посте, полученный от вк.
	 * @return array Крупный капсовый асоц массив со всей инфой
	 * @version WORK
	 */
    public function prepareOnePostInfo($postArr)
    {
        $INFO = [];
        $val  = $postArr; # Костыль
        # - ###

        $INFO['RAW_JSON'] = json_encode($postArr);


        $INFO['STAT_VIEWS_HAS'] = isset($val['views']['count']);

        if( $INFO['STAT_VIEWS_HAS'] )
        {
            $INFO['STAT_VIEWS'] = $val['views']['count'];
            if( count($val['views'])>=2) dump($val['views'] );
        }
        else
        {
            $INFO['STAT_VIEWS'] = 0;
        }

        #if( ! isset($val['likes']) ) dd($postArr);

        $INFO['STAT_LIKES']      = $val['likes']['count'];
        $INFO['STAT_LIKES_USER'] = $val['likes']['user_likes'];

        if( ($INFO['STAT_LIKES'] != 0) && ($INFO['STAT_VIEWS'] != 0) )
            $INFO['STAT_LIKES_PERCENT'] = (string)(int) floor(($INFO['STAT_LIKES']/$INFO['STAT_VIEWS'])*100);
        else
            $INFO['STAT_LIKES_PERCENT'] = '0';

        $lp = $INFO['STAT_LIKES_PERCENT'];
        if( ($lp == 0) ) $INFO['STAT_LIKES_TIER'] = 'TIER_0';
        if( ($lp >   0) && ($lp <  5) ) $INFO['STAT_LIKES_TIER'] = 'TIER_0-5';
        if( ($lp >=  5) && ($lp < 10) ) $INFO['STAT_LIKES_TIER'] = 'TIER_5-10';
        if( ($lp >= 10) && ($lp < 15) ) $INFO['STAT_LIKES_TIER'] = 'TIER_10-15';
        if( ($lp >= 15) && ($lp < 20) ) $INFO['STAT_LIKES_TIER'] = 'TIER_10-15';
        if( ($lp >= 15) && ($lp < 20) ) $INFO['STAT_LIKES_TIER'] = 'TIER_15-20';
        if( ($lp >= 20) && ($lp < 25) ) $INFO['STAT_LIKES_TIER'] = 'TIER_20-25';
        if( ($lp >= 25) && ($lp < 30) ) $INFO['STAT_LIKES_TIER'] = 'TIER_25-30';
        if( ($lp >= 30) ) $INFO['STAT_LIKES_TIER'] = 'TIER_30+';


        # BUG: Их может быть 0

        $INFO['STAT_COMMENTS_HAS'] = ($val['comments'] > 0 );
        $INFO['STAT_COMMENTS']     = $val['comments']['count'];



        $INFO['STAT_REPOSTS_HAS'] = ($val['reposts']['count'] > 0 );
        $INFO['STAT_REPOSTS_ALL']  = $val['reposts']['count'];
        $INFO['STAT_REPOSTS_WALL'] = $val['reposts']['wall_count'] ?? -1; # NOTE: Не будет в
        $INFO['STAT_REPOSTS_MSG']  = $val['reposts']['mail_count'] ?? -1; # NOTE: Не будет в
        $INFO['STAT_REPOSTS_USER'] = $val['reposts']['user_reposted'];



        # - ###
        $INFO['ATTACH_HAS'] = (count($val['attachments']) > 0 );
        $INFO['ATTACH'] = [];

        if($INFO['ATTACH_HAS'])
        {
            foreach ($val['attachments'] as $one)
            {
                switch( $one['type'] )
                {
                    case 'photo':
                        #$IMG = [];

                        $sizeMaxKey = 0;
                        $sizeMaxHeight = $one['photo']['sizes'][0]['height'];
                        foreach( $one['photo']['sizes'] as $keyS => $sizeArr ) {
                            if ($sizeArr['height'] > $sizeMaxHeight) {
                                $sizeMaxKey = $keyS;
                                $sizeMaxHeight = $sizeArr['height'];
                            }
                        }

                        $a = $one['photo']['sizes'][$sizeMaxKey];
                        $a['DATE_PERFORM'] = date("Y-m-d H:i:s");
                        $a['WxH'] = "{$a['width']}x{$a['height']}";
                        $INFO['ATTACH']['IMAGES_MAXED'] []= $a;

                        break;

                    case 'doc':
                        $INFO['ATTACH']['DOCS_RAW_ALL'] []= $one;
                        break;

                    case 'link':
                        #$INFO['ATTACH']['LINK_RAW_ALL'] []= $one;
                        $INFO['ATTACH']['LINKS'] []= [
                            'TITLE'  => $one['title'] ?? '',
                            'URL'    => $one['url'] ?? '',
                            'TARGET' => $one['target'] ?? '',
                            'DESCR'  => $one['description'] ?? '',
                        ];
                        break;

                    case 'audio':
                        $INFO['ATTACH']['AUDIO_RAW_ALL'] []= $one;
                        break;

                    case 'market':
                        $INFO['ATTACH']['MARKET_RAW_ALL'] []= $one;
                        break;

                    case 'poll':
                        $INFO['ATTACH']['POLL_RAW_ALL'] []= $one;
                        break;

                    case 'video':
                        $INFO['ATTACH']['VIDEO_RAW_ALL'] []= $one;
                        break;

                    case 'album':
                        $INFO['ATTACH']['ALBUM_RAW_ALL'] []= $one;
                        break;

                    case 'event':
                        $INFO['ATTACH']['EVENT_RAW_ALL'] []= $one;
                        break;
					
                        case 'page':
                        $INFO['ATTACH']['PAGE_RAW_ALL'] []= $one;
                        break;


                    default:
                        $INFO['ATTACH']['ALL_OTHER_RAW'] []= $one;
                        dump($one);
                }

            }


        }

        # - ###

        $INFO['TEXT_HAS'] = (strlen($val['text'])>0);
        $INFO['TEXT_RAW'] = $val['text'];
        $INFO['TEXT_RAW_LEN'] = strlen($val['text']);
        $INFO['TEXT_BASE64'] = base64_encode( $val['text']);
        $INFO['TEXT_BASE64_LEN'] = strlen($INFO['TEXT_BASE64']);

        # - ###

        $INFO['META_POST_ID'] = (string)$val['id'];
        $INFO['META_GROUP_ID'] = $val['owner_id'];
        $INFO['META_GROUP_ID_STR'] = (string)($val['owner_id'] * -1);
        $INFO['META_POST_DATE_U'] = $val['date']+10800;
        $INFO['META_POST_DATE_T'] = gmdate("Y-m-d H:i:s",$val['date']+10800);
        $INFO['META_POST_DATE_T_DAY'] = explode(' ',$INFO['META_POST_DATE_T'])[0];
        $INFO['META_POST_DATE_T_TIME'] = explode(' ',$INFO['META_POST_DATE_T'])[1];

        $INFO['META_POST_URL_FULL'] = "https://vk.com/wall-{$INFO['META_GROUP_ID_STR']}_{$val['id']}";
        $INFO['META_POST_URL_WALL'] =                "wall-{$INFO['META_GROUP_ID_STR']}_{$val['id']}";
        $INFO['META_POST_URL_NUMS'] =                     "{$INFO['META_GROUP_ID_STR']}_{$val['id']}";

        $INFO['META_FILE_NAME'] = implode(' # ',[
            str_pad($INFO['META_POST_URL_WALL'],21,'_'),

            $INFO['META_POST_DATE_T_DAY'],

            "V-".str_pad($INFO['STAT_VIEWS'],6,'_').
            " L-".str_pad($INFO['STAT_LIKES'],6,'_').
            " P-".str_pad($INFO['STAT_LIKES_PERCENT'],2,'_')."%",

            str_pad($INFO['STAT_LIKES_TIER'],10,'_').' # '
        ]);


        return $INFO;

        # - ###
    }



    # - ### ### ###
    #   NOTE: Первичное
	
	# https://dev.vk.com/method/photos.getAll
	
	
	/** API-Запрос. Вытащить из поста ID лайкнувших юзеров.
	 * @param int $groupIdOnly ID целевой группы
	 * @param int $postId ID целевого поста
	 * @param int $count Сколько за раз, макс=100
	 * @param int $offset Сдвиг в общем списке.
	 * @return array Мой предобработанный массив
	 * @example https://dev.vk.com/method/likes.getList
	 * @version WORK
	 */
	public function apiLikes_GetList_ByPost( int $groupIdOnly , int $postId , $count=100 , $offset=0)
	{
		$method = 'likes.getList';
		$params['type'] = 'post';  #
		$params['owner_id'] = $groupIdOnly;
		$params['item_id'] = $postId;  #
		
		$params['skip_own'] = true;  # Без своих лайков
		
		$params['filter'] = 'likes';  #
		
		#$params['extended'] = 1;  # Робит = Расшир инфа о юзере.
		
		$params['count'] = $count;  # Макс 100
		$params['offset'] = $offset;  #
		
		
		$RES = $this->sendQueryVk_GET($method,$params);
		
		
		if( isset($RES['ANSWER_JSON']['response']) )
			return [
				'IS_ERROR' => false,
				'RESP_CURL' => $RES,
				'RESP_JSON' => $RES['ANSWER_JSON']['response']['items'],
			];
		
		return [
			'IS_ERROR' => true,
			'RESP_CURL' => $RES,
			'RESP_JSON' => $RES['ANSWER_JSON']['error'],
		];
		
	}



    # NOTE: Стоит фильтр - только авторские
    # https://dev.vk.com/method/wall.get
    public function apiWallGet_Posts($idOrDomain , $count , $offset=0)
    {
        $method = 'wall.get';
        $params['count'] = $count;  # Макс 100
        $params['filter'] = 'owner';  #
        $params['offset'] = $offset;  #

        if( is_string($idOrDomain) )
            $params['domain'] = $idOrDomain;
        else
            $params['owner_id'] = $idOrDomain;


        $RES = $this->sendQueryVk_GET($method,$params);



        if( isset($RES['ANSWER_JSON']['response']) )
            return [
                'IS_ERROR' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON']['response']['items'],
            ];


        return [
            'IS_ERROR' => true,
            'RESP_CURL' => $RES,
            'RESP_JSON' => $RES['ANSWER_JSON']['error'],
        ];

    }
    # {"error":{"error_code":5,"error_msg":"User authorization failed: no access_token passed.","request_params":[{"key":"method","value":"wall.get"},{"key":"oauth","value":"1"}]}}
	
	
	
	# Для бота сообщества
	# TESTING WORK
	# https://dev.vk.com/method/messages.send
	public function apiMessagesSend_SendOneForGroupBot( int $peer_idForThisGroup , string $msgText='EMPTY' , $attachArrVkUrls=[] )
	{
		$method = 'messages.send';
		
		$params = [
			'random_id' => 0, # Проверка отключена
			'peer_id' => $peer_idForThisGroup, #2000000002
		];
		
		if( $msgText !== 'EMPTY' )
			$params['message'] = $msgText;
		
		if( $attachArrVkUrls !== [ ] )
		{
			foreach($attachArrVkUrls as &$one )
				$one = str_replace('https://vk.com/','',$one);
			
			$params['attachment'] = implode(',',$attachArrVkUrls); # {type}{owner_id}_{media_id}, ...
		}
		
		$RES = $this->sendQueryVk_GET($method,$params);
		
		if( isset($RES['ANSWER_JSON']['response']) )
			return [
				'IS_ERROR' => false,
				'RESP_CURL' => $RES,
				'RESP_JSON' => $RES['ANSWER_JSON']['response'],
			];
		
		return [
			'IS_ERROR' => true,
			'RESP_CURL' => $RES,
			'RESP_JSON' => $RES['ANSWER_JSON']['error'],
		];
		
	}
	


    # WORK   TODO
    # https://dev.vk.com/method/wall.post
    public function apiWallPost_MakePost( $idOnly , $msgText='EMPTY' , $attachArrVkUrls=[] , $timeDtMSK='NOW' )
    {
        $method = 'wall.post';

        $params['owner_id'] = $idOnly;
        $params['from_group'] = 1; # От имени группы

        # - ###

        if( $msgText !== 'EMPTY' )
            $params['message'] = $msgText;

        if( $attachArrVkUrls !== [ ] )
        {
            foreach($attachArrVkUrls as &$one )
                $one = str_replace('https://vk.com/','',$one);

            $params['attachments'] = implode(',',$attachArrVkUrls);
        }

        if( $timeDtMSK !== 'NOW' )
            $params['publish_date'] = strtotime($timeDtMSK)-10800;

        #dd($params);
        # - ###

        $RES = $this->sendQueryVk_GET($method,$params);

        # - ###

        # Чтоб увидеть капчу
        #if( ! isset($RES['ANSWER_JSON']['response']['post_id']) )
        #    dump($RES['ANSWER_JSON']);

        # - ###

        try{

            if( isset($RES['ANSWER_JSON']['response']) )
                return [
                    'IS_ERROR' => false,
                    'IS_SUCCESS' => true,
                    'IS_CAPTCHA' => false,
                    'RESP_CURL' => $RES,
                    'RESP_JSON' => $RES['ANSWER_JSON']['response']['post_id'],
                ];

            if( isset($RES['ANSWER_JSON']['error']['error_code']) ) # WORK
                if( $RES['ANSWER_JSON']['error']['error_code'] === 14 )
                    return [
                        'IS_ERROR' => false,
                        'IS_SUCCESS' => false,
                        'IS_CAPTCHA' => true,
                        'RESP_CURL' => $RES,
                        'RESP_JSON' => $RES['ANSWER_JSON']['error'],
                        #'CAPTCHA' => [ '1'=>'', '2'=>'' ],
                    ]; # */


            return [
                'IS_ERROR' => true,
                'IS_SUCCESS' => false,
                'IS_CAPTCHA' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON']['error'],
            ];

        }catch( \Throwable $e){
            TryCatcher::ddOnTryCatch($e, 'Вк-постер вылет на return');
        }

        # - ###
    }

    # WORK
    # https://dev.vk.com/method/groups.getById
    public function apiGroupGet_AllInfo( $idOrDomain )
    {
        $method = 'groups.getById';

        $params['group_id'] = $idOrDomain;

        $params['fields'] =[
                'activity','age_limits','can_message','can_suggest','contacts','counters','country',
                'cover','description','links','members_count','status','trending','verified'
            ]; #

        $RES = $this->sendQueryVk_GET($method,$params);

        if( isset($RES['ANSWER_JSON']['response']) )
            return [
                'IS_ERROR' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON']['response'][0],
            ];

        return [
            'IS_ERROR' => true,
            'RESP_CURL' => $RES,
            'RESP_JSON' => $RES['ANSWER_JSON']['error'],
        ];

    }

	# WORK
	# https://vk.com/dev/photos.getById
	public function apiPhotoGet_InfoWithSizesArr($photosFullId)
	{
		$method = 'photos.getById';
		
		$params['photo_sizes'] = 1;
		$params['photos'] = $photosFullId; # NOTE: !!! с минусом в начале для групп.  Можно через запятую.
		
		
		$RES = $this->sendQueryVk_GET($method,$params);
		#dump($RES);
		
		if( isset($RES['ANSWER_JSON']['response']) )
		{
			return [
				'IS_ERROR' => false,
				'RESP_CURL' => $RES,
				'RESP_JSON' => $RES['ANSWER_JSON']['response'], # [0]['sizes']
			];
		}
		
		return [
			'IS_ERROR' => true,
			'RESP_CURL' => $RES,
			'RESP_JSON' => $RES['ANSWER_JSON']['error'],
		];
		
	}
	
	# WORK
	# Отпарсить массив размеров картинки и вернуть 1 самый большой.
	public function parsePictureSizesArr_GetMaxImgArr( $arrRawSizes )
	{
		#$arrRawSizes = $RES['ANSWER_JSON']['response'][0]['sizes'];
		
		$sizeMaxKey = 0;
		$sizeMaxHeight = $arrRawSizes[0]['height'];
		
		foreach( $arrRawSizes as $keyS => $sizeArr )
		{
			if ($sizeArr['height'] > $sizeMaxHeight)
			{
				$sizeMaxKey = $keyS;
				$sizeMaxHeight = $sizeArr['height'];
			}
		}
		
		return $arrRawSizes[$sizeMaxKey];
	}
	


    # - ### ### ###
    #   NOTE: Низкоуровневое | Базовые

    public $captcha = ['SID'=>'','KEY'=>''];
    public function setCaptchaAnswer($sid,$key)
    {
        $this->captcha = ['SID'=>$sid,'KEY'=>$key];

        # NOTE https://dev.vk.com/api/captcha-error

        /*
            RESP_JSON" => array:7 [▼
            "error_code" => 14
            "error_msg" => "Captcha needed"
            "request_params" => array:8 [▶]
            "captcha_sid" => "766216789266"
            "captcha_img" => "https://api.vk.com/captcha.php?sid=766216789266&s=1"
            "captcha_ts" => 1682624409.858
            "captcha_attempt" => 1
          ]
            */

    }
    
	public $postDonut = false;
	public $postDonutText = '🍩 🍩 🍩';
	public function setPostIsDonut_Only(){ $this->postDonut = -1; }
	public function setPostIsDonut_Day1(){ $this->postDonut = 86400; }
    
    public function addUnivUrlParams( &$dataQuery )
    {
        $opts = [
            'access_token' => $this->token,
            'v' => $this->versionVk,
            'oauth' => '1', # Сам добавил
        ];

        if( ! empty($this->captcha['KEY']) )
        {
            $opts['captcha_sid'] = $this->captcha['SID'];
            $opts['captcha_key'] = $this->captcha['KEY'];
            $this->captcha = ['SID'=>'','KEY'=>'']; # Зануляю обратно
        }
        
        if( $this->postDonut !== false )
        {
	        $opts['donut_paid_duration'] = $this->postDonut;
	        $this->postDonut = false; # Зануляю обратно
        }
        
        $dataQuery = array_merge($dataQuery,$opts);
    }

    public function sendQueryVk_POST( $method , $dataQuery=[] )
    {
        $paramsUrlArr = [];
        $this->addUnivUrlParams($paramsUrlArr);

        $RES = RequestCURL::POST_Multi("https://api.vk.com/method/{$method}/",
            $paramsUrlArr, $dataQuery);

        if( $RES['IS_ERROR'] )
            dd($RES);

        return $RES;
    }

    public function sendQueryVk_GET( $method , $dataQuery=[] )
    {
        $this->addUnivUrlParams($dataQuery);

        $RES = RequestCURL::GET("https://api.vk.com/method/{$method}",$dataQuery);

        if( $RES['IS_ERROR'] )
            dd($RES);

        return $RES;
    }


    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE: Свалка.

    # WORK 3 Нафиг
    public function apiPhotos_1_GetUploadURL( $idOrDomain )
    {
        $method = 'photos.getWallUploadServer';
        $params['from_group'] = '1';  #

        if( is_string($idOrDomain) )
            $params['domain'] = $idOrDomain;
        else
            $params['owner_id'] = $idOrDomain;


        $RES = $this->sendQueryVk_POST($method,$params);

        #dd($RES);
        /*
            "album_id" => -14
            "upload_url" => "https://pu.vk.com/c518136/ss2220/upload.php?act=do_add&mid=1870..."
            "user_id" => 187086026    # Мой id
        */

        if( isset($RES['ANSWER_JSON']['response']) )
            return [
                'IS_ERROR' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON']['response'],
            ];


        return [
            'IS_ERROR' => true,
            'RESP_CURL' => $RES,
            'RESP_JSON' => $RES['ANSWER_JSON']['error'],
        ];

    }
    public function apiPhotos_2_UploadIMG( $uploadUrl , $picPath )
    {
        #$curl_photo = curl_file_create($picPath);
        #$curl_photo = file_get_contents($picPath);
        #dd($curl_photo);

        $RES = $this->sendQueryVk_POST('123123',
            ['photo'=>curl_file_create($picPath)],$uploadUrl);

        /*
            "server" => 228131
            "photo" => "[ много ]"
            "hash" => "c632e0977de88760e1a3e6721baa73e7"
        */

        if( isset($RES['ANSWER_JSON']['hash']) )
            return [
                'IS_ERROR' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON'],
                'RESP_JSON_PIC' => json_decode($RES['ANSWER_JSON']['photo'],true)[0],
            ];

        return [
            'IS_ERROR' => true,
            'RESP_CURL' => $RES,
            'RESP_JSON' => $RES['ANSWER_JSON']['error'],
        ];

    }
    public function apiPhotos_3_SaveUploadIMG( $idOrDomain , $paramsArr )
    {
        $method = 'photos.saveWallPhoto';

        #if( is_string($idOrDomain) )
        #    $params['domain'] = $idOrDomain;
        #else
        #    $params['owner_id'] = $idOrDomain;


        $RES = $this->sendQueryVk_POST($method,$paramsArr);

        #dd($RES);
        /*
             "response" => array:1 [▼
                0 => array:8 [▼
                  "album_id" => -14
                  "date" => 1682203745
                  "id" => 457259569
                  "owner_id" => 187086026
                  "access_key" => "85ef78ec6913021"
                  "sizes" => array:10 [▼]
                  "text" => ""
                  "has_tags" => false
                ]
              ]
        */

        if( isset($RES['ANSWER_JSON']['response']) )
            return [
                'IS_ERROR' => false,
                'RESP_CURL' => $RES,
                'RESP_JSON' => $RES['ANSWER_JSON']['response'],
            ];


        return [
            'IS_ERROR' => true,
            'RESP_CURL' => $RES,
            'RESP_JSON' => $RES['ANSWER_JSON']['error'],
        ];

    }
    # https://dev.vk.com/method/photos.saveWallPhoto
    # https://dev.vk.com/api/upload/wall-photo

    # NOTE: Не юзать, нафиг
    public static function actionBig_MakePost()
    {

        dd('Нафиг - гемор и капчи.  Проще залить руками в альбом и оттуда прикреплять');

        # NOTE: Сработал 1 раз, походу нельзя добавлять атрибуты group_id и тп, иначе сломается хеш
        #  БУДУТ капчи.


        $POST = [
            'GROUP' => 'awc_capital', # id или домен, можно    -188937898
            'TEXT' => 'api test',
            'ATTACH_PICS' => [
                [ 'URL'=>'TEST-PIC=ms-icon-144x144.png', 'DESC'=>'123' ],
                # [ 'URL'=>'', 'DESC'=>'' ],
            ],
            'TIME' => 'NOW',
        ];

        EasyFront::echoTag_IMG('TEST-PIC=ms-icon-144x144.png');


        # Эталон = https://vk.com/photo-188937898_457239025
        # Эталон = https://vk.com/photo-188937898_187086026  187086026

        # TODO: Проверка что все пикчи доступны

        #dd(123);
        $VK = new apiVkCom();


        $groupId = 188937898;  #'awc_capital'
        $picName = 'abstract-1.jpg';  # 'TEST-PIC=ms-icon-144x144.png'

        # - ### Получение ссылки
        $uploadUrlInfo = $VK->apiPhotos_1_GetUploadURL($groupId);
        /*
        "album_id" => -14
        "upload_url" => "https://pu.vk.com/c228331/ss2032/upload.php?act=do_add&mid=187..."
        "user_id" => 187086026
        */
        dump($uploadUrlInfo);


        # - ### Отправка объекта изображения по полученной ссылке
        $uploadPicInfo = $VK->apiPhotos_2_UploadIMG($uploadUrlInfo['RESP_JSON']['upload_url'],
            $picName);
        #$curl_photo = curl_file_create('TEST-PIC=ms-icon-144x144.png');


        /*
          "server" => 228131
          "photo" => "[ много ]"
          "hash" => "c632e0977de88760e1a3e6721baa73e7"
        */

        dump($uploadPicInfo);

        # - ###  получение информации о картинке с сервера

        $params = [
            'photo' => $uploadPicInfo['RESP_JSON']['photo'],
            #'photo' => curl_file_create('TEST-PIC=ms-icon-144x144.png'),
            #'photo' => 'TEST-PIC=ms-icon-144x144.png',
            'server' => $uploadPicInfo['RESP_JSON']['server'],
            'hash' => $uploadPicInfo['RESP_JSON']['hash'],

            'group_id' => $groupId,
            'caption' => 'Test API',  # Необяз - описание
        ];

        $uploadPicInfo_2 = $uploadPicInfo['RESP_JSON'];
        #$uploadPicInfo_2['group_id'] = $groupId;
        #$uploadPicInfo_2['caption'] = 'Test API';


        $uploadPicInfo_3 = $VK->apiPhotos_3_SaveUploadIMG($groupId,$uploadPicInfo_2);

        dump($uploadPicInfo_3);


        $codeQueryImage = "photo{$uploadPicInfo_3['RESP_JSON'][0]['owner_id']}_{$uploadPicInfo_3['RESP_JSON'][0]['id']}";

        dump($codeQueryImage);
        dump('https://vk.com/'.$codeQueryImage);
        # Не открывает

        dd('END');
    }



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
