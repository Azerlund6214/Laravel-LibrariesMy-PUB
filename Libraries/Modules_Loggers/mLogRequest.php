<?php

namespace LibMy;

use App\LogRequest;

use App\LogReqUser;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Класс для логгирования КАЖДОГО запроса к серверу и записью в БД.
 * Вызывается каждый раз.
 * В комплекте идет Модель+Таблица+Посредник
 */
class mLogRequest
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    private static $TABLE_NAME = 'log_requests';

    # - ### ### ###

    # NOTE: Скрип только для роутов артизана и сервиса.
    public static function checkLogIsNeed():bool
    {
        if ( strstr(Request::path(),'artisan') ) return false;
        if ( Request::path() === 'lr' ) return false;
        if ( Request::path() === 'lu' ) return false;
        #if ( strstr(Request::path(),'service') ) return false;
        # dump('LogRequest@createLogRow -> Это роут артизана, не пишу лог');
		
        # IMPORTANT: Оно вешает скрипт на 2000мс.  Всегда.   Решение: в ENV в бд писать сразу ip а не локалхост
        if ( ! Schema::hasTable(self::$TABLE_NAME) ) return false;
        # dump('LogRequest@createLogRow -> Таблицы логов нет, не пишу лог');
        # Скрипт только залит на хост и прямо сейчас идет запрос artisan на миграцию.
		
        return true;
    }

    


    # - ### ### ###
    #   NOTE:

	# Illuminate\Http\Response\
    public static function makeLogIfNeed_MAIN( $RESPONSE )
    {
        # - ###

        if( ! self::checkLogIsNeed() )
            return;

        # - ###
	    # $a = 1/0; # ERROR
        # - ###
	
	    $R = Requester::getFullRequestAndRouteInfo(true);
	    
        # - ###
	    
        $ARR = [
            'id_md5_IpUa' => $R['REQUEST']['ID_USER'],
            'id_this_req' => $R['REQUEST']['ID_CURRENT'],
            'id_authUser' => $R['USER']['UID'],
            
            'MCA' => $R['ROUTE']['ACTION_FULL_2'],
			
            'parReq_cnt' => $R['PARAMS']['ARR_COUNT'],
            'parReq_len' => $R['PARAMS']['JSON_LEN'],
            'parReq_json'=> $R['PARAMS']['JSON_DATA'],
			
            'parUri_cnt' => $R['ROUTE']['URI_PARAMS_COUNT'],
            'parUri_len' => $R['ROUTE']['URI_PARAMS_JSON_LEN'],
            'parUri_json'=> $R['ROUTE']['URI_PARAMS_JSON'],
			
            'ip' => $R['IP']['SUB_39'],
            'ua' => $R['UA']['SUB_256'],
            'uaExt' => $R['UA']['EXT_TEXT'],
            'uaMob' => $R['UA']['EXT_IS_MOBILE'] ? 'YES' : 'NO',
            'refSite' => $R['ORIGIN']['DOMAIN_SHORT'],
            'refUrl'  => $R['ORIGIN']['SUB_128'],

            'url_full'=> $R['ROUTE']['URL_FULL_SUB128'],
            'url_patt'=> $R['ROUTE']['URI_PATTERN'],
            'url_path'=> $R['ROUTE']['URI_PATH'],
            
            'reqTimeMs'=> $R['REQUEST']['TIME_MS'],
            'reqBaseHeadersJson'=> $R['HEADERS']['JSON_DATA'],
            
            'respStatusNum' => $RESPONSE->status(),
            'respStatusText' => $RESPONSE->statusText(),
            'respContentLen' => strlen($RESPONSE->getContent()),
            'respErrorHasAny' => ($RESPONSE->exception !== null) ? 'YES' : 'NO',
            'respErrorMsg64' => '', # Пишется ниже
            'respErrorInfo_Json' => '[ ]', # Пишется ниже
            
            'datetime_utc' => $R['DATE']['UTC'],
            'datetime_msk' => $R['DATE']['MSK'],
        ];
		
        if( $ARR['respErrorHasAny'] === 'YES' )
        {
        	$ARR['respErrorMsg64'] = Stringer::sliceTo($RESPONSE->exception->getMessage(),64);
        	
        	$ARR['respErrorInfo_Json'] = json_encode([
		        'MSG' => $RESPONSE->exception->getMessage(),
		        'FileLine' => $RESPONSE->exception->getFile().':'.$RESPONSE->exception->getLine(),
		        'Class' => (get_class($RESPONSE->exception) ?? 'UNDEF'),
	        ]);
        }
        
        # - ###
        
        $LR = LogRequest::Create($ARR);
        #dd($ARR,$LR);
        
	    # - ###
	    # Блок про лог Юзера
	    
	    if( ! LogReqUser::where('id_md5_IpUa',$ARR['id_md5_IpUa'])->count() )
	    {
		    $ARR2 = [
			    'id_md5_IpUa' => $ARR['id_md5_IpUa'],
			    'ip' => $ARR['ip'],
			    #'ip_country' => $ARR[''],
			    #'ip_info' => $ARR[''],
			    
			    'ua' => $ARR['ua'],
			    'uaExt' => $ARR['uaExt'],
			    'uaMob' => $ARR['uaMob'],
			    
			    'first_url_full' => $ARR['url_full'], # sub128
			    'first_refUrl' => $ARR['refUrl'], # sub128
			    'first_reqBaseHeadersJson' => $ARR['reqBaseHeadersJson'],
			    
			    #'respCnt_All' => $ARR[''],  Будет дефолт = 1
			    
			    'datetime_msk_first' => $ARR['datetime_msk'],
			    'datetime_msk_last' => $ARR['datetime_msk'],
		    ];
		    
		    switch( $RESPONSE->status() )
		    {
			    case 200: $ARR2['respStatusCnt_200']   = 1; break;
			    case 302: $ARR2['respStatusCnt_302']   = 1; break;
			    case 404: $ARR2['respStatusCnt_404']   = 1; break;
			    case 418: $ARR2['respStatusCnt_418']   = 1; break;
			    case 500: $ARR2['respStatusCnt_500']   = 1; break;
			    default : $ARR2['respStatusCnt_Other'] = 1;
		    }
		    
		    $LU = LogReqUser::Create($ARR2);
		    #dd($ARR2,$LU);
	    }
	    else
	    {
		    $LU = LogReqUser::where('id_md5_IpUa',$ARR['id_md5_IpUa'])->first();
		
		    switch( $RESPONSE->status() )
		    {
			    case 200: $LU->respStatusCnt_200   += 1; break;
			    case 302: $LU->respStatusCnt_302   += 1; break;
			    case 404: $LU->respStatusCnt_404   += 1; break;
			    case 418: $LU->respStatusCnt_418   += 1; break;
			    case 500: $LU->respStatusCnt_500   += 1; break;
			    default : $LU->respStatusCnt_Other += 1;
		    }
		
		    $LU->respCnt_All += 1;
		    $LU->datetime_msk_last = $ARR['datetime_msk'];
		    $LU->save();
		    #dd($LU);
	    }
	    
	    # - ###
    }



    # - ### ### ###
    #   NOTE:
	
	public static function dumpRowsByIds($begin, $end)
	{
		$rows = LogRequest::whereBetween('id', [$begin,$end])->get();
		#dd($rows);
		foreach($rows as $row)
		{
			#dd($row);
			
			dump($row->toArray());
			#dump(json_decode($row->json_data,true));
			
			dump("#### #### ####");
			dump("#### #### ####");
		}
		
		#dd('end');
	}

    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
