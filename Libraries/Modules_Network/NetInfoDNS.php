<?php

namespace LibMy;


/**
 * Класс для любой работы с DNS.
 */
class NetInfoDNS {

    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    # NOTE:
    #  Точно робит: DNS_A - DNS_AAAA - DNS_NS - DNS_CNAME - DNS_SRV - DNS_MX
    #  Точно не робит: DNS_ANY - DNS_ALL - DNS_TXT
    #  .
    #  Если сайта нет - вернет вылет с  "DNS Query failed"
    #  Если есть, но нет записей - вернет [ ]
    #  .
    #  .


    # FINAL
    public static function checkHostIsUnderCloud($host):bool
    {
        try {
            $res = dns_get_record($host, DNS_NS); # Вылет  либо  []  либо  массивы

            if( str_contains($res[0]['target'],'ns.cloudflare.com') ||
                str_contains($res[2]['target'],'ns.cloudflare.com') ||
                str_contains($res[3]['target'],'ns.cloudflare.com') )
                return true; # "robert.ns.cloudflare.com"
        }catch(\Exception $e) {
            return false; # Вылет - хоста нет.
        }
        return false;
    }

    # FINAL
    public static function checkHostIsExist($host):bool
    {
        try {
            dns_get_record($host, DNS_A); # Вылет  либо  []  либо  массивы
            return true;
        }catch(\Exception $e) {
            return false; # Вылет - хоста нет.
        }
    }

    # FINAL-Почти
    public static function getInfoByHost($host):array
    {

        $targets = [
            # [ 'type' => DNS_SRV,  'arrKey' => 'SRV', 'targetKey'=>'' ],
            # [ 'type' => DNS_CNAME,  'arrKey' => 'CNAME', 'targetKey'=>'' ],
            [ 'type' => DNS_NS   ,  'arrKey' => 'NS'   , 'targetKey'=>'target' ],
            [ 'type' => DNS_A    ,  'arrKey' => 'A'    , 'targetKey'=>'ip'     ],
            [ 'type' => DNS_AAAA ,  'arrKey' => 'IPv6' , 'targetKey'=>'ipv6'   ],
            [ 'type' => DNS_MX   ,  'arrKey' => 'MX'   , 'targetKey'=>'target' ],

        ];

        $res = [ ];

        foreach( $targets as $t )
        {
            $type = $t['type'];
            $key  = $t['arrKey'];
            $keyTarg  = $t['targetKey'];


            try {
                $dnsRecordsArr = dns_get_record($host,$type);

                #dd($dnsRecordsArr,$res);

                if ( count($dnsRecordsArr) )
                {
                    foreach( $dnsRecordsArr as $one )
                        $res[$key]['data'] []= $one[$keyTarg];

                    sort($res[$key]['data']);
                    $res[$key]['isEmpty'] = false;
                }
                else
                {
                    $res[$key]['isEmpty'] = true;
                }


                #dd($dnsRecordsArr,$res);

                $res[$key]['status'] = 'success';
            }catch(\Exception $e) {
                #dd($e);
                $res[$key]['status'] = 'error';
            }


        }

        return $res;


        /*


        */
    }

    # FINAL
    public static function printMyDnsInfo($arr):void
    {
        $TEXT = '';

        foreach( $arr as $key=>$t )
        {
            if( $t['status'] === 'error' ) { $TEXT .= $key.': ERROR'.PHP_EOL.PHP_EOL.PHP_EOL;  continue; }
            if( $t['isEmpty'] )            { $TEXT .= $key.': ERROR'.PHP_EOL.PHP_EOL.PHP_EOL;  continue; }

            $text = $key.': '.PHP_EOL;

            foreach( $t['data'] as $one )
                $text .= '    '.$one.PHP_EOL;

            $TEXT .= $text.PHP_EOL.PHP_EOL;
        }
        dump($TEXT);
    }

    /*
        $site = [
        'gmail.com',
        '2domains.ru',
        'support.google.com',
        'firstvds.ru',
        '123.ru',
        'phpclub.ru',
        'yandex.ru',
        'google.com',
        'php.su', # не существ
        #'',
        ];
        DNSer::printMyDnsInfo(DNSer::getInfoByHost($site[0]));
        dd(DNSer::checkHostIsUnderCloud($site[0]));
        dd(DNSer::checkHostIsExist($site[0]));
        dd(DNSer::getInfoByHost($site[0]));
        dd( $res );
    */

} # End class


