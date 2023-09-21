<?php
    # - ### ### ###
    # - ### ### ###
    #   NOTE: Динамически подключит все *.php во всех папках.
    #    Бесполезен если есть композер, незаменим если его нет.

    $pathToLibs = __DIR__; # "...\Libraries"

    foreach ( scandir($pathToLibs) as $pathFolder)
    {
        if( in_array($pathFolder,['.','..']) ) continue;
        if( ! is_dir($pathToLibs.'/'.$pathFolder) ) continue;

        foreach ( glob("$pathToLibs\\$pathFolder\*.php" ) as $pathFile)
        {
            #dump($pathFile); #dd($pathFile);
            include $pathFile;
        }
    }

    # - ### ### ###
    # - ### ### ###

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    # End class
