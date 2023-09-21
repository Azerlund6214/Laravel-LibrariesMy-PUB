<?php

namespace LibMy;

# Из Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ReflectionException;

# Библиотеки

# Модели

/**
 * Вытаскивает полезную информацию о модели и бд.
 * @version DRAFT WORK
 */ # ДатаВремя создания: 150923
class ModelInfo
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	/** Получить подробную инфу обо всех моделях сразу. Просто обертка с циклом.
	 * @param bool $needRow    Запрашивать ли реальную строку из бд
	 * @param bool $needStruct Запрашивать ли список столбцов
	 * @return array
	 * @version WORK
	 */
	public static function getModelsInfoForAll($needRow=true,$needStruct=true):array
	{
		$FIN = [];
		foreach( self::getModelsNamesArr() as $name ){
			$FIN[$name] = self::getInfo_Full($name,$needRow,$needStruct);
		}
		return $FIN;
	}
	
	
	/** Получить список имен классов моделей. Только из корня \App
	 * @todo Оптимизировать выборку сразу до корня
	 * @return array Список имен классов моделей
	 * @version WORK
	 */
    public static function getModelsNamesArr():array
    {
	    $pathsFromRoot = [];
	    foreach(collect(File::allFiles(app_path())) as $file)
	    {
		    $path = $file->getRelativePathName();
		    if( ! str_contains($path, '\\') ) # Файл НЕ в папке
			    if( ! str_contains($path, ' ') ) # Это не мой тхт с комментом и тд
				    if( str_contains($path, '.php') ) # Это точно пхп
					    $pathsFromRoot []= str_replace('.php','',$path); # Сразу убераю расширение
	    }
	
	    $pathsValid = [];
	    foreach ($pathsFromRoot as $className)
	    {
		    try { $reflection = new \ReflectionClass('App\\'.$className);
		    } catch (ReflectionException $e) {  continue;  }
		
		    if( ($reflection->isSubclassOf(Model::class) && !$reflection->isAbstract())  )
			    $pathsValid []= $className;
	    }
	    
	    return $pathsValid;
    }
	
    
	/** Для заданной модели - вытащить всю полезную инфу, включая структуру таблицы и рандом строку бд
	 * @param string $modelName Имя класса целевой модели
	 * @param bool $needRow    Запрашивать ли реальную строку из бд
	 * @param bool $needStruct Запрашивать ли список столбцов
	 * @return array
	 * @version WORK
	 */
	public static function getInfo_Full($modelName,$needRow=true,$needStruct=true)
	{
		$INFO['NAME'] = $modelName;
		$INFO['NAMESPACE'] = 'App\\'.$modelName;
		
		$inst = new $INFO['NAMESPACE'];
		$INFO['DB_TABLE'] = $inst->getTable();
		
		try{
			$INFO['DB_COUNT'] = $inst->count();
		}catch(\Exception $e){
			$INFO['DB_COUNT'] = -1; // Если таблицы не существует.
		}
		$INFO['DB_PK']      = $inst->getKeyName();
		$INFO['DB_PK_TYPE'] = $inst->getKeyType();
		
		if( ($INFO['DB_COUNT'] >= 1) )
		{
			if( $needRow    ) $INFO['ROW_RANDOM'] = $inst->inRandomOrder()->limit(1)->get()->toArray()[0];
			if( $needStruct ) $INFO['TBL_STRUCT'] = Schema::getColumnListing($INFO['DB_TABLE']);;
		}
		else
		{
			$INFO['ROW_RANDOM'] = [];
			$INFO['TBL_STRUCT'] = [];
		}
		
		return $INFO;
	}
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####   ʕ•ᴥ•ʔ  \(★ω★)/  (^=◕ᴥ◕=^)   ####
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
