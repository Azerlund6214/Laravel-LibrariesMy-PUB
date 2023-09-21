<?php

namespace LibMy;


use Illuminate\Support\Facades\Log;

/**
 * Короткий универсальный логгер для всякого.
 * Пока мало.
 */
class LogUniv
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
	public function __destruct()  {    }
    
    # - ### ### ###
	
	# FINAL
	public static function generateLogText_UnivSimple_OneStr( $MSG = 'NoMsg'):string
	{
		# - ###
		
		$REQ = Requester::getAllInfo(false);
		
		# - ###
		
		$JSON = json_encode($REQ);
		$JSON_len = strlen($JSON);
		
		$ARR = [
			"RID-U/R {$REQ['REQUEST']['ID_USER']} {$REQ['REQUEST']['ID_CURRENT']}",
			"{$MSG}",
			#"{$REQ['DATE']['MSK']}",
			"{$REQ['IP']['SUBPAD_16']}",
			"{$REQ['UA']['EXT_TEXT_PAD']}",
			"ORIG: ".str_pad($REQ['ORIGIN']['SUB_64'],16,' '),
			str_pad($REQ['ROUTE']['URL_FULL_SUB80'],80,' '),
			"JSON_REQ (Len={$JSON_len}): Смотреть в бд",
			"{$REQ['UA']['SUB_256']}",
			#"{}",
		];
		
		return implode(' => ',$ARR);
		# - ###
	}
	
	
	
	
	
	# - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:

	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
