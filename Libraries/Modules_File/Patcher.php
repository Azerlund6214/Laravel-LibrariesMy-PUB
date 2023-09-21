<?php

namespace LibMy;



/**
 * Хелпер для любой работы про собирание путей к файлам/папкам/другому
 * Все про пути в 1 месте.
 * Получение любых путей.
 */
class Patcher
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	# NOTE: Всегда с \ в конце
	# NOTE: Использовать только \
	
	# - ### ### ###
	#   NOTE: TODO
	#$includes = glob($path.'/{,.}*', GLOB_BRACE);
	#$systemDots = preg_grep('/\.+$/', $includes);  файлы с точкой
	
    # - ### ### ###
	#   NOTE: Получение путей лары и особых
	
	/**  G:\DOMAINS\  */
	public static function getPath_DomainsFolder()
	{
		# Началом считается public/index.php
		return realpath('../../').'\\';
	}
	
	/**  G:\DOMAINS\Laravel-Proj\  */
	public static function getPath_LaraRoot()
	{
		# Началом считается public/index.php
		return realpath('../').'\\';
	}
	
	/**  G:\DOMAINS\Laravel-Proj\public\  */
	public static function getPath_LaraPublic()
	{
		return public_path().'\\';
	}
	
	/**  G:\DOMAINS\Laravel-Proj\app\  */
	public static function getPath_LaraApp()
	{
		return app_path().'\\';
	}
	
	/**  G:\DOMAINS\Laravel-Proj\storage\logs\  */
	public static function getPath_LaraStorageLogs()
	{
		return storage_path().'\\logs'.'\\';
	}
    
    # - ### ### ###
    #   NOTE: Получение php.exe

	# NOTE: В теории может не сработать или выдать не то. WORK
	/**  g:\SERVER\modules\php\PHP_8.1\php.exe  */
	public static function getPath_PhpExe_GetEnv()
	{
		return getenv('PHP_BINARY');
	}
	
	# NOTE: Надежный WORK
	/**  g:\SERVER\modules\php\PHP_8.1\php.exe  */
	public static function getPath_PhpExe_Good()
	{
		return (new \Symfony\Component\Process\PhpExecutableFinder)->find(false);
	}
	
	
    # - ### ### ###
    #   NOTE: Получение путей к файлам
	
	# TODO: public static function getFilePathsArr_AnyFILES($path){ return glob("$path\*" ); }
	# TODO: public static function getFilePathsArr_AnyFOLDERS($path){ return glob("$path\*" ); }
	
	
	public static function getFilePathsArr_ANY($path){ return glob("$path\*" ); }
	
	public static function getFilePathsArr_JPG( $path){ return glob("$path\*.jpg" ); }
	public static function getFilePathsArr_PNG($path){ return glob("$path\*.png" ); }
	
	public static function getFilePathsArr_PHP($path){ return glob("$path\*.php" ); }
	public static function getFilePathsArr_LOG($path){ return glob("$path\*.log" ); }
	public static function getFilePathsArr_CONFIG( $path){return glob("$path\*.config" ); }
	public static function getFilePathsArr_JSON( $path){return glob("$path\*.json" ); }
	
	public static function getFilePathsArr_HTML($path){return glob("$path\*.html" ); }
	public static function getFilePathsArr_CSS($path){return glob("$path\*.css" ); }
	public static function getFilePathsArr_JS($path){return glob("$path\*.js" ); }


    # - ### ### ###
    #   NOTE:

	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
