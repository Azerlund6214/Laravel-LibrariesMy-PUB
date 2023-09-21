<?php

    namespace LibMy;
    
	
	# Старый класс, пока непереписывал

/**
 * Все про крипту.
 */
class SFCRYPTO {

	
	
	/*
	https://min-api.cryptocompare.com/data/pricemultifull?fsyms=BTC,ETH,DASH,LTC,XRP,XMR,BCH,XLM&tsyms=USD
	погуглить гайд по апи

	робит  авг23
	курс точный с гуглом +-10д на юитке

	биток {"RAW":{"BTC":{"USD":{"TYPE":"5","MARKET":"CCCAGG","FROMSYMBOL":"BTC","TOSYMBOL":"USD","FLAGS":"2052","PRICE":26014.13,"LASTUPDATE":1693530679,"MEDIAN":26016.1,"LASTVOLUME":0.00013771,"LASTVOLUMETO":3.584109315,"LASTTRADEID":"6ARVVYZ90Z9E","VOLUMEDAY":725.1051600900049,"VOLUMEDAYTO":18858615.312330805,"VOLUME24HOUR":27795.724955550002,"VOLUME24HOURTO":738917752.3961023,"OPENDAY":25935.96,"HIGHDAY":26058.42,"LOWDAY":25927.8,"OPEN24HOUR":27198.15,"HIGH24HOUR":27574.12,"LOW24HOUR":25670.77,"LASTMARKET":"itBit","VOLUMEHOUR":104.60605742999964,"VOLUMEHOURTO":2722221.515184911,"OPENHOUR":26047.39,"HIGHHOUR":26053.42,"LOWHOUR":26009.2,"TOPTIERVOLUME24HOUR":27795.724955550002,"TOPTIERVOLUME24HOURTO":738917752.3961023,"CHANGE24HOUR":-1184.0200000000004,"CHANGEPCT24HOUR":-4.353310795035694,"CHANGEDAY":78.17000000000189,"CHANGEPCTDAY":0.3013962081989712,"CHANGEHOUR":-33.2599999999984,"CHANGEPCTHOUR":-0.12769033672854901,"CONVERSIONTYPE":"direct","CONVERSIONSYMBOL":"","CONVERSIONLASTUPDATE":1693530679,"SUPPLY":19472868,"MKTCAP":506569719624.84,"MKTCAPPENALTY":0,"CIRCULATINGSUPPLY":19472868,"CIRCULATINGSUPPLYMKTCAP":506569719624.84,"TOTALVOLUME24H":135639.34496316075,"TOTALVOLUME24HTO":3544375702.9446898,"TOTALTOPTIERVOLUME24H":132151.2682802893,"TOTALTOPTIERVOLUME24HTO":3453636422.666503,"IMAGEURL":"/media/37746251/btc.png"}}},"DISPLAY":{"BTC":{"USD":{"FROMSYMBOL":"?","TOSYMBOL":"$","MARKET":"CryptoCompare Index","PRICE":"$ 26,014.1","LASTUPDATE":"Just now","LASTVOLUME":"? 0.0001377","LASTVOLUMETO":"$ 3.58","LASTTRADEID":"6ARVVYZ90Z9E","VOLUMEDAY":"? 725.11","VOLUMEDAYTO":"$ 18,858,615.3","VOLUME24HOUR":"? 27,795.7","VOLUME24HOURTO":"$ 738,917,752.4","OPENDAY":"$ 25,936.0","HIGHDAY":"$ 26,058.4","LOWDAY":"$ 25,927.8","OPEN24HOUR":"$ 27,198.2","HIGH24HOUR":"$ 27,574.1","LOW24HOUR":"$ 25,670.8","LASTMARKET":"itBit","VOLUMEHOUR":"? 104.61","VOLUMEHOURTO":"$ 2,722,221.5","OPENHOUR":"$ 26,047.4","HIGHHOUR":"$ 26,053.4","LOWHOUR":"$ 26,009.2","TOPTIERVOLUME24HOUR":"? 27,795.7","TOPTIERVOLUME24HOURTO":"$ 738,917,752.4","CHANGE24HOUR":"$ -1,184.02","CHANGEPCT24HOUR":"-4.35","CHANGEDAY":"$ 78.17","CHANGEPCTDAY":"0.30","CHANGEHOUR":"$ -33.26","CHANGEPCTHOUR":"-0.13","CONVERSIONTYPE":"direct","CONVERSIONSYMBOL":"","CONVERSIONLASTUPDATE":"Just now","SUPPLY":"? 19,472,868.0","MKTCAP":"$ 506.57 B","MKTCAPPENALTY":"0 %","CIRCULATINGSUPPLY":"? 19,472,868.0","CIRCULATINGSUPPLYMKTCAP":"$ 506.57 B","TOTALVOLUME24H":"? 135.64 K","TOTALVOLUME24HTO":"$ 3.54 B","TOTALTOPTIERVOLUME24H":"? 132.15 K","TOTALTOPTIERVOLUME24HTO":"$ 3.45 B","IMAGEURL":"/media/37746251/btc.png"}}}}
	*/
	
	
    # IMPORTANT - Юзается в расчет счетов к оплате
    # Вернет цену крипты в нужной валюте.
    # ### Только 1 цена за раз.  Никаких списокв и тд.
    public static function getCryptoCurrencyRates($source, $baseCrypto, $target, $strongMode=false)
    {
        $result = -1;

        switch ($source)
        {
            case 'COINBASE':
                # https://developers.coinbase.com/api/v2#get-currencies
                #$data = file_get_contents('https://api.coinbase.com/v2/exchange-rates?currency='.$baseCrypto);

                # NOTE: BTC на сумму 100$.      Итог=Юзать SPOT
                #  /buy=99.53$   /sell=100.54$   /spot=100.06
                #  При счете на 49$ Среднее отклонение в разной крипте по СПОТУ - около 10 центов. максимум 25-30.

                $url = 'https://api.coinbase.com/v2/prices/'.$baseCrypto.'-'.$target.'/spot';  # /buy /sell   #/spot
                $answerJson = file_get_contents($url);
                $answer = json_decode($answerJson,true);
                #dd($answer);

                # TODO:  Выдетает большая ошибка если в file_get_contents не существует страницы (404)

                # TODO: Тут проверка что даные пришли и что не отказали

                #if( array_key_exists($target, $answer['data']['amount']) )
                # костыль
                # Явно проверяю, что ответ похож на успешный. Тк я не знаю как выглядят ошибки.
                if( count($answer['data'])===3 && $answer['data']['currency']===$target)
                    $result = $answer['data']['amount'];
                else
                    $result = -999;
                break;


            case 'COINMARKET':


                break;

            case 'BINANCE-USDT':
                # https://api.binance.com/api/v1/ticker/allPrices
                # https://api.binance.com/api/v1/ticker/price?symbol=LTCBTC   !!!!!!
                # https://api.binance.com/api/v1/klines?symbol=LTCBTC&interval=1d&limit=1

                break;

            case '1234':
                # Еще идеи, но платное или надо регаться,  что-то тут криво
                # https://cryptoapis.io/

                # Биток, любой фиат.  Только BTC  https://blockchain.info/ru/ticker
                break;

            default: dd('SF@getCryptoCurrencyRates() - такой источник не прописан - '.$source);
        }


        if($strongMode)
        {
            $resultArr = array();

            if($result === -999)
                $resultArr['error'] = true;
            else
                $resultArr['error'] = false;

            $resultArr['source'] = $source;
            $resultArr['url'] = $url;
            $resultArr['from'] = $baseCrypto;
            $resultArr['to'] = $target;

            $resultArr['price'] = $result;

            $resultArr['full_answer_arr'] = $answer['data'];
            $resultArr['exchange_full_answer_json'] = $answerJson;

            # Для удобства логов.
            $resultArr['this_method_answer_arr_json'] = json_encode($resultArr);

            return $resultArr;

        }


        return $result;

    }

    


} # End class


