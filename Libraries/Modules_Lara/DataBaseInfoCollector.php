<?php

namespace LibMy;


use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\Facades\DB;

/**
 * Простенькая прокладка для получения инфы о текущем коннекте к БД.
 * Для нужд дебага и логов.
 */
class DataBaseInfoCollector
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###


    public static function getFullDbInfo()
    {
        $FIN = [];

        $FIN['CONFIG__DB_NAME'] = DB::getConfig()['database'];

        $FIN['PDO_CONNECTED'] = true;
        $FIN['PDO_OBJECT'] = '';
        $FIN['PDO_ERROR'] = '';
        $FIN['PDO_ERROR_CODE'] = '';
        $FIN['PDO_ERROR_MSG'] = '';

        try{
            $FIN['PDO_OBJECT'] = DB::connection()->getPdo();
        }catch(\Throwable $ERR){
            $FIN['PDO_CONNECTED'] = false;
            $FIN['PDO_OBJECT'] = null;
            $FIN['PDO_ERROR'] = $ERR;
            $FIN['PDO_ERROR_CODE'] = $ERR->getCode();
            $FIN['PDO_ERROR_MSG'] = $ERR->getMessage();
        }

        return $FIN;
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
