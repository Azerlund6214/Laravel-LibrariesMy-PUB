<?php

namespace LibMy;



# NOTE: Уже спорно - Из-за особенностей хрома работает ТОЛЬКО в одной и той же вкладке. Надо сразу делать редирект на реал страницу.

/** - Модуль пуш ведомлений.
 * - Вызывать так: FrontPushNotifier::make( ТИП , Текст , Заголовок , Параметры )
 * - \App\Modules\FrontPushNotifier::make( 'success' , '' , '' );
 * - Типы сообщений = 'info','warning','success','error'
 * - Известные параметры. Точно работают = '{timeOut: 15000}'
 */ # ДатаВремя: 090121 / Перепись 041021
class FrontPushNotifier
{
    # - ### ### ###

    public static $mainKeyName = 'Toast-Notifies'; # Можно менять на любой. Toast-Notifies
    public static $defaultTimeSec = 20; # Дефолтное время показа пуша.

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Главные методы

    # Метод добавки нового пуша в сессию. При каждом добавлении все заново пишется в сессию.
    private static function addOneNotify($type, string $content, string $title='', $timeSec='DEF')
    {
        if( ! in_array($type, ['info','warning','success','error']) )
            dd(__METHOD__.' - $type = '.$type);

        # - ### Баг, найденный через 10 месяцев!! 201021
        # Явное экранирование одинарной кавычки.  success('123123 0'0',''); => success('123123 0\'0','');
        # Иначе из-за ' забагуется исполнение JS

        $content = str_replace('\'','\\\'',$content);
        $title   = str_replace('\'','\\\'',$title);

        # - ###
        # Готовлю полную строчку вызова

        if( $timeSec === 'DEF' )
            $text = "toastr.$type('$content', '$title', {timeOut: '".self::$defaultTimeSec."000' } );"; # 1сек = 1 000
        else
            $text = "toastr.$type('$content', '$title', {timeOut: '$timeSec"."000' } );"; # 1сек = 1 000

        # - ###
        # Создаю пустой ключ если его нет, пишу 1 тост и выхожу.

        if( ! SessionMy::keyExist(self::$mainKeyName) )
        {
            SessionMy::keySet(self::$mainKeyName,[$text]);
            SessionMy::sessionSave();
            return;
        }

        # - ###
        # Добавление и запись

        # Получаю текущую очередь и сразу зануляю.
        $arrNotifies = self::getKeyAndClear();

        # Добавляем 1 новый пуш в общий массив
        $arrNotifies []= $text;

        # Повторно пишем весь массив, включая новый элемент.
        SessionMy::keySet(self::$mainKeyName,$arrNotifies);
        SessionMy::sessionSave();
        #session()->flash(self::$MainKeyName, $arrNotifies);

        return;

        # Устарело
        # BUG: При добавлении нескольких пушей, в ключе сессии _flash/new/ создается 3 элемента(дубли) с именем временного ключа (toast)
        #  Исправить методами сессии не удалось.
        #  Решение - в ручную 'забывать' все лишние элементы.
        #  !!! Баг не критичен, все норм работает !!!
    }
    # Приватный !!!


    # Методы - прокладки. Для удобного вызова
    public static function make($type, $content, $title='', $time='NO')
    {
        self::addOneNotify($type, $content, $title,$time);
    }
    public static function makeError($content, $title='', $time='NO')
    {
        self::addOneNotify('error', $content, $title,$time);
    }
    public static function makeSuccess($content, $title='', $time='NO')
    {
        self::addOneNotify('success', $content, $title,$time);
    }
    public static function makeInfo($content, $title='', $time='NO')
    {
        self::addOneNotify('info', $content, $title,$time);
    }
    public static function makeWarn($content, $title='', $time='NO')
    {
        self::addOneNotify('warning', $content, $title,$time);
    }

    # Пока юзлесс
    public static function debugTestPush()
    {
        self::addOneNotify('success', random_int(0,20), random_int(0,20));
        self::addOneNotify('info',    random_int(0,20), random_int(0,20),10);
        self::addOneNotify('warning', random_int(0,20), random_int(0,20),20);
        self::addOneNotify('error',   random_int(0,20), random_int(0,20),30);

        #dd( session()->all() );
        #return;
        return redirect('/');
        #return redirect()->route('login');
    }

    # - ### ### ###
    #   NOTE: Работа с ключем сессии


    public static function sessKeyGetData():array
    {
        if( SessionMy::keyExist(self::$mainKeyName) )
            return SessionMy::keyGet( self::$mainKeyName ) ?? [];
        else
            return [];
    }

    public static function getKeyAndClear()
    {
        if( ! SessionMy::keyExist(self::$mainKeyName) )
        {
            SessionMy::keySet(self::$mainKeyName,[]);
            SessionMy::sessionSave();
            return [];
        }
        else
        {
            $buf = SessionMy::keyGet(self::$mainKeyName) ?? [];
            SessionMy::keyClear(self::$mainKeyName,[]);
            SessionMy::sessionSave();

            return $buf;
            #session()->forget(self::$MainKeyName);
        }
    }

    # Есть ли хоть одно уведомление?
    public static function sessQueueIsFilled():bool
    {
        return (bool) count(self::sessKeyGetData());
    }


    # - ### ### ###
    #   NOTE: Все для вывода на страницу


    public static function echoConnectHtml()
    {
        echo PHP_EOL.'        <!-- Toastr = Уведомления в углу = CDN =  Kb -->
        <!-- https://github.com/CodeSeven/toastr https://codeseven.github.io/toastr/demo.html -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>';

        echo PHP_EOL.PHP_EOL.'        <script>
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "10000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        </script>
        <style>
            /* Отступ от верха, чтоб не загораживал навбар */
            .toast-top-right { top: 90px; }
        </style>'.PHP_EOL;

        /*
        {{--
            info  warnin g success error
            toastr.error('I do not think that word means what you think it means.', 'заголовок')

            toastr.remove()   // Immediately remove current toasts without using animation
            toastr.clear()    // Remove current toasts using animation

            // Override global options
            toastr.success('We do have the Kapua suite available.', 'Turtle Bay Resort', {timeOut: 5000})

        --}}
        */
    }


    # Метод генерации html-JS для показа всех сообщений.
    public static function echoQueueExecHtmlJs()
    {
        if( ! SessionMy::keyExist(self::$mainKeyName) )
        {
            echo '<!-- FrontPushNotifier@echoQueueExecHtmlJs - Нет ключа сессии. -->';
            return;
        }

        if( ! self::sessQueueIsFilled() )
        {
            echo '<!-- FrontPushNotifier@echoQueueExecHtmlJs - Ключ сессии пуст. -->';
            return;
        }

        # - ###

        $queueDataArr = self::getKeyAndClear();

        $final = '<!-- FrontPushNotifier@echoQueueExecHtmlJs -->'.PHP_EOL;
        $final .= '<script>'.PHP_EOL.'$(document).ready(function() { ';

        $final .= PHP_EOL;

        $queueDataArr = array_reverse($queueDataArr); # Для вывода по порядку, если неск уведомлений.

        foreach( $queueDataArr as $one )
            $final .= PHP_EOL.$one;

        $final .= PHP_EOL;
        $final .= PHP_EOL;

        $final .= '}) </script>'.PHP_EOL;

        echo $final;

        return;
    }

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться

    */
} # End class
