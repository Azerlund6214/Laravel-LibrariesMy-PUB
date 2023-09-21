<?php

namespace LibMy;



/**
 * Универсальный класс для генерации и выдачи HTML для добавки любых фавиконов.
 * Сразу все теги и размеры. Все по полной программе.
 * Сами фавиконы хранятся в специальной папке, подготовлены заранее.
 * !!! Используется в продакшене, везде.
 */ # ДатаВремя создания: 100121 / Перенос в новый файл 021021
class FrontHeadGen_Favicon
{
    # - ### ### ###
    #   NOTE: Свои поля


    # NOTE: Имя файлов = ПРЕФИКС-размер.png
    public static $arrFavPrefixes = array(
        'PNG'     => 'favicon',
        'ANDROID' => 'android-icon',
        'APPLE'   => 'apple-icon',
        'ICO'     => 'favicon',
        'MSFT'     => 'ms-icon',
        # Еще можно добавить SVG, но юзлесс.
        # Без дефисов
    );

    public static $arrFavSets = array(

        # NOTE: - ЭТАЛОН. Тут все возможные размеры.  # В ico 'EMPTY' значит отсутствие префикса в имени
        'p-sovdep' => [
            'PNG'     => [ '16','32','96','128' ], # Еще бывает 48 192 228
            'ANDROID' => [ '36','48','72','96','144','192' ],
            'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ], # последние 2 это 192x192
            'ICO'     => [ 'EMPTY' ],
            'MSFT'    => [ '70','144','150','310' ],
        ],

        'p-awc' => [
            'PNG'     => [ '16','32','96' ],
            'ANDROID' => [ '36','48','72','96','144','192' ],
            'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],
            'ICO'     => [ 'EMPTY' ],
            'MSFT'    => [ '144' ],
        ],

        'fbi' => [
            'PNG'     => [ '16','32','96','128','196' ],
            'ANDROID' => [ '36','48','72','96','144','192' ],
            'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],
            'ICO'     => [ 'EMPTY' ],
            'MSFT'    => [ '70','144','150','310' ],
        ],

        # - ####

        # Только полные сеты. Неполные лежат отдельно, не в унив
        'univ-1'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-2'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-3'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-4'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-5'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-6'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-7'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-8'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-9'  => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-10' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-11' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-12' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-13' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-14' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-15' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-16' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-17' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-18' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-19' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-20' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-21' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-22' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-23' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-24' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-25' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-26' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-27' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-28' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-29' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-30' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-31' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-32' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-33' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-34' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-35' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-36' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-37' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-38' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-39' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-40' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-41' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-42' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-43' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-44' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-45' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-46' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-47' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-48' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-49' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-50' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-51' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-52' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-53' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-54' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-55' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-56' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-57' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-58' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-59' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-60' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-61' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],
        'univ-62' => [ 'PNG' => [ '16','32','96' ],'ANDROID' => [ '36','48','72','96','144','192' ],'APPLE'   => [ '57','60','72','76','114','120','144','152','180','EMPTY','PREC' ],'ICO'     => [ 'EMPTY' ],'MSFT'    => [ '70','144','150','310' ], ],

    );

    # IDEA - искать .svg и делать из него все виды .png  либо хайрез пнг

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # NOTE: Просто удобные короткие обертки.
    public static function echoFullHtml_SetCustom($setName)
    {
        self::echoGeneratedFullHtmlForSet($setName);
    }
    public static function echoFullHtml_SetFromENV()
    {
        self::echoGeneratedFullHtmlForSet('ENV_FILE');
    }

    # - ### ### ###
    #   NOTE:

    public static function getCurrentFaviconSetName()
    {
        return 'univ-1';
        #return ProjectDefines::getValue('VARIANT_FAVICON_SET');
    }

    private static function getFullPathToFavFolder($name='EMPTY')
    {
        $mainFaviconFolder = env('PROJECT_FAVICON_FOLDER'); # Главная папка со всеми фавиконами.

        if($name !== 'EMPTY')
            return $mainFaviconFolder . $name;

        return $mainFaviconFolder . self::getCurrentFaviconSetName();
    }


    private static function checkSetArrayFilled($targetName)
    {
        return array_key_exists($targetName, self::$arrFavSets);
    }


    # - ### ### ###
    #   NOTE:

    public static function generateOneHtmlString($setName,$type,$resolution)
    {
        $folderPath = self::getFullPathToFavFolder($setName);

        if(! self::checkSetArrayFilled($setName))
            return "FrontHeadGen_Favicon@generateOneHtmlString - сет $setName не найден в массиве";

        $size = $resolution.'x'.$resolution;
        $shortName = self::$arrFavPrefixes[$type];
        $fileName  = self::$arrFavPrefixes[$type].'-'.$size;

        #$size = str_pad($size, 7,' ');

        # Добавляет недостающие пробелы, что бы список был идеально ровным
        if(strlen($size) === 5)
            $a = '  ';
        else
            $a = '';


        switch($type)
        {
            case 'PNG':
            case 'ANDROID':
                if($resolution === 'EMPTY')
                    return '<link rel="icon" type="image/png" sizes="'.$size.'"'.$a.'   href="'.$folderPath.'/'.$shortName.'.png">';
                else
                    return '<link rel="icon" type="image/png" sizes="'.$size.'"'.$a.'   href="'.$folderPath.'/'.$fileName.'.png">';
                break;

            case 'APPLE':
                if($resolution === 'EMPTY')
                    return '<link rel="apple-touch-icon" sizes="192x192"   href="'.$folderPath.'/'.$shortName.'.png">';
                elseif($resolution === 'PREC')
                    return '<link rel="apple-touch-icon" sizes="192x192"   href="'.$folderPath.'/'.$shortName.'-precomposed.png">';
                else
                    return '<link rel="apple-touch-icon" sizes="'.$size.'"'.$a.'   href="'.$folderPath.'/'.$fileName.'.png">';
                break;

            case 'ICO':
                if($resolution === 'EMPTY')
                    return '<link rel="shortcut icon" href="'.$folderPath.'/'.$shortName.'.ico">';
                else
                    return '<link rel="shortcut icon" href="'.$folderPath.'/'.$fileName.'.ico">';
                #<link rel="shortcut icon" href="/the_favicon/favicon.ico">
                break;

            case 'MSFT':
                #return '<link rel="shortcut icon" href="'.$folderPath.'/'.$fileName.'.ico">';
                return '<meta name="msapplication-TileImage" content="'.$folderPath.'/'.$fileName.'.png">';
                break;
        }

    }

    # NOTE: Отступ - 1 пустая строка сверху и снизу.
    public static function echoGeneratedFullHtmlForSet($setName='ENV_FILE')
    {
        if($setName === 'ENV_FILE')     $setName = env('PROJECT_FAVICON_NAME');

        if($setName === 'EMPTY')     $setName = self::getCurrentFaviconSetName();
        if($setName === 'RANDOM') $setName = array_rand(self::$arrFavSets,1);

        if(! self::checkSetArrayFilled($setName))
            return "FrontHeadGen_Favicon@generateFullHtmlForSet - сет $setName не найден в массиве";

        # - #####

        $finalArr = array();

        $finalArr []= '';
        $finalArr []= '<!-- *** PROJECT FAVICON *** -->';
        foreach( self::$arrFavSets[$setName] as $type=>$arrSizes)
        {
            $finalArr []= '';

            if($type === 'PNG')     $finalArr []= '    <!-- Стандартные PNG-Иконки -->';
            if($type === 'ANDROID') $finalArr []= '    <!-- Иконки для ANDROID -->';
            if($type === 'APPLE')   $finalArr []= '    <!-- Иконки для APPLE -->';
            if($type === 'ICO')     $finalArr []= '    <!-- Иконки для старых браузеров -->';
            if($type === 'MSFT')
            {
                $finalArr []= '    <!-- Иконки для MSFT (без манифеста) -->';
                $finalArr []= '    <meta name="theme-color" content="#ffffff">';
                $finalArr []= '    <meta name="msapplication-TileColor" content="#ffffff">';
            }

            foreach($arrSizes as $oneSize)
            {
                $res = self::generateOneHtmlString($setName, $type, $oneSize);
                $finalArr []= '    '.$res;

            }
        }
        $finalArr []= '';
        $finalArr []= '<!-- *** PROJECT FAVICON *** -->';
        $finalArr []= '';
        $finalArr []= '';

        #echo implode(PHP_EOL, $finalArr);
        #dd($finalArr, implode(PHP_EOL, $finalArr));

        # Делаю отступы в 2 таба, что бы было ровно.
        foreach( $finalArr as &$one)
            $one = '        '.$one;

        #dd($finalArr);
        echo implode(PHP_EOL, $finalArr);

        return;
    }

    # - ### ### ###
    #   NOTE:

    /*  Мои заметки из старого файла шаблона

       ГУГЛ ХРОМ (на локальном серве) ПОДТЯНУЛ favicon-32x32.png
       ЛИСА (на локальном серве) ПОДТЯНУЛА apple-icon.png (192)  +  favicon-16x16.png

       <link rel="shortcut icon" href="url-адрес">
       type="image/x-icon"

       <!-- <link rel="manifest" href="/manifest.json"> -->

   */

    /*
        laravel 16x16 <link rel="shortcut icon" href="https://laravel.ru/favicon.ico" type="image/x-icon">

        хабр
        <link rel="apple-touch-icon" sizes="180x180" href="https://dr.habracdn.net/habr/5fec4973/images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="https://dr.habracdn.net/habr/5fec4973/images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="https://dr.habracdn.net/habr/5fec4973/images/favicon-16x16.png">

        <link rel="apple-touch-icon" sizes="180x180" href="/static/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/static/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/static/favicon/favicon-16x16.png">

        делать такой png
        <link rel="icon" href="/img/favicon-128.png" sizes="16x16 32x32 64x64 128x128" type="image/png"/>
        <link rel="apple-touch-icon" href="/img/favicon-128.png">

        <link rel="shortcut icon" href="/the_favicon/favicon.ico?v=zX7n49rwEM">
        <link rel="apple-touch-icon" sizes="180x180" href="/the_favicon/apple-touch-icon.png?v=zX7n49rwEM">

        <link rel="icon" type="image/png" href="http://www.example.com/image.png">
    */

    # - ### ### ###
    #   NOTE:

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться

    */
} # End class
