<?php

namespace LibMy;

use Illuminate\Support\Facades\Route;

/**
 * Класс со вспомогательными методами, пока все в кучу.
 */ # ДатаВремя создания: 030923 - Все сгреб сюда.
class HelperUniv
{
    # - ### ### ###
	
	public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
	public function __destruct()  {    }
    
    # - ### ### ###
	
	# TODO: Можно доработать, все внутри
	public static function isNowOnProductionServer()
	{
		# TODO:  $_SERVER['SERVER_ADDR'] === '127.0.0.1') && (env('DB_USERNAME') === 'root')
		# Про адрес воодще не аргумент, он всегда локальный.
		
		if( env('APP_DEBUG') === true || (env('DB_USERNAME') === 'root'))
			return false; # Локалка
		else
			return true; # Релиз
	}
	
	
	
	# Были ли отправлены заголовки + доп инфа.   Предназанчен для dd(...)
	public static function isHeadersAlreadySent():array
	{
		$INFO = [];
		$INFO['IS_SEND'] = headers_sent($INFO['FILE'],$INFO['LINE']);
		return $INFO;
	}
	
	/**
	 * Проверка отправленности заголовков.
	 * DD с инфой если отправлены.
	 * Только для дебага и тестов.
	 */
	public static function checkHeadersIsNotSentOrDD(): void
	{
		$file = '';
		$line = 0;
		if( headers_sent($file , $line) ){
			dump(__METHOD__ , 'АХТУНГ!!!!1 -> Сессия уже была отправлена' , $file . ':' . $line);
			@dump(StackTraceUntangler::getFullStackCurrent(true , true , true , true));
			dd(debug_backtrace());
		}
	}
	
	
	
	
	
	public static function getAllRoutesForController( $contrName ): array
	{
		$INFO = [];
		
		# NOTE: Не добавит те, где сразу функция в web
		
		foreach( Route::getRoutes() as $route ){
			
			if( isset($route->action['controller']) )
				if( str_contains($route->action['controller'] , $contrName) )
					$INFO [] = $route;
			
			#dd($route->uri());
		}
		
		return $INFO;
	}
	
	
	# WORK
	public static function getGeneralServerInfo()
	{
		return [
			'PHP_VERSION'        => phpversion() ,
			'SOFTWARE'           => $_SERVER['SERVER_SOFTWARE'] ?? 'UNDEF' ,
			'PHP_OS'             => php_uname() ,
			'PHP_DEFINED_OS'     => PHP_OS ,
			'PHP_DEFINED_OS_FAM' => PHP_OS_FAMILY ,
		];
	}
	
	
	/**
	 * Просто dd($_SERVER). Для централизации вызовов.
	 */
	public static function ddServerVar()
	{
		//phpinfo(1);
		dd($_SERVER);
	}
	
	
	/**
	 * Выводит текущее использование памяти PHP
	 * @param string $unit - Единица измерения - G M K B
	 * @param bool $peak - Выводить ли пиковое значение
	 * @param bool $real - Выводить ли реальное значение (например вместо 234 будет 256)
	 * @return double Данные
	 * @version Первую версию я написал янв2018 из краулера.
	 */
	public static function getMemoryUsage( $unit = "M" , $peak = false , $real = false )
	{
		if( ! is_bool($peak) || ! is_bool($real) )
			dd("Не BOOL параметры Peak({$peak}) или Real({$real}).(Return false)");
		
		if( $peak )
			$ram = memory_get_peak_usage($real);
		else
			$ram = memory_get_usage($real);
		
		switch( $unit ){
			case "G":
				return (double) $ram / 1024 / 1024 / 1024;
				break;
			case "M":
				return (double) $ram / 1024 / 1024;
				break;
			case "K":
				return (double) $ram / 1024;
				break;
			case "B":
				return (double) $ram;
				break;
		}
		
		dd(__METHOD__ , 'Неправильное значение - ' . $unit);
	}
	
	# - ### ### ###
	#   NOTE:
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	/**
	 * Валидация корректрости адреса/номера кошелька.
	 *
	 * @param string $target
	 * @param string $method
	 * @return boolean
	 * */
	public  function validateWalletNumber( $target , $method )
	{
		
		#dump('sf@validateWalletNumber()  НЕ ТЕСТИЛ этот метод');
		#dump('sf@validateWalletNumber()  НАДО ДОБАВИТЬ PERFMON');
		
		$patterns = array(
			
			'PAYEER'      => '^[Pp]{1}[0-9]{7,15}$^', # Номер счета = P1000000 = P + от 7 до 15 цифр
			
			'PeMon'       => '/^U\d{6,9}$/', # Номер счета = U + 6-9цифр   Только USD
			'PERFMON'     => '/^U\d{6,9}$/', # Номер счета = U + 6-9цифр   Только USD
			
			'QIWI'        => '^\+\d{9,15}$^', # Номер телефона = +7953155XXXX
			'YANDEXMONEY' => '^41001[0-9]{7,11}$^', # Номер счета = 410011499718000
			'ADVCASH'     => '^[RUE]{1}[0-9]{7,15}|.+@.+\..+$^', # Номер счета = advcash@payeer.com
			
			# - ###
			
			'Bitcoin' => '^[A-Za-z0-9]{32,42}$^', #  = 13C3fxYMZzbt9HsTvCni779gqXyPadGtTQ
			'BITCOIN' => '^[A-Za-z0-9]{32,42}$^', #  = 13C3fxYMZzbt9HsTvCni779gqXyPadGtTQ
			'BTC'     => '^[A-Za-z0-9]{32,42}$^', #  = 13C3fxYMZzbt9HsTvCni779gqXyPadGtTQ
			
			'Ethereum' => '^0x[A-Za-z0-9]{40,40}$^', #  = 0x0bdca97324da3f6e5df8c66ad67d62eea0ba6e57
			'ETHEREUM' => '^0x[A-Za-z0-9]{40,40}$^', #  = 0x0bdca97324da3f6e5df8c66ad67d62eea0ba6e57
			'ETH'      => '^0x[A-Za-z0-9]{40,40}$^', #  = 0x0bdca97324da3f6e5df8c66ad67d62eea0ba6e57
			
			'Litecoin' => '^[A-Za-z0-9]{32,34}$^', #  = LZYYLmDWFg4bcujUHa7AtihcpSpKM5JbGo
			'LITECOIN' => '^[A-Za-z0-9]{32,34}$^', #  = LZYYLmDWFg4bcujUHa7AtihcpSpKM5JbGo
			'LTC'      => '^[A-Za-z0-9]{32,34}$^', #  = LZYYLmDWFg4bcujUHa7AtihcpSpKM5JbGo
			
			'BITCOINCASH'  => '^[A-Za-z0-9\:]{32,54}$^', #  = bitcoincash:qz7vqcnjf6ps3nxmdve2tshxdnu0m8xeq50c5pvuyr
			'BITCOIN-CASH' => '^[A-Za-z0-9\:]{32,54}$^', #  = bitcoincash:qz7vqcnjf6ps3nxmdve2tshxdnu0m8xeq50c5pvuyr
			'BCH'          => '^[A-Za-z0-9\:]{32,54}$^', #  = bitcoincash:qz7vqcnjf6ps3nxmdve2tshxdnu0m8xeq50c5pvuyr
			
			'Dash' => '^[A-Za-z0-9]{32,34}$^', #  = XkvxoLRnrBKv6EFuzf1ftkMyGNFe3nSXTv
			'DASH' => '^[A-Za-z0-9]{32,34}$^', #  = XkvxoLRnrBKv6EFuzf1ftkMyGNFe3nSXTv
			
			'XRP'        => '^[A-Za-z0-9]{32,34}$^', #  = rshvnxLDE9Jsm8sJxPxct425HhQC2tk5CV
			'RIPPLE'     => '^[A-Za-z0-9]{32,34}$^', #  = rshvnxLDE9Jsm8sJxPxct425HhQC2tk5CV
			'RIPPLE-TAG' => '^[0-9]{1,10}$^', #  = 1234567890
			
			'TETHER' => '^0x[A-Za-z0-9]{40,40}$^', #  = 0x0bdca97324da3f6e5df8c66ad67d62eea0ba6e57
			'USDT'   => '^0x[A-Za-z0-9]{40,40}$^', #  = 0x0bdca97324da3f6e5df8c66ad67d62eea0ba6e57
		
		);
		
		if ( ! in_array($method, array_keys($patterns) ) )
			exit("SF@validateWalletNumber() Неверный код платежного метода.  method=$method ");
		
		
		if( preg_match( $patterns[ $method ] , $target ) )
			return true;
		
		return false;
	}
	
	# - ### ### ###
	#   NOTE:
	
	/** # CONCEPT: Ооооочень древний.   На полную переработку.
	 * Выводит содержимое любой переменной в хорошо читаемом виде. Основная функция для дебага.
	 * @param string $Traget - Что выводим
	 * @param string $MODE - Тип вывода = print_r или var_dump
	 * @param string $Description - Описание
	 */
	public static function PRINTER( $Traget, $MODE = "print_r", $Description = "Default" )
	{
		#TODO Сделать еще принтер массивов в таблицу
		
		echo "<hr color=red>";
		echo "<pre>";
		
		if ( $Description != "Default" )
			echo "Описание: $Description<br>";
		
		switch( $MODE )
		{
			case "print_r":
			case "print":
			case "P":
			case "p":
				print_r( $Traget );
				break;
			
			case "var_dump":
			case "var":
			case "V":
			case "v":
				var_dump( $Traget );
				break;
			
			default:
				echo "SF_PRINTER: case-Дефолт (MODE=$MODE) (Валидные=P или V), Вывожу как var_dump(V) \n\n";
				var_dump( $Traget );
				break;
			
		}
		
		echo "</pre>";
		echo "<hr color=red>";
	}
	
	
	/** # CONCEPT: Ооооочень древний.   На полную переработку.
	 * Выведет список переменных и метоодов класса(объекта)
	 * @param object $target - экземпляр класса для вывода
	 * @param string $mode - FUNC / VARS / anychar - Что выводим
	 */
	public static function Print_Class_Func_and_Vars( $target , $mode="any char")
	{
		
		echo "<pre>";
		
		echo "<hr color=red>";
		echo "<hr color=red>";
		
		switch( $mode )
		{
			case "FUNC":
				echo "<hr>Все методы класса:";
				print_r( @get_class_methods( $target ) );
				break;
			
			case "VARS":
				echo "<hr>Все ПОЛЯ класса:";
				print_r( @get_object_vars( $target ) );
				break;
			
			default:
				echo "<hr>Все методы класса:";
				print_r( @get_class_methods( $target ) );
				
				echo "<hr color=blue>Все ПОЛЯ класса:";
				print_r( get_object_vars( $target ) );
		}
		
		echo "<hr color=red>";
		echo "<hr color=red>";
		
		echo "</pre>";
	}
	
	
	/** # CONCEPT: Ооооочень древний.   На полную переработку.
	 * Выводит путь до файла, который вызвал функцию (путь от корня САЙТА (НЕ Файловой системы))
	 * @param string $TARGET = FILE / FOLDER
	 * @param string $ACTION = ECHO / RETURN
	 * @param string $DIR = Переменная __DIR__ из места вызова функции (прямо так и писать)
	 * @return string
	 */
	public static function Echo_This_File_Path( $TARGET = "FILE", $ACTION="ECHO", $DIR = "")
	{
		# Старый рабочий вариант:  (PATH = __FILE__ при вызове этой функции)
		#$PATH = str_replace ( "\\", "/", $PATH); # Нужно только для локалки, на хосте все слеши сразу правильные
		#echo str_replace ( $_SERVER['DOCUMENT_ROOT'] , "" , $PATH );
		
		if ( $TARGET === "FILE" )
		{
			$file_caller = debug_backtrace()[0]['file']; # ПОЛНЫЙ путь до вызвавшего ФАЙЛА
			
			$file_caller = str_replace ( "\\", "/", $file_caller); # Нужно только для локалки, на хостинге все слеши сразу правильные
			$result = str_replace ( $_SERVER['DOCUMENT_ROOT'] , "" , $file_caller );
			
		}
		else
		{
			# Если нужна папка
			$file_dir = str_replace ( "\\", "/", $DIR); # Нужно только для локалки, на хосте все слеши сразу правильные
			$result = str_replace ( $_SERVER['DOCUMENT_ROOT'] , "" , $file_dir );
		}
		
		
		$result = substr($result,1); # Обрезаем слеш в начале (Чтоб не ругался хостинг)
		
		
		if ( $ACTION === "ECHO" )
			echo $result;
		
		return $result;
		
	}
	
	
	# - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE: Статистические обработки
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	# Статистический метод - выясняет "усеченное среднее" значение.
	# Суть - обрезать с краев N% значений что бы исключить выбросы.
	# Всегда акругляет до 2 знаков -> 13.32  0.00
	# При ошибках вернет -9999
	public function statAvgTruncated( $arrayNums, $truncPerc ):float
	{
		# Если ORM не нашел строк
		if( $arrayNums === null || empty($arrayNums) )
			return 0;
		
		# - ###
		
		if( ! is_array( $arrayNums ) )
			return -9999;
		
		$cnt = count($arrayNums);
		
		# array();
		if( $cnt === 0 )
			return -9999;
		
		# Не оптимально, но по-другому пока никак.
		foreach( range(0,$cnt-1) as $i )
		{
			if( ! is_numeric($arrayNums[$i]) )
				return -9999;
			
			$arrayNums[$i] = number_format($arrayNums[$i] , 2, '.','') ;
		}
		
		
		# - ###
		# Обязательно, иначе все будет бессмысленно
		
		sort($arrayNums);
		
		# - ###
		
		#dump($arrayNums);
		
		# - ###
		
		if( $cnt === 1 )
			return $arrayNums[0];
		
		if( $cnt === 2 )
			return number_format(($arrayNums[0]+$arrayNums[1])/2 , 2, '.','') ;
		
		# - ###
		# Считаем, сколько элементов обрезать.
		
		# Если вычислит меньше 1.0 элементов, то обрезки не будет. маленькая выборка
		$trunkCntOne = floor( $cnt * ($truncPerc/100) );
		$trunkCntAll = $trunkCntOne*2;
		$countResult = $cnt - $trunkCntAll;
		
		# - ###
		
		#dump($arrayNums);
		
		# Режу начало
		$arrayNums = array_slice($arrayNums, $trunkCntOne);
		
		# Выбираю ТОЛЬКО N первых элементов. (итоговое количество)
		$arrayNums = array_slice($arrayNums, 0,$countResult);
		
		# - ###
		# Считаем итог
		
		$sum = array_sum($arrayNums);
		
		$avgTruncatedSum = number_format(($sum/$countResult),2,'.','');
		
		# - ###
		
		return $avgTruncatedSum;
		
		# - ###
		
		/*
		dump($arrayNums);
		dump('cnt = '.$cnt);
		dump('truncOne = '.$trunkCntOne);
		dump('truncAll = '.$trunkCntAll);
		dump('cntRes = '.$countResult);

		dump('sum = '.$sum);
		dump('avgT = '.$avgTruncatedSum);
		*/
		
	}
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	# Статистический метод - выясняет "медианное" значение.
	# 50% элементов больше него и 50% меньше.
	# При четном кол-ве элементов берется среднее из двух посередине.
	public function statMedian( $arrayNums ):float
	{
		# Если ORM не нашел строк
		if( $arrayNums === null || empty($arrayNums) )
			return 0;
		
		if( ! is_array($arrayNums) )
			return -9999;
		#throw new Exception('$arr must be an array!');
		
		sort($arrayNums);
		
		# - ###
		
		$cnt = count($arrayNums);
		
		# Находим средний элемент (со сдвигом влево, если массив четный)
		$middleVal = floor(($cnt - 1) / 2);
		
		# - ###
		# Если массив нечетный, то отдаем средний элемент
		
		//dd($arrayNums);
		
		if( ($cnt % 2) !== 0 )
			return $arrayNums[$middleVal];
		
		# - ###
		# Если четный, то вычисляем среднее из 2 серединных элементов
		
		$lowMid = $arrayNums[$middleVal];
		$highMid = $arrayNums[$middleVal + 1];
		
		$avg =  number_format(($lowMid + $highMid) / 2,2,'.','');
		
		return $avg;
	}
	
	
	
	# - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
