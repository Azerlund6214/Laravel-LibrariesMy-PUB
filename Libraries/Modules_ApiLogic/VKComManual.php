<?php

namespace LibMy;



/**
 * Крупные практически применимые действия через VK-API.
 * Предполагается ручной контроль за кодом в методах и настройками.
 * Просто вынес все такие методы в отдельный класс.
 */
class VKComManual
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	
	# - ### ### ###
	#   NOTE:
	
	# Массовый слив постов из массива групп + обработки.
	public static function vkMassSlivPostov()
	{
		
		# - ### ### ### ### ###
		#   ### 1 - Выкачка
		/* # = # = # ^/
		# NOTE: Теперь тупо жать ф5 и он сам определит следующий интервал.
		
		#$code = 'POZITIV';
		
		$domainsList = [
			#'домены',
			#'',
		];
		
		$domainsList = self::getMlpGroupsArr();
		
		#$domain = -123123;  # ID пихать числом с -
		
		$intervalMy = [
			'0-500'    ,  '500-1000',
			#'1000-1500', '1500-2000',
			#'2000-2500', '2500-3000',
			#'3000-3500', '3500-4000',
			#'4000-4500', '4500-5000',
			#'5000-5500', '5500-6000',
		];
		
		foreach($domainsList as $domain)
			foreach($intervalMy as $intOne)
			{
				$buf = explode('-',$intOne);
				$countLoad = $buf[1]-$buf[0];
				$offset = $buf[0];
				
				
				$code = strtoupper($domain);
				$fileNameFull = "{$code} = {$domain} = {$intOne} = {$countLoad}шт = Posts Prepared.json";
				
				if( file_exists($fileNameFull) )
				{
					dump('Уже есть = скип = '.$fileNameFull);
					continue;
				}
				dump('Качаю = '.$fileNameFull);
				
				#dd(123);
				# NOTE: Фулл жкшен
				apiVkCom::actionBig_getLastPosts($countLoad,$domain,
					$fileNameFull,$offset);
				
				#dd(123);
				#dump('Слип 5 и авторелоад через жс'); flush();
				#sleep(5);
				#EasyFrontJS::echoJS_ReloadPage();
				
				#EasyFrontJS::echoJS_ReloadPage_PHP_WithDump(5);
			}
		
		
		dump('Все скачано');
		
		/* # = # = # */
		
		
		# - ### ### ### ### ###
		#   ### 2 - Сгреб в 1 жсон
		/* # = # = # ^/
		#dd(FilePathsGrabber::getPathsArr_JSON(public_path()) );
		$FIN_JSON_ALL = [ ];   ini_set('memory_limit','512M');
		foreach( FilePathsGrabber::getPathsArr_JSON(public_path()) as $jsonPathOne )
			$FIN_JSON_ALL = array_merge($FIN_JSON_ALL,FileJsoner::getBase_FullAsArray($jsonPathOne));
		file_put_contents('- ALL.json',json_encode($FIN_JSON_ALL, JSON_PRETTY_PRINT));
		dd(count($FIN_JSON_ALL));
		/* # = # = # */
		
		
		# - ### ### ### ### ###
		#   ### 3 - Выкачка по жсону
		/* # = # = # ^/
	    ini_set('memory_limit','512M');
		apiVkCom::actionBig_downloadPics(
		    'POZITIV = -123123123 = 0-5000 = 5000шт = Posts Prepared.json',
		    'POZITIV', true);
		/* # = # = # */
		
		# - ### ### ### ### ###
		#   ### Сгребание в 1 жсон, для лайков.
		/* # = # = # ^/
		ini_set('memory_limit','512M');
		#dd(FilePathsGrabber::getPathsArr_JSON(public_path().'/JSON') );
	    $FIN_JSON_ALL = [ ];   ini_set('memory_limit','512M');
	    
	    foreach( FilePathsGrabber::getPathsArr_JSON(public_path().'/JSON') as $jsonPathOne )
	    {
		    $arr = FileJsoner::getBase_FullAsArray($jsonPathOne);
	    	
		    $groupDomain = explode(' = ',$jsonPathOne)[1];
		    
		    foreach( $arr as $key=>$one )
		    {
		    	$keyNew = $groupDomain.'='.$key;
		    	$valNew = [
				    'GROUP_ID' => $one['META_GROUP_ID'],
				    'GROUP_DOM' => $groupDomain,
				    'POST_URL' => $one['META_POST_URL_FULL'],
				    'POST_ID' => $one['META_POST_ID'],
				    'DT_T' => $one['META_POST_DATE_T'],
				    'DT_U' => $one['META_POST_DATE_U'],
				    'STAT_LIKES' => $one['STAT_LIKES'],
				    'STAT_VIEWS' => $one['STAT_VIEWS'],
			    ];
			    $FIN_JSON_ALL[$keyNew] = $valNew;
			    
		    }
		    
	    }
	    
	    dump(count($FIN_JSON_ALL));
        #dd();
	    file_put_contents('- ALL.json',json_encode($FIN_JSON_ALL, JSON_PRETTY_PRINT));
	    
	    dd('Все');
		/* # = # = # */
		
	}
	
	
	# - ### ### ###
	#   NOTE:
	
	# WORK
	# Статы группы по дням.   РУКАМИ
	public static function mainTestFunc_vkGroupDayStat(  )
	{
		set_time_limit(6000); # 100мин
		ignore_user_abort(false);
		ini_set('memory_limit','256M');
		
		$VK = new apiVkCom();
		$VK->setOpt_Token_fromAUTH();
		# - ###
		
		
		#dd(__FUNCTION__,'dd');
		
		$group = 'equestriawebm';
		$group = authTokenStrings::$VK_GROUPS['MLP']['ID'];
		
		
		# 100 постов  authTokenStrings::$VK_GROUPS['MIA_P']['ID']
		$POSTS_PREP = $VK->actionBig_getLastPosts(300,
			$group,1,0,123);
		#dump(json_encode($POSTS_PREP));
		
		dump(last($POSTS_PREP));
		#return;
		
		$FIN2 = [];
		$FIN = [];
		
		#$dateToday = $POSTS_PREP[array_key_first($POSTS_PREP)]['META_POST_DATE_T_DAY']; # Кривит если 100+ шт
		$dateToday = date("Y-m-d"); # 2023-04-24  '2023-07-24';
		$dateLast = last($POSTS_PREP)['META_POST_DATE_T_DAY'];
		dump($dateToday,$dateLast);
		
		foreach( $POSTS_PREP as $post )
		{
			$date = $post['META_POST_DATE_T_DAY'];
			
			if( in_array($date , [$dateToday,$dateLast]) )
				continue; # Скипаю сегодняшнюю и последнюю дату тк там не полные дни, часть постов дня обрезаны.
			
			$FIN2['ALL_LIKES'] []= $post['STAT_LIKES'];
			$FIN2['ALL_VIEWS'] []= $post['STAT_VIEWS'];
			
			if( ! isset($FIN[$date]) )
			{
				$FIN[$date]['POSTS'] = 0;
				$FIN[$date]['LIKES'] = 0;
				$FIN[$date]['VIEWS'] = 0;
			}
			
			$FIN[$date]['POSTS'] += 1;
			$FIN[$date]['LIKES'] += $post['STAT_LIKES'];
			$FIN[$date]['VIEWS'] += $post['STAT_VIEWS'];
		}
		
		sort($FIN2['ALL_LIKES']);
		sort($FIN2['ALL_VIEWS']);
		
		dump($FIN);
		dump($FIN2);
		
		# - ###
		
		$strArr = [];
		$mediana = (int)(count($FIN2['ALL_LIKES'])/2);
		$strArr []= 'Медиана штук постов= '.$mediana;
		$strArr []= 'Медиана лайков 1 поста = '.$FIN2['ALL_LIKES'][$mediana];
		$strArr []= 'Медиана просм  1 поста = '.$FIN2['ALL_VIEWS'][$mediana];
		
		foreach( $FIN as $date=>$data )
		{
			$strArr []= ("{$date} => P-{$data['POSTS']} V-{$data['VIEWS']} L-{$data['LIKES']} = ".((int)($data['LIKES']/$data['VIEWS']*100)).'%');
			
		}
		
		dump($strArr);
	}
	
	
	
	
	# - ### ### ###
    #   NOTE: Выгребание лайков
	
	# Имеется массив со ссылками на посты
	# Задача: Для каждого поста - вытащить id всех лайкнувших. (лимит макс 100 за раз, нужен цикл)
	public static function mainTestFunc_vkLikesSlicer_get(  )
	{
		set_time_limit(6000); # 100мин
		ignore_user_abort(false);
		ini_set('memory_limit','256M');
		
		$VK = new apiVkCom();
		$VK->setOpt_Token_fromAUTH();
		
		
		$fileJson_Wait = 'MLP Группы - ласт 1000 постов, на лайки.json';
		$fileJson_Done = 'MLP Группы - с лайками 100.json';
		
		foreach( range(0,1000) as $i )
		{
			#EasyFront::echoTag_HrROW_PreDef_GradDEV(); flush();
			EasyFront::echoTag_HR_Red(); flush();
			
			#dump($i);
			$arr = FileJsoner::getElem_First($fileJson_Wait);
			$val = $arr['VAL'];
			#dd($val);
			
			if( $val['GROUP_DOM'] === 'club29376882' )
			{
				dump($val);
				FileJsoner::getElem_FirstAndDel($fileJson_Wait);
				continue;
			}
			
			
			if( $val['DT_U'] < 1641025070 ) # до янв22
			{
				dump('Удаление по дате - до 2022');
				FileJsoner::getElem_FirstAndDel($fileJson_Wait);
				continue;  # До 2022
			}
			
			if( $val['DT_U'] < 1578781481 )
			{
				dump($val);
				FileJsoner::getElem_FirstAndDel($fileJson_Wait);
				continue;  # До 2020
			}
			dump($val['POST_URL']);
			
			if( ! isset($val['LIKES_ID_LIST']) )
			{
				$arrIds_or_RES = $VK->actionBig_getLikesListFull($val['GROUP_ID'],$val['POST_ID']);
				
				#dd($arrIds_or_RES);
				
				if(isset($arrIds_or_RES['IS_ERROR'])) dd($arrIds_or_RES);
				
				#dd($arrIds_or_RES);
				$val['LIKES_ID_LIST'] = implode('|',$arrIds_or_RES);
			}
			
			dump($i.'=> Пришло лайков: '.count($arrIds_or_RES));
			
			#dd($val);
			
			FileJsoner::action_addByKey($fileJson_Done,$val,$arr['KEY']);
			FileJsoner::getElem_FirstAndDel($fileJson_Wait);
			
			Sleeper::sleeper(0.2,'',true);
			
			if($i >= 100)
				EasyFrontJS::echoJS_ReloadPage_PHP_WithDump(5);
			# Иначе вылет по памяти  (Это при дефолте)
		}
		#dd($arr,$arrIds_or_RES);
	}
	
	# Имеется асоц массив с Постами+Список id лайкнувших
	# Задача: Агрегировать данные по самым активным юзерам(колво лайков) в заданном интервале времени
	public static function mainTestFunc_vkLikesSlicer_parse(  )
	{
		
		$path = 'Группы = 3 = с лайками 34к постов = Только с 2022.json';
		
		$ARR = FileJsoner::getBase_FullAsArray($path);
		
		$FIN_ARRids = [ ];
		
		foreach( $ARR as $k=>$val )
		{
			# - ###
			
			#if( $val['DT_U'] < 1641025070 ) continue; # 1641025070 = янв 2022
			if( $val['DT_U'] < 1672525585 ) continue; # 1672525585 = янв 2023
			#if( $val['DT_U'] < 1682893585 ) continue; # 1682893585 = май 2023
			
			# - ###
			#$IDS = $val['LIKES_ID_LIST'];
			$IDS_arr = explode('|',$val['LIKES_ID_LIST']);
			
			foreach( $IDS_arr as $id )
			{
				$id = (string) $id;
				
				if($id === '') continue;
				
				if(array_key_exists($id,$FIN_ARRids))
					$FIN_ARRids[$id] += 1;
				else
					$FIN_ARRids[$id] = 1;
			}
		}
		
		ksort($FIN_ARRids);
		
		$likes = 3;
		foreach( $FIN_ARRids as $k=>&$val )
		{
			# Всего = 88к
			# >= 10  22400
			# >= 20  15600
			# >= 30  12300
			# >= 50  9000
			# >= 100 5500
			
			if( ! ($val >= $likes) ) unset($FIN_ARRids[$k]);
		}
		
		
		file_put_contents(
			'ID = янв23-май23 = '.$likes.'+ лайков = '.count($FIN_ARRids).'шт.txt',
			implode(PHP_EOL,array_keys($FIN_ARRids)));
		
		#FileJsoner::action_writeArray($path2 , $FIN_ARRids);
		dd(count($FIN_ARRids),array_slice($FIN_ARRids,0,300,true));
		
	}
	
	
	# - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
