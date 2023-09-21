<?php

namespace LibMy;


/**
 * Универсальный класс для выдачи готового HTML/CSS с заготовками частей страницы.
 * Фон body, полоса прокрутки, цвета ссылок и прочее.
 * Используется в продакшене.
 */ # ДатаВремя создания: 041021   Фулл перепись: 080123
class FrontHeadGen_Selectable
{
    # - ### ### ###
    #   NOTE: Свои поля


    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # NOTE: Полечение данных из ENV. Вынесено отдельно.
    public static function getMainFolder_ENV():string
    {
        return env('PROJECT_SELECTABLE_FOLDER');
    }
    public static function getAllTypes_ENV():array
    {
        return [
            'text-sel-color' => env('PROJECT_SELECTABLE_TextSelColor'),
            'scrollbar'      => env('PROJECT_SELECTABLE_Scrollbar'),
            'body-bg-color'  => env('PROJECT_SELECTABLE_BodyBgColor'),
            'link-a-color'   => env('PROJECT_SELECTABLE_LinkTagColor'),
        ];
    }

    # - ### ### ###
    #   NOTE:


    public static function echoCssLinkFor_TextSelColor($variant='ENV_FILE'):void
    {
        self::echoCssLinkFor_UNIV('text-sel-color', $variant);
    }
    public static function echoCssLinkFor_Scrollbar($variant='ENV_FILE'):void
    {
        self::echoCssLinkFor_UNIV('scrollbar', $variant);
    }
    public static function echoCssLinkFor_BodyBgColor($variant='ENV_FILE'):void
    {
        self::echoCssLinkFor_UNIV('body-bg-color', $variant);
    }
    public static function echoCssLinkFor_LinkTagColor($variant='ENV_FILE'):void
    {
        self::echoCssLinkFor_UNIV('link-a-color', $variant);
    }


    public static function echoCssLinkFor_UNIV( $TYPE, $variant='ENV_FILE'):void
    {

        if( $variant === 'ENV_FILE' )
            $variant = self::getAllTypes_ENV()[$TYPE];

        switch($TYPE)
        {
            case 'OFF': break;
            case 'text-sel-color': $fullPath = self::getMainFolder_ENV().'/text-sel-color/text-sel-color-'.$variant; break;
            case 'scrollbar':      $fullPath = self::getMainFolder_ENV().'/scrollbar/scrollbar-'.$variant; break;
            case 'body-bg-color':  $fullPath = self::getMainFolder_ENV().'/body-bg-color/body-bg-color-'.$variant; break;
            case 'link-a-color':   $fullPath = self::getMainFolder_ENV().'/link-a-color/link-a-color-'.$variant; break;

            default: dd(__METHOD__.' - Switch-default = Тип не существует!!', $TYPE, $variant);
        }

        if($variant === "OFF")
            echo '<!-- Отключено через ENV=OFF -->';
        else
            echo '<link href="'.asset($fullPath).'.css" rel="stylesheet" type="text/css">';
    }

    # - ### ### ###




    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться

    */
} # End class
