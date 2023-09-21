<?php

namespace LibMy;

# Из Laravel
use ReflectionClass;
use ReflectionException;

# Библиотеки

# Модели

/** Получение полной информации о классе и его методах
 *
 */ # ДатаВремя создания: 160923
class Reflector
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
	
	/** Получить полную информацию о классе.
	 * @param string $class 'App\MyHelpers\CarbonDT'
	 * @return array
	 * @version FINAL
	 */
    public static function parseFullClassInfo($class)
    {
    	$FIN = [
	        'CLASS' => [
	        	'NAMESPACE_FULL' => $class,
	        	'NAMESPACE' => '',
	        	'EXIST' => false,
	        	#'REF' => null,
	        	'NAME' => null,
	        	'FILE' => null,
	        	'PHPDOC_EXIST' => false,
	        ],
			
	        'METHODS' => [  ],
	        #'PROPS' => [  ],
	        'ERROR' => null,
	    ];
		
		try{
		    $REF = new ReflectionClass($class);
		    
		    $FIN['CLASS']['NAMESPACE'] = $REF->getNamespaceName();
		    $FIN['CLASS']['EXIST'] = true;
		    #$FIN['CLASS']['REF'] = $REF;
		    $FIN['CLASS']['NAME'] = $REF->getShortName();
		    $FIN['CLASS']['FILE'] = $REF->getFileName();
		    $FIN['CLASS']['PHPDOC_EXIST'] = ($REF->getDocComment() !== false);
		    
		}catch( ReflectionException $e ){ $FIN['ERROR']=$e; return $FIN; } # WORK
	    
		
	    foreach($REF->getMethods() as $M)
	    {
	    	$ARR = [
	    		#'REF_M' => $M,
	    		'NAME' => $M->getName(),
	    		'IS_STATIC' => $M->isStatic(),
	    		'IS_PUBLIC' => $M->isPublic(),
			    'PARAMS_COUNT' => count($M->getParameters()),
			    'PARAMS_STR_FULL' => '',
			    'CALL_METHOD' => '',
			    'CALL_FULL' => '',
			    'PHPDOC' => [
			    	'EXIST' => ($M->getDocComment() !== false),
				    'LEN' => strlen(($M->getDocComment())),
				    'RAW' => (($M->getDocComment())),
			    	'HAVE_VERSION'=> (($M->getDocComment() !== false) && str_contains($M->getDocComment() , '@version')),
			    	'HAVE_PARAMS' => (($M->getDocComment() !== false) && str_contains($M->getDocComment() , '@param')),
			    	'HAVE_RETURN' => (($M->getDocComment() !== false) && str_contains($M->getDocComment() , '@return')),
			    	'HAVE_TODO'   => (($M->getDocComment() !== false) && str_contains($M->getDocComment() , '@todo')),
			    ],
		    ];
	    	
		    foreach($M->getParameters() as $P)
		    {
			    $ARR['PARAMS_STR_FULL'] .= ( ( strlen($ARR['PARAMS_STR_FULL'])===0) ? '' : ' , ' ) . '$'.$P->name;
			    
			    if($P->isDefaultValueAvailable())
			    {
			    	$def = $P->getDefaultValue();
			    	#dump(gettype($def));
			    	
			    	switch( gettype($def) ) # NOTE: Надо явно писывать каждый тип
				    {
					    case 'string' : $ARR['PARAMS_STR_FULL'] .= "='{$def}'"; break;
					    case 'boolean': $ARR['PARAMS_STR_FULL'] .= "=".($def ? 'true' : 'false'); break;
					    case 'integer': $ARR['PARAMS_STR_FULL'] .= "={$def}"; break;
					    case 'array'  : $ARR['PARAMS_STR_FULL'] .= "=".json_encode($def).""; break;
					    default:        $ARR['PARAMS_STR_FULL'] .= "=TODOтипДанных-".gettype($def); break;
				    }
			    }
		    }
		    
		    $ARR['CALL_METHOD'] = "{$ARR['NAME']}( {$ARR['PARAMS_STR_FULL']} )";
		    $ARR['CALL_FULL'] = $FIN['CLASS']['NAME'].(($ARR['IS_STATIC']) ? '::' : '->') . "{$ARR['CALL_METHOD']};";
		    
		    $FIN['METHODS'][] = $ARR;
	    }
		
	    
	    
	    
    	return $FIN;
    }

    
    # - ### ### ###
    #   NOTE:
	
	
    # - ### ### ###
    #   NOTE:
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####   ʕ•ᴥ•ʔ  \(★ω★)/  (^=◕ᴥ◕=^)   ####
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
