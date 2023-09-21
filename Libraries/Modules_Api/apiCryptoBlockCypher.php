<?php

namespace LibMy;


use Carbon\Carbon;



# - ### ### ### ###
# - ###
	/* # = # = # = # */
	
	# Это оооооочень старый класс, сейчас бы писал вообще по другому.
	# Не стал разбирать и переписывать.
	# Был в продакшене.
	
	/* # = # = # = # */
# - ###
# - ### ### ### ###




/**
 * Class
 * https://www.blockcypher.com/dev/bitcoin/#transaction-api
 *
 * Classic requests, up to 3 requests/sec and 200 requests/hr
 *
 */

class apiCryptoBlockCypher # 190321 0047
{
    # - ######################################

    public static $NAME1 = ''; #
    public static $NAME2 = array(); #


    public static $BASE_URL = 'https://api.blockcypher.com/v1'; #
    public static $ALLOWED_CURR = array('BTC','ETH','LTC','DASH','DOGE'); #

    # - ######################################

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ######################################
    # - ######################################
    #   NOTE:

    # TODO: Обернуть курл в исключение

    # IMPORTANT: Главный обработчик.
    public static function request( $reqUrl )
    {
        $fin = array(
            'ERROR-HAS' => false,
            'ERROR-MSG' => '',
            'ERROR-CODE' => '',
            'RESPONSE-RAW' => '',
            'RESPONSE-FULL' => '',
        );

        # - ###  Защиты от дурака

        if( $reqUrl[0] !== '/' )
            dd(__METHOD__.' забыт слеш в начале reqUrl', $reqUrl);


        # - ###

        $headers = [
            'Accepts: application/json',
        ];

        # - ###

        $requestUrl = self::$BASE_URL . $reqUrl;
        #dd($requestUrl);

        # - ###

        $curl = curl_init(); // Get cURL resource

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));

        $fin['RESPONSE-RAW'] = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request

        # - ###

        $fin['RESPONSE-FULL'] = json_decode($fin['RESPONSE-RAW'], true); // print json decoded response

        # - ###

        if( ! empty( $fin['RESPONSE-FULL']['error'] ) )
        {
            $fin['ERROR-HAS'] = true;
            $fin['ERROR-MSG']  = $fin['RESPONSE-FULL']['error'];

            if( strstr( $fin['RESPONSE-FULL']['error'],'not found.') )
            {
                $fin['ERROR-CODE'] = 'TXID_NOT_FOUND';
            }
            else
            {
                $fin['ERROR-CODE'] = 'UNDEFINED_ERROR';
            }

            return $fin;
        }

        $fin['DATA']   = $fin['RESPONSE-FULL'];

        unset($fin['RESPONSE-RAW']); # Ибо без форматирования
        unset($fin['RESPONSE-FULL']); # Ибо дублируется

        return $fin;
    }

    # - ######################################
    #   NOTE:


    # - ######################################
    #   NOTE:

    public static function verifyAllowedCurrency( $currency )
    {
        if( ! in_array($currency,self::$ALLOWED_CURR) )
            return false;

        return true;
    }


    public static function convertSatoshiAmount( $satoshi )
    {
        $amount = $satoshi / 100000000;
        return number_format($amount,8,'.','');
    }


    public static function requestInfoByTxid( $currency , $txid )
    {
        if( ! self::verifyAllowedCurrency($currency) )
            dd(__METHOD__.' Недопустимая валюта', $currency);


        $reqUrl = '/'.strtolower($currency).'/main/txs/'.$txid;

        $fin = array(
            'REQUEST' => [   ],
            'TX_INFO' => [   ],
        );


        $fin['REQUEST'] = self::request($reqUrl);

        if( $fin['REQUEST']['ERROR-HAS'] )
            return $fin;

        # - ###
        #dd($fin);

        $fin['TX_INFO']['CURRENCY'] = $currency;
        $fin['TX_INFO']['HASH'] = $fin['REQUEST']['DATA']['hash'];

        $fin['TX_INFO']['IN_MEMPOOL'] = ( $fin['REQUEST']['DATA']['block_height'] === -1 );

        if( ! $fin['TX_INFO']['IN_MEMPOOL'] )
        {
            $fin['TX_INFO']['BLOCK-HASH'] = $fin['REQUEST']['DATA']['block_hash'];
            $fin['TX_INFO']['BLOCK-ID']   = $fin['REQUEST']['DATA']['block_height'];
        }

        # Все адреса-участнии транзы - Входные+выходные
        $fin['TX_INFO']['ARR_ALL_ADDRESSES'] = $fin['REQUEST']['DATA']['addresses'];

        $fin['TX_INFO']['CONFIRMATIONS'] = $fin['REQUEST']['DATA']['confirmations'];
        $fin['TX_INFO']['DATE_RECEIVED'] = Carbon::createFromTimeString($fin['REQUEST']['DATA']['received'])->toDateTimeString();
        if( ! empty($fin['REQUEST']['DATA']['confirmed']) )
            $fin['TX_INFO']['DATE_CONFIRMED'] = Carbon::createFromTimeString($fin['REQUEST']['DATA']['confirmed'])->toDateTimeString();

        $fin['TX_INFO']['AMOUNT_TOTAL'] = self::convertSatoshiAmount($fin['REQUEST']['DATA']['total']);
        $fin['TX_INFO']['AMOUNT_FEE']   = self::convertSatoshiAmount($fin['REQUEST']['DATA']['fees']);

        $fin['TX_INFO']['COUNT_INPUT'] = count($fin['REQUEST']['DATA']['inputs']);
        $fin['TX_INFO']['COUNT_OUT']   = count($fin['REQUEST']['DATA']['outputs']);

        #$i = 0;
        foreach( $fin['REQUEST']['DATA']['outputs'] as $i => $val )
        {
            $fin['TX_INFO']['ARR_INFO_OUT'][$i]['WALLET_TARGET'] = $val['addresses'][0];
            $fin['TX_INFO']['ARR_INFO_OUT'][$i]['AMOUNT'] = self::convertSatoshiAmount($val['value']);
            #$i++;
        }

        $fin['TX_INFO']['ARR_INFO_INPUT'] = 'Не делаю.';

        # - ###

        return $fin;
    }

    # - ######################################

    public static function requestFullAddressInfo( $currency , $address )
    {
        if( ! self::verifyAllowedCurrency($currency) )
            dd(__METHOD__.' Недопустимая валюта', $currency);


        $reqUrl = '/'.strtolower($currency)."/main/addrs/$address/full/";

        $fin = array(
            'REQUEST' => [   ],
            'TX_INFO' => [   ],
        );


        $fin['REQUEST'] = self::request($reqUrl);

        if( $fin['REQUEST']['ERROR-HAS'] )
            return $fin;

        return $fin;

        # - ###
        #dd($fin);

        $fin['TX_INFO']['CURRENCY'] = $currency;
        $fin['TX_INFO']['HASH'] = $fin['REQUEST']['DATA']['hash'];

        $fin['TX_INFO']['IN_MEMPOOL'] = ( $fin['REQUEST']['DATA']['block_height'] === -1 );

        if( ! $fin['TX_INFO']['IN_MEMPOOL'] )
        {
            $fin['TX_INFO']['BLOCK-HASH'] = $fin['REQUEST']['DATA']['block_hash'];
            $fin['TX_INFO']['BLOCK-ID']   = $fin['REQUEST']['DATA']['block_height'];
        }

        # Все адреса-участнии транзы - Входные+выходные
        $fin['TX_INFO']['ARR_ALL_ADDRESSES'] = $fin['REQUEST']['DATA']['addresses'];

        $fin['TX_INFO']['CONFIRMATIONS'] = $fin['REQUEST']['DATA']['confirmations'];
        $fin['TX_INFO']['DATE_RECEIVED'] = Carbon::createFromTimeString($fin['REQUEST']['DATA']['received'])->toDateTimeString();
        if( ! empty($fin['REQUEST']['DATA']['confirmed']) )
            $fin['TX_INFO']['DATE_CONFIRMED'] = Carbon::createFromTimeString($fin['REQUEST']['DATA']['confirmed'])->toDateTimeString();

        $fin['TX_INFO']['AMOUNT_TOTAL'] = self::convertSatoshiAmount($fin['REQUEST']['DATA']['total']);
        $fin['TX_INFO']['AMOUNT_FEE']   = self::convertSatoshiAmount($fin['REQUEST']['DATA']['fees']);

        $fin['TX_INFO']['COUNT_INPUT'] = count($fin['REQUEST']['DATA']['inputs']);
        $fin['TX_INFO']['COUNT_OUT']   = count($fin['REQUEST']['DATA']['outputs']);

        #$i = 0;
        foreach( $fin['REQUEST']['DATA']['outputs'] as $i => $val )
        {
            $fin['TX_INFO']['ARR_INFO_OUT'][$i]['WALLET_TARGET'] = $val['addresses'][0];
            $fin['TX_INFO']['ARR_INFO_OUT'][$i]['AMOUNT'] = self::convertSatoshiAmount($val['value']);
            #$i++;
        }

        $fin['TX_INFO']['ARR_INFO_INPUT'] = 'Не делаю.';

        # - ###

        return $fin;
    }



    # - ######################################
    #   NOTE:


    public static function debugMakeAllTxRequests( $onlyOne=false )
    {
        $arr = array(
            'LTC' => 'dc7495b4313e2312f603a24f717c2ceb951b61d8bfaaff3b49229753084ad82211111', # Не существ

            'BTC' => '4656513edce6ea21ee4cbf2912722bbcdd28646c93b61f68ee8ea66943c01b24', # BTC Много входов и выходов
            #'BTC' => '4656513edce6ea21ee4cbf2912722bbcdd28646c93b61f68ee8ea66943c01b24', # BTC 1/1
            'ETH' => '0x6b0944c6eed8d1584ba24301f781b80b313f72a3a0443d5189ca64ee3a72651a',
            #'LTC' => 'dc7495b4313e2312f603a24f717c2ceb951b61d8bfaaff3b49229753084ad822',
            'DASH' => 'dcf78cab2c3ecd66c70656de69e614783a845296da8e458cd09a4db2a7c08f21',
            'DOGE' => '46bb166f22b7c00ddb924028368b6b348429b7e3417fbe99ed0e478971151d78', # DOGE много/1
        );

        # не существ https://api.blockcypher.com/v1/ltc/main/txs/dc7495b4313e2312f603a24f717c2ceb951b61d8bfaaff3b49229753084ad82211111
        # BTC https://api.blockcypher.com/v1/btc/main/txs/0364a12c62b7ce4a554c8b66e12db1177d45ecc13715b7b0193f9339fa41c678
        # ETH https://api.blockcypher.com/v1/eth/main/txs/0x6b0944c6eed8d1584ba24301f781b80b313f72a3a0443d5189ca64ee3a72651a
        # LTC https://api.blockcypher.com/v1/ltc/main/txs/dc7495b4313e2312f603a24f717c2ceb951b61d8bfaaff3b49229753084ad822
        # DASH https://api.blockcypher.com/v1/dash/main/txs/dcf78cab2c3ecd66c70656de69e614783a845296da8e458cd09a4db2a7c08f21
        # DOGE https://api.blockcypher.com/v1/doge/main/txs/46bb166f22b7c00ddb924028368b6b348429b7e3417fbe99ed0e478971151d78


        foreach( $arr as $key => $val )
        {
            dump("$key => $val");
            dump(self::requestInfoByTxid($key, $val) );
            dump('=====================');

            if( $onlyOne )
                break;

            sleep(1); # i sec
        }

        dd('End');
    }


    # - ######################################
    #   NOTE:

    # - ######################################
    #   NOTE:



    # TODO:
    # TODO:
    # TODO:




    # - ######################################
    # - ######################################
    # - ######################################

    /*   свалка


    */



}
