<?php

namespace LibMy;



/**
 * Универсальный класс для вызова в блоках TryCatch в случае ошибки.
 * Выводит всю нужную информацию о вылете в удобном виде.
 * Де-факто нужен для дебага.
 * Итого: Вместо кучи кода в каждом исключении теперь достаточно прописать 1 вызов метода отсюда и все.
 */
class TryCatcher
{
    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:
	
	public static function dumpOnTryCatch($err,$myDesc='Try-Catch')
	{
		try{
			dump($myDesc, $err->getMessage(), $err->getTrace(), [$err] );
			dump(ExceptionInfoCollector::getFullExceptionInfo($err));
		}catch(\Throwable $e){ dump($err,$myDesc,'===','Вылет в dumpOnTryCatch:',$e); }
	}
	
    public static function ddOnTryCatch($err,$myDesc='Try-Catch')
    {
		try{
	        dump($myDesc, $err->getMessage(), $err->getTrace(), [$err] );
			dd(ExceptionInfoCollector::getFullExceptionInfo($err));
		}catch(\Throwable $e){ dd($err,$myDesc,'===','Вылет в ddOnTryCatch:',$e); }
    }


    public static function makeError()
    {
        return 1/0;
    }

    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
