<?php

namespace LibMy;



/** Набросок
 * Класс для реализации функционала прокси.
 *
 */
class ProxyMy
{
    # - ### ### ###
    
    public function __construct() {    }
    public function __destruct()  {    }
    
    # - ### ### ###
    
    public $TARGET_DOMAIN;
    public $URL_TARG_FULL;
    
    public $HEADERS_USER;
    public $HEADERS_RESP;
    
    public $HTML_ANSWER_RAW;
    
    
    # - ### ### ###
    #   NOTE:
	
	public static function simpleProxyTest($domain)
	{
		$site = 'https://'.$domain.'/';  # NOTE: Написано спонтанно, на коленке, за 10 минут.  Может вылетать.
		$R = \LibMy\RequestCURL::GET($site)['ANSWER_TEXT'][0];
		$R = str_replace('src="/' ,'src="'.$site , $R);
		$R = str_replace('content="/' ,'content="'.$site , $R);
		$R = str_replace('src=\'/' ,'src=\''.$site , $R);
		$R = str_replace('href="/' ,'href="'.$site , $R);
		$R = str_replace('action="/' ,'action="'.$site , $R);
		
		echo($R);
	}
	
	
	
    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:

	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
