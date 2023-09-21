<?php

namespace LibMy;

use Illuminate\Support\Facades\File;


/**
 * Класс-обертка для работы с папкой временных файлов.
 * Пока применимость спорная.
 */
class StorageTemp
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    # WORK
    public static function getFolderName()
    {
        return env('STORAGE_FOLDER_TEMP');
    }

    # - ### ### ###


    # WORK
    public static function getPath_OneFile($fileName):string
    {
        $folder = self::getFolderName();
        return "{$folder}/{$fileName}";
    }

    # WORK
    public static function getPath_AllFiles():array
    {
        $folder = self::getFolderName();

        $fin = [];
        foreach( File::allFiles($folder) as $file)
            if( $file->isFile() )
                $fin []= $file->getPathname(); # "Storage=Temp\test.txt"

        return $fin;
    }

    # WORK
    public static function checkFileExists($fileName):bool
    {
        return File::exists( self::getPath_OneFile($fileName) );
    }



    # WORK
    public static function deleteOneFile($fileName):bool
    {
        $path = self::getPath_OneFile($fileName);

        if( ! self::checkFileExists($fileName) )
            return true;

        return File::delete($path);
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
