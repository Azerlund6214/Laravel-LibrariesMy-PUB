<?php

namespace LibMy;


/** Хелпер для очень удобной работы с файлами .json
 * Написан очень универсально и избыточно, чтоб на все случаи.
 * Крайне важный и удобный. Вообще весь файловый JSON делается через него.
 */
class FileJsoner
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    # FINAL
    public static function fileCreateIfNeed($path)
    {
        if( ! file_exists($path) )
        {
            $fp = fopen($path, 'w'); //
            fwrite($fp,'[ ]');
            fclose($fp);
        }
    }
    public static function ddIfErrorOrArray($path)
    {
        self::fileCreateIfNeed($path);

        $res = json_decode(file_get_contents($path),true);;

        $err = [];
        if( json_last_error() !== JSON_ERROR_NONE )
        {
            $err['PATH'] = $path;
            $err['LINTER'] = 'https://jsonlint.com/';
            $err['NUM'] = json_last_error();
            $err['MSG'] = json_last_error_msg();
            $err['PARSED'] = Jsoner::getDecodeErrorInfo(json_last_error_msg());

            Ancii::auchtung();
            dd($err);
        }

        return $res;
    }

    # - ### ### ###

    public static function convertTXTtoJSON( $pathTXT , $saveFileName=false)
    {
        $textTxt = file_get_contents($pathTXT);
        $arrStrings = explode(PHP_EOL , $textTxt);


        ## NOTE: Ручная обрезка
        foreach($arrStrings as &$str) $str = explode('	',$str)[0];
        #foreach($arrStrings as $key=>$str) if( strlen($str) > 10 ) unset($arrStrings[$key]);

        #dd($arrStrings);
        #foreach($arrStrings as $key=>$str) if( $str==='.' ) unset($arrStrings[$key]);
        foreach($arrStrings as $key=>$str) if( empty(trim($str)) ) unset($arrStrings[$key]);
        #foreach($arrStrings as $key=>$str) if( str_contains($str,'Open Source')) unset($arrStrings[$key]);

        #$arrFin = [];
        #foreach($arrStrings as $key=>$str) if( strstr($str,'- ') ) $arrFin []= $arrStrings[$key-1].' '.$arrStrings[$key];
        #$arrStrings = $arrFin;  dd($arrStrings);
        /*
        foreach($arrStrings as $key=>$str)  # NOTE: не робит  либо тестить
        {
            $strAr = str_split($str);

            #dump( strstr($strAr[0],"b") );
            #dd($strAr);
            foreach( $strAr as $char )
            {
                if( $char === ' ' ) continue;

                if( in_array($char ,str_split(Stringer::$alphabet_rus_all)) )
                    unset($arrStrings[$key]);
            }

        }  */



        #unset($arrStrings[632]);

        /*$i = 0;
        foreach( $arrStrings as $key=>$str )
        {
            if( $i%2 !== 0 )
                unset($arrStrings[$key]);
            $i++;
        } */

        $arrStrings = array_unique($arrStrings);
        $arrStrings = array_values($arrStrings);
        $arrStrings = array_unique($arrStrings);

        #dd($arrStrings);
        $json = json_encode($arrStrings,JSON_PRETTY_PRINT);
        #dd( json_decode($json,true) );


        if( ! $saveFileName )
            return $json;

        file_put_contents($saveFileName , $json);
        dump('Сохранено в '.$saveFileName, 'Count = '.count($arrStrings));
        return $json;
    }

    # - ### ### ###

    #$JSON_raw = str_replace(PHP_EOL, '', $JSON_raw);
    #$JSON_raw = str_replace("\n", '', $JSON_raw);

    public static function getBase_FullAsString($path) { return   file_get_contents($path); }
    public static function getBase_FullAsArray( $path ){ return self::ddIfErrorOrArray($path); }

    public static function getBase_KeysCount($path){ return count(self::getBase_KeysFullArr($path)); }
    public static function getBase_KeysFullArr( $path){ return array_keys(self::getBase_FullAsArray($path)); }
	
	public static function isEmpty($path):bool{ return ( strlen( file_get_contents($path) ) <= 3 ); }

    # - ### ### ###
    # NOTE: Базовые низкоуровневые методы.

    public static function action_writeArray( $path, $arr)
    {
        self::fileCreateIfNeed($path);

        # Для асоц - пишу сразу тк ключи пофиг
        if( Arrayer::isAsoc($arr) )
            Filer::writeInBlankFile($path,
                json_encode($arr,JSON_PRETTY_PRINT));
        else
            Filer::writeInBlankFile($path,
                json_encode(
                    Arrayer::regenerateKeysNumeric($arr),JSON_PRETTY_PRINT));
        # Для номерных - надо перегенерить ключи, иначе сам превратит в асоц.
    }
    public static function action_deleteKey( $path, $key)
    {
        $arr = self::getBase_FullAsArray($path);

        if( ! count($arr) ) return;

        unset( $arr[$key] );
        self::action_writeArray($path,$arr);
    }
    public static function action_addByKey( $path, $arrData, $key='NO_KEY')
    {
        $arr = self::getBase_FullAsArray($path);

        if( $key !== 'NO_KEY' )
            $arr[$key] = $arrData;
        else
            $arr []= $arrData;

        self::action_writeArray($path,$arr);
    }


    # - ###



    # - ###
    # IMPORTANT | FINAL | WORK
    private static function getterUNIV_ONE( $p, $select, $what, $delete)
    {
        $ARR=self::getBase_FullAsArray($p);

        if( ! count($ARR) )
            return 'EMPTY_JSON';

        # - ###

        $KEY = '';
        switch( $select )
        {
            case 'FIRST':  $KEY = array_key_first($ARR);    break;
            case 'RAND': $KEY = array_rand($ARR);  break;
            case 'LAST':   $KEY = array_key_last($ARR);    break;
        }

        # - ###

        $RESULT = '';
        switch( $what )
        {
            case 'ELEM': $RESULT = [ 'KEY'=>$KEY , 'VAL'=>$ARR[$KEY] ];  break;
            case 'VAL':  $RESULT = $ARR[$KEY];              break;
            case 'KEY':  $RESULT = $KEY;      break;
        }

        # - ###

        if( $delete )
        {   # NOTE: Можно через мой метод, но тогда будет лишние чтение-запись. Поэтому прямо тут.
            unset( $ARR[$KEY] );
            self::action_writeArray($p,$ARR);

            if( $what === 'ELEM' )
                $RESULT['COUNT_REMAIN'] = count($ARR);
        }

        # - ###

        return $RESULT;

        # - ###
    }

    # - ###
    public static function getElem_First($path){ return self::getterUNIV_ONE($path,'FIRST','ELEM',false);}
    public static function getElem_Rand ($path){ return self::getterUNIV_ONE($path,'RAND' ,'ELEM',false);}
    public static function getElem_Last ($path){ return self::getterUNIV_ONE($path,'LAST' ,'ELEM',false);}

    public static function getElem_FirstAndDel($path){ return self::getterUNIV_ONE($path,'FIRST','ELEM',true);}
    public static function getElem_RandAndDel ($path){ return self::getterUNIV_ONE($path,'RAND' ,'ELEM',true);}
    public static function getElem_LastAndDel ($path){ return self::getterUNIV_ONE($path,'LAST' ,'ELEM',true);}
    # - ###
    public static function getValue_First($path){ return self::getterUNIV_ONE($path,'FIRST','VAL',false);}
    public static function getValue_Rand ($path){ return self::getterUNIV_ONE($path,'RAND' ,'VAL',false);}
    public static function getValue_Last ($path){ return self::getterUNIV_ONE($path,'LAST' ,'VAL',false);}

    public static function getValue_FirstAndDel($path){ return self::getterUNIV_ONE($path,'FIRST','VAL',true);}
    public static function getValue_RandAndDel ($path){ return self::getterUNIV_ONE($path,'RAND' ,'VAL',true);}
    public static function getValue_LastAndDel ($path){ return self::getterUNIV_ONE($path,'LAST' ,'VAL',true);}
    # - ###
    public static function getKey_First($path){ return self::getterUNIV_ONE($path,'FIRST','KEY',false);}
    public static function getKey_Rand ($path){ return self::getterUNIV_ONE($path,'RAND' ,'KEY',false);}
    public static function getKey_Last ($path){ return self::getterUNIV_ONE($path,'LAST' ,'KEY',false);}

    public static function getKey_FirstAndDel($path){ return self::getterUNIV_ONE($path,'FIRST','KEY',true);}
    public static function getKey_RandAndDel ($path){ return self::getterUNIV_ONE($path,'RAND' ,'KEY',true);}
    public static function getKey_LastAndDel ($path){ return self::getterUNIV_ONE($path,'LAST' ,'KEY',true);}
    # - ###

    # - ### ### ###
    # NOTE: Работа сразу с несколькими.  На базе одинарных методов.
    #  Неэффективно по оптимизации тк каждый раз чтение/запись.
    #  Решение - писать аналог getterUNIV_ONE, но для мульти, с циклами внутри.
    #  Сейчас вообще не актуально, так что пофиг.

    public static function getElemS_Rand ($path,$count){ $RES = []; foreach( range(1,$count) as $i) $RES []= self::getElem_Rand ($path); return $RES; }
    public static function getValueS_Rand($path,$count){ $RES = []; foreach( range(1,$count) as $i) $RES []= self::getValue_Rand($path); return $RES; }

    # Мульти методы пишу только нужные.





    # - ### ### ###


    # - ### ### ###
    #   NOTE:


    /*
    function fileJson_AddArrToJson($path, $arrData)
    {
        # Получаю старый
        $json = FileJsoner::getBase_FullAsArray($path);

        foreach($arrData as $one) # Добавка
            $json []= $one;

        # Запись обновленного обратно
        writeArray($path, $arrData);
    }*/


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
