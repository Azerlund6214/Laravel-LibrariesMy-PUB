<?php

namespace LibMy;

# Из Laravel
use Illuminate\Support\Facades\DB;

# Библиотеки

# Модели

/**
 *
 */ # ДатаВремя создания: 170923
class DataBasePredefQuery
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	/** Получить массив с размерами всех имеющихся БД. В мб.
	 * @return array [БД => Размер]
	 * @version WORK
	 */
    public static function getAllDbSizes()
    {
	    $q = '  SELECT table_schema AS "Имя",
				ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS "Размер"
				FROM information_schema.TABLES
				GROUP BY table_schema;';
		$resRaw = DB::select($q); # stdClass
	
		$fin = [];
	    foreach( $resRaw as $k=>$v ){
	    	$v = (array)$v;
		    $fin[$v['Имя']] = $v['Размер'];
		}
		
	    return $fin;
	    #dump($resRaw);
	    #dump(json_decode(json_encode($resRaw),true));
    }
	
	/** Получить список таблиц текущей БД.
	 * @return array Номерной массив с именами таблиц, без префиксов.
	 * @version WORK
	 */
	public static function getTablesList()
	{
		$q = 'SHOW TABLES;';
		$resRaw = DB::select($q); # stdClass
		
		$fin = [];
		foreach( $resRaw as $k=>$v ){
			$tbl = array_values((array)$v)[0];
			$pref = DB::connection()->getConfig()['prefix'];
			$fin []= substr($tbl,strlen($pref));
		}
		
		return $fin;
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
