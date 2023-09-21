<?php

namespace LibMy;


/**
 * Хелпер для любой работы с отдельным файлом.
 * Только нативные методы.
 *
 */
class Filer
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###
	#   NOTE: Обобщенные методы
	
	/**
	 * Проверяет существование файла
	 * @param $path
	 * @return bool
	 */
    public static function checkExists($path):bool
    {
        return file_exists($path);
    }
	
	/**
	 * Создает пустой файл если его нет
	 * Можно указывать с папками, но они должны существовать "Папка1/Папка2/файл.txt"
	 * @param $path
	 */
    public static function createIfNeed($path)
    {
	    if ( file_exists($path) ) //
		    return;
    	
        $fp = fopen($path, 'a+'); // a+ = с конца, создать если нет.
        fclose($fp);
    }
    
	# WORK
	public static function getFileLinesCount($path)
	{
		#return count(file($path)); # WORK но не оптимально
		
		# NOTE: Оптимально тк читает построчно и держит в памяти по 1 строке за раз а не все целиком.
		#  Считает правильно.
		$stream = fopen($path, 'r');
		$i = 0;
		while( (fgets($stream)) !== false)
		{
			$i++;
		}
		fclose($stream);
		return $i;
	}
	
	/**
	 * Удаляет файл, если он существует.
	 * @param $path
	 */
	public static function deleteFile( $path )
	{
		if ( file_exists($path) ) //
			unlink( $path );
	}
	
	
	# CONCEPT: public static function getFileInfo($path){ }
	
	
	# - ### ### ###
	#   NOTE: Запись

    # WORK
    public static function writeInBlankFile($path, $str)
    {
        $fp = fopen($path, 'w'); // w=простоянно очищать, создать если нет.
        fwrite($fp,$str);
        fclose($fp);
    }
	
	/**
	 * Записыват $path в файл сразу в конец файла.
	 * @param $path
	 * @param $str
	 * @version WORK
	 */
    public static function writeToEnd($path, $str)
    {
        $fp = fopen($path, 'a+'); // a+ = с конца, создать если нет.
        fwrite($fp,$str);
        fclose($fp);
    }
    
	/**
	 * Записыват $path в файл с новой строки. Создать если нет.
	 * @param $path
	 * @param $str
	 * @version WORK
	 */
    public static function writeToEnd_NewLine($path, $str)
    {
        $fp = fopen($path, 'a+'); // a+ = с конца, создать если нет.
        fwrite($fp,PHP_EOL.$str);
        fclose($fp);
        # TODO: Убрать PHP_EOL если файл свежий. Проверять exist.
    }

    # - ### ### ###
    #   NOTE: Чтение целиком
	
	# WORK
	# Если файла нет - будет 500
	public static function readFullAsText($path)
	{
		return file_get_contents($path);
	}
	
	# WORK
	public static function readFullAsArrLines($path)
	{
		return file($path);
	}
	
	# - ### ### ###
	#   NOTE: Чтение построчно
	
	# WORK
	# Читает построчно только до нужной строки.  Только для 1 строки.  Строка либо false.
	public static function readLineOne_ByRealNum( $path , $num )
	{
		$stream = fopen($path, 'r');
		$lineText = false;
		
		$i = 1; # Именно от 1, а не от 0.  Чтоб понятные номера строк.
		while (($lineText = fgets($stream)) !== false)
		{
			if( $i == $num )
				break; # Когда дошли до нужной строки - перестаем читать.
			$i++;
		}
		fclose($stream);
		
		return $lineText;
	}
	
	# WORK
	public static function readLines_ByRealNums( string $path , array $numsArr )
	{
		$stream = fopen($path, 'r');
		$lineTextArr = [];
		
		$i = 1; # Именно от 1, а не от 0.  Чтоб понятные номера строк.
		while (($lineText = fgets($stream)) !== false)
		{
			if( in_array($i , $numsArr) )
			{
				$lineTextArr[$i] = $lineText;
				
				if( count($numsArr) === count($lineTextArr) )
					break; # Когда получили все нужные строки - перестаем читать.
			}
			
			$i++;
		}
		fclose($stream);
		
		return $lineTextArr;
	}
	
	# WORK
	public static function readLineNear($path,$lineStart,$nearCount=2)
	{
		$arrLineNums = range( ($lineStart-$nearCount) , ($lineStart+$nearCount)  );
		return self::readLines_ByRealNums($path,$arrLineNums);
	}
	
	
	
    # - ### ### ###
    #   NOTE: Важные заметки
	
	/*
		'r'  - Чтение ; Начало файла
	    'r+' - Чт/Зап ; Начало файла
	    'w'  - Запись ; Начало файла ; Создаем если нет ; Очистить файл.
	    'w+' - Чт/Зап ; Начало файла ; Создаем если нет ; Очистить файл.
	    'a'  - Запись ; Конец файла  ; Создаем если нет.
	    'a+' - Чт/Зап ; Конец файла  ; Создаем если нет.
	*/
	
	/*
		fwrite ($fp, "\n");
		fwrite ($fp, "\r\n");
		fwrite ($fp, chr(0x0a));
		fwrite ($fp, PHP_EOL);
	*/

    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
