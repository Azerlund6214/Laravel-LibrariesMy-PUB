<?php

namespace LibMy;



/** Универсальный класс для работы с JSON. 230921 */
class Jsoner
{
    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:


    public static function sendJsonAnswerAndExit($commonAsocArr)
    {
        header('Content-Type: application/json');

        echo json_encode($commonAsocArr);

        exit();
    }


    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:

    # NOTE: Для проверки всегда надо попытаться его раскодировать. Иначе никак.

    /**
     * Проверить JSON на валидность.
     * @param string $string Проверяемый JSON
     * @param array &$result Результат, в случае успеха. По ссылке!!!
     * @return bool
     */
    public static function checkValidAndDecodeJson($string, &$result):bool
    {
        $result = json_decode($string,1);
        return (json_last_error() === JSON_ERROR_NONE); # json_last_error is supported in PHP >= 5.3.0 only.
    }


    # Написано, тестить
    /**
     * Проверить JSON на валидность и выдать подробную информацию об ошибке.
     * @param string $string Проверяемый JSON
     * @return array
     */
    public static function getDecodeErrorInfo($string):array
    {
        $INFO = [
            'IS_ERROR' => true,
            'ERROR_CODE' => '',
            'ERROR_TEXT' => '',
            'RESULT' => '',
        ];

        // decode the JSON data
        $INFO['RESULT'] = json_decode($string);;

        // switch and check possible JSON errors
        switch (json_last_error()) # json_last_error is supported in PHP >= 5.3.0 only.
        {
            case JSON_ERROR_NONE:
                $INFO['IS_ERROR'] = false;
                $INFO['ERROR_CODE'] = 'JSON_ERROR_NONE';
                $INFO['ERROR_TEXT'] = 'No errors // JSON is valid // No error has occurred';
                break;
            case JSON_ERROR_DEPTH:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_DEPTH';
                $INFO['ERROR_TEXT'] = 'The maximum stack depth has been exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_STATE_MISMATCH';
                $INFO['ERROR_TEXT'] = 'Invalid or malformed JSON';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_CTRL_CHAR';
                $INFO['ERROR_TEXT'] = 'Control character error, possibly incorrectly encoded';
                break;
            case JSON_ERROR_SYNTAX:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_SYNTAX';
                $INFO['ERROR_TEXT'] = 'Syntax error, malformed JSON';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_UTF8';
                $INFO['ERROR_TEXT'] = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_RECURSION';
                $INFO['ERROR_TEXT'] = 'One or more recursive references in the value to be encoded';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_INF_OR_NAN';
                $INFO['ERROR_TEXT'] = 'One or more NAN or INF values in the value to be encoded';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $INFO['ERROR_CODE'] = 'JSON_ERROR_UNSUPPORTED_TYPE';
                $INFO['ERROR_TEXT'] = 'A value of a type that cannot be encoded was given';
                break;
            default:
                $INFO['ERROR_CODE'] = 'UNKNOWN';
                $INFO['ERROR_TEXT'] = 'Unknown JSON error occurred';
                break;
        }

        return $INFO;
    }



    # - ### ### ###

    /*      */

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
