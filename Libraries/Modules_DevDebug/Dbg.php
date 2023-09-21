<?php

namespace LibMy;


/**
 * Класс для дебага.
 */
class Dbg {

    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###
	
	
	/** Универсальный замер времени исполнения + DD.
	 * @param callable $FUNC Что мерить = function(){ код }
	 * @version WORK
	 */
	public static function getExecTime_DD( callable $FUNC )
	{
		$TIMER = new TimerMy();
		
		$RES = $FUNC();
		
		dd($TIMER->getTimeMs4(),$RES);
	}
	
	/** Универсальный замер времени исполнения + DD. Массовый, с X повторений подряд.
	 * @param callable $FUNC Что мерить = function(){ код }
	 * @param int $count Количество циклов повторения
	 * @version WORK
	 */
	public static function getExecTime_DD_MassCall( callable $FUNC, int $count )
	{
		$arrTimes = [];
		
		foreach( range(1,$count) as $i ){
			$TIMER = new TimerMy();
			
			$RES = $FUNC();
			
			$arrTimes []= $TIMER->getTimeMs4(false);
			if( $i === $count ) dump('Последний вывод:',$RES);
		}
		
		$ARR = [
			'RAW' => $arrTimes,
			'RAW_SORT' => '',
			'AVG' => array_sum($arrTimes)/$count,
			'' => 1,
		];
		sort($arrTimes);
		$ARR['RAW_SORT'] = $arrTimes;
		
		dd($ARR);
	}
	
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	# NOTE: Возможно баян
	/**
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
	
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	# NOTE: Возможно баян
	/**
	 * Выводит полный стек вызовов функций
	 * TODO: Написать вывод присланных аргуменов
	 * @param bool $Exit_after_echo - Завершать ли работу после вывода
	 */
	public static function Echo_Call_Stack( $Exit_after_echo = true )
	{
		# http://php.net/manual/ru/function.debug-backtrace.php
		
		
		#SF::PRINTER(debug_backtrace());
		
		
		$result = array();
		foreach( debug_backtrace() as $one )
		{
			$text  = @$one['class'];
			$text .= @$one['type'];
			$text .= @$one['function'];
			$text .= "( ";
			
			$text .= "Аргументов: ".count(@$one['args']) ;
			
			/*foreach ( @$one['args'] as $arg )
			{
				//echo "<br>".print_r($arg);
				//$text .= var_dump($arg);
				//$text .= "";
			}*/
			
			$text .= " )";
			
			$result []= $text;
		}
		
		
		$result []= "Корень программы";
		#SF::PRINTER($result);
		
		
		$result = array_reverse($result);
		
		# Прямо тут можно прописать Unset последнего элемента
		
		$count_results = count($result);
		
		
		for ( $i=0 ; $i < $count_results ; $i++ )
		{
			if($i!=0) echo "<br>";
			echo str_repeat("- - ", $i+1 );
			echo $result[$i];
		}
		
		if( $Exit_after_echo )
			exit("<hr>Выход из Echo_Call_Stack");
		
	}
	
	
	
	
	
	// Пока крайне спорно. Пхп технически не вывозит мои тербования.
    public static function dddd($vars,$args)
    {
        // Dbg::dddd(get_defined_vars(), func_get_args());

        $stack = StackTraceUntangler::getFullStackCurrent(true,false,false);

        $frame = $stack[1];
        dump('Дебажится метод: '.$frame['function']);

        dump('Его аргументы: ');
        foreach( $frame['args'] as $arg ) dump($arg);

        dump('Его переменные: ');
        foreach( $vars as $var ) dump($var);


        dd($stack);


        /*
        22:03 08.01.2023
        dddd

        Должен сам найти себя.
        Потом вывести все аргументы метода + все доступные тут переменные.

        получить все окружение в его области видимости.

        грести в сторону стектрейса.
        чекать как устроен мой лог ошибок - там разворачивается портянка.

        обрезать длинные массивы и тд.
        */
    }





} # End class


