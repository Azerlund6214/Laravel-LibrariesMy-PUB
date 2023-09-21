<?php

namespace LibMy;



/**
 * IMPORTANT: В этом классе могут/будут лежать критически важные данные.
 *  Все сгреблено в 1 место чтоб не искать.
 *  Используется в случае, если убирать в ENV не оптимально или данные нельзя публичить.
 *
 */
class authTokenStrings
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	public static $VK_ownerId_MY = 123; # Мой акк вк
	public static $VK_idApp = 123;
	
	public static $VK_GROUPS = [
		'AWC' => ['ID'=>-123,'DOM'=>'123123' ],
	]; #
	
	
	# - ### ### ###
    #   NOTE:
	
	
	public static $VK_CHATBOT_GROUP_ID = 123;
	public static $VK_CHATBOT_ID = '';
	public static $VK_CHATBOT_SECRET = '123';
	
	public static $VK_TOKEN_CURRENT_FILE = [ # Возьмет [0]
		'TOKEN_VK_API=REAL=GROUP=CHAT_BOT.txt',
		#'TOKEN_VK_API=REAL=USER=MY_ACC_FULL.txt',
	];
	
	
	# WORK
	# Файл в токеном лежит в папке доменов и не светится в проекте и на гите.
	public static function getVkAuthToken_FromDomainsRoot()
	{
		$path = Patcher::getPath_DomainsFolder().self::$VK_TOKEN_CURRENT_FILE[0];
		
		return Filer::readFullAsText($path);
	}
	
	
	
	
	
    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:

	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
