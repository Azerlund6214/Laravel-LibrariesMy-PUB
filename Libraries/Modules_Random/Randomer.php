<?php

namespace LibMy;


/**
 * Все про рандом.
 */
class Randomer
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###


    # - ### ### ###
    #   NOTE:

    # IMPORTANT: Только для длинных операций.  При вызове подряд выдаст дубль
    public static function SetNewRandomSeed()
    {
        #dd(microtime(true)*10000);
        $timeInt = (int)(microtime(true)*10000);
        srand( $timeInt );
        return $timeInt;

        # srand((int)(microtime(true)*100));   = в 1 строку
    }

    # - ### ### ###
    #   NOTE:

    public static function getUrlReal_Site()
    {
        $arr = [
            'https://www.123.ru/',
            'https://translate.yandex.ru/',
            'https://vk.com/',
            'https://purplesmart.ai/',
        ];
        return $arr[array_rand($arr)];
    }

    public static function getUrlReal_Picture()
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
	
	
	public static function generateArrayRandomInt($count, $intStart, $intEnd)
	{
		$arr = array();
		
		foreach( range(0, $count-1) as $i )
			array_push($arr,random_int($intStart, $intEnd));
		
		return $arr;
	}

 
	
	# - ### ### ###
    #   NOTE:
	
	/** FINAL TESTED
	 * Получить рандомное целое число. Просто обертка для random_int.
	 * @param int $min Минимальное  число.
	 * @param int $max Максимальное число.
	 * @return int
	 */
	public static function getRandomInt(int $min, int $max):int
	{
		# NOTE: Исключение пустое, просто что бы IDE не подсвечивал все вызовы метода, предлагая его добавить.
		#  Ошибка тут крайне маловероятна, поэтому не учитываю. Защиты от дурака нет.
		try {
			return random_int($min, $max);
		} catch (\Exception $e) {
			dd($e);
		}
	}
	
	/** FINAL TESTED
	 * Получить рандомное дробное число.
	 * @param int $leftMin Минимальная  целая часть. Явное число.
	 * @param int $leftMax Максимальная целая часть. Явное число. Например 56|3|117|44
	 * @param int $accurMin Минимальная точность в знаках. ВСЕГДА >= 1.   При 0 будет вылет.
	 * @param int $accurMax Максимальная точность в знаках. ВСЕГДА >= accurMin
	 * @return string Строкой, так как могут быть нули в конце. Иначе они пропадут.
	 * @throws \Exception
	 */
	public static function getRandomFloat(int $leftMin,int $leftMax, int $accurMin, int $accurMax):string
	{
		$left = self::getRandomInt($leftMin, $leftMax);
		
		$precision = self::getRandomInt($accurMin, $accurMax);
		
		# Получаю самое большое N-значное число.
		$rightMax = str_pad('', $precision,'9');
		
		$right = self::getRandomInt( 1  , (int)$rightMax );
		
		# Дополняю дробную часть нулями слева, если их не хватает.
		# Например выпало число 45, а надо точность 5.  Будет 00045
		$right = str_pad($right, $precision,'0',STR_PAD_LEFT);
		
		return ($left.'.'.$right);
	}
	
	
	# - ### ### ###
    #   NOTE: DEV

    public static function getDev_Arr()    { return ['1','2','3','4','5','6','7','8']; }
    #public static function getDev_ArrBig()  { return ; }
    public static function getDev_ArrAsoc()  { return ['1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8]; }
    public static function getDev_ArrAsocBig(){ return json_decode('{"IMG":{"POSTER":"data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==","URL":"https://www.megacritic.ru/media/reviews/photos/thumbnail/640x640s/e2/06/33/1005878-89-1560119112.jpg","H":"941\">","W":"640","WH":"640x941\">"},"ABOUT_TITLE":"Король Лев","ABOUT_RATE_CRIT":"6.0","ABOUT_RATE_USER":"7.2 ","ABOUT_RATE_AGE":"6+","ABOUT_DIRECTOR":"Джон Фавро","ABOUT_YEAR":"2019","ABOUT_DESC_RAW":"Мультфильм Король Лев 2019 года представляет собой трёхмерную обновлённую кино-версию знаменитого хита от студии Дисней Король Лев. На этот раз персонажи созданы благодаря компьютерной анимации и современным технологиям. Но рассказывают по-преженму всё ту же историю о львёнке Симбе, сыне великого короля Муфасы, который становится жертвой интриги дядюшки Шрама, претендующего на лидерство в львином прайде....>>>","ABOUT_DESC_END_3_DOTS":"Мультфильм Король Лев 2019 года представляет собой трёхмерную обновлённую кино-версию знаменитого хита от студии Дисней Король Лев. На этот раз персонажи созданы благодаря компьютерной анимации и современным технологиям. Но рассказывают по-преженму всё ту же историю о львёнке Симбе, сыне великого короля Муфасы, который становится жертвой интриги дядюшки Шрама, претендующего на лидерство в львином прайде....","ABOUT_DESC_END_LAST_DOT":"Мультфильм Король Лев 2019 года представляет собой трёхмерную обновлённую кино-версию знаменитого хита от студии Дисней Король Лев. На этот раз персонажи созданы благодаря компьютерной анимации и современным технологиям. Но рассказывают по-преженму всё ту же историю о львёнке Симбе, сыне великого короля Муфасы, который становится жертвой интриги дядюшки Шрама, претендующего на лидерство в львином прайде.","ABOUT_DESC_END_LAST_DOT_LEN":749,"PAGE_URL":"https://www.megacritic.ru/film/korol-lev","GENRE_ARR":["Драмы","Мультики","Мюзиклы"],"COUNTRY_ARR":["США"],"DATE_RUS":{"Y":"2019","M":"07","D":"18"},"CATEGORY":{"MULT":true,"C_RUS":false,"C_USA":true,"C_UK":false,"C_FR":false,"C_JP":false,"C_DE":false,"C_CA":false},"TG_SENDED":false}',true); }
    public static function getDev_Float2()  { return 74.89; }
    public static function getDev_Float8(){ return 28.74527485; }

    /*
    ['a', 'b', 'c']; // false
    ["0" => 'a', "1" => 'b', "2" => 'c']; // false
    ["1" => 'a', "0" => 'b', "2" => 'c']; // true
    ["a" => 'a', "b" => 'b', "c" => 'c']; // true
    */

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
