<?php

namespace LibMy;

# Из Laravel
use Illuminate\Support\Facades\Session;

# Хелперы

/**
 * Главный класс для работы с сессией. Все операции только через него.
 * Много методов-прокладок, но так удобнее и проще.
 *
 * https://laravel.ru/docs/v5/session
 */ # ДатаВремя создания: 1 версия=030421 0120 | переФорматирование и доработка=190921
class SessionMy
{
    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Операции с отдельными ключами.

    # NOTE: ВысокоУровневые операции, Вторичные.

    /**
     * Создать ключ если его нет.
     * @param string $key
     * @param string $default Значение, если будем создавать.
     */
    public static function keyCreateIfNeed($key, $default=null)
    {
        if( ! self::keyExist($key))
            self::keySet($key, $default);
    }


    /**
     * Получить значение одного ключа + сразу запаковать в JSON.
     * @param string $key
     * @return string
     */
    public static function keyGetJson($key)
    {
        return json_encode(self::keyGet($key));
    }


    /**
     * Очистить значение одного ключа + создать если нет.
     * @param string $key
     * @param string $default Значение
     */
    public static function keyClear($key, $default=null)
    {
        # Без проверок
        self::keySet($key, $default);
    }


    /**
     * Узнать тип данных ключа.
     * @param string $key
     * @return null|mixed Если не существует, то Null
     */
    public static function keyType($key)
    {
        if( ! self::keyExist($key) )
            return null;

        $val = self::keyGet($key);

        return gettype($val);
    }


    # - ###
    # NOTE: НизкоУровневые операции, Первичные.

    /** NOTE: Первичный.
     * Проверка существования одного ключа.
     * @param string $key
     * @return bool
     */
    public static function keyExist($key):bool
    {
        return Session::exists($key); # Даже если значение=null
        #return session()->exists($key);
    }

    /** NOTE: Первичный.
     * Получить значение одного ключа. Без проверок.
     * @param string $key
     * @return null|mixed  Если ключа нет - вернет null
     */
    public static function keyGet($key)
    {
        # Без проверок
        return Session::get($key);
        #return session($key);
        #return session()->get($key); # Аналог
    }

    /** NOTE: Первичный.
     * Установить значение одного ключа.
     * @param string $key
     * @param mixed $val
     */
    public static function keySet($key, $val)
    {
        # Без проверок
        Session::put($key, $val);
        #session([$key => $val]);
    }

    /** NOTE: Первичный.
     * Полностью удалить ключ.
     * @param string $key
     */
    public static function keyForget($key)
    {
        # Без проверок
        Session::forget($key);
        #session()->forget($key);
    }



    # - ### ### ###
    #   NOTE: Операции над всей сессией целиком.


    #  IMPORTANT: !!!!! Сохраняет сессию даже если сразу после был вызван dd()
    #   Может спонтанно перестать работать, тогда уйти на любой роут с view(), что бы полчить куки.
    #   Без вызова этого метода не сохраняет изменения.
    #   Вызывать явно, когда нужно по логике.
    /** NOTE: Первичный.
     * Сохранить сессию и все внесенные в неё изменения.
     */
    public static function sessionSave( )
    {
        # Все виды сейва
        session()->save(); # РАБОТАЕТ в соло.
        Session::save(); # РАБОТАЕТ в соло.

        #Requester::getObjectRequest()->session()->save(); # хз
    }


    /** NOTE: Первичный.
     * Полностью очистить сессию.  ID Сессии не меняется.
     */
    public static function sessionForgetAll( )
    {
        Session::flush();
        #session()->flush();
    }


    /** NOTE: Первичный.
     * Получить полную сессию.
     * @return array  Может вернуть пустой массив "[ ]"
     */
    public static function sessionGetAll( ):array
    {
        return Session::all();
        #return session()->all();
    }

    # - ###

    /**
     * Получить полную сессию в виде JSON.
     * @return string Если сессия пуста, то вернет "[ ]"
     */
    public static function sessionGetAllJson( ):string
    {
        return json_encode(self::sessionGetAll());
    }


    /**
     * Есть ли хоть 1 ключ в сессии.
     * @return bool
     */
    public static function sessionIsEmpty( ):bool
    {
        return (bool) ! self::sessionKeysCount();
    }


    /**
     * Получить количество ключей в сессии.
     * @return int
     */
    public static function sessionKeysCount( ):int
    {
        $keys = array_keys(self::sessionGetAll());
        # NOTE: Если сессия пуста, то он так и вернет пустой массив.

        if( empty($keys) )
            return 0;
        else
            return count($keys);
    }


    /**
     * Получить массив со списком существующих ключей 1лвл.
     * @return array Всегда массив. Может быть пустым "[ ]".
     */
    public static function sessionKeysList( ):array
    {
        # NOTE: Если сессия пуста, то он так и вернет пустой массив.
        return array_keys(self::sessionGetAll());
    }







    # - ### ### ###
    #   NOTE: Тестовые


    /** IMPORTANT
     * Специальный тест для провеки сломанности сессии - были ли получены куки?
     * Подробности проблемы и решения внутри.
     */
    public static function sessionWorkTest( )
    {
        $text = '';

        $text .= PHP_EOL.'Симптомы сломанной:'.
                 PHP_EOL.'1. Каждое обновление новый файл в \storage\framework\sessions.'.
                 PHP_EOL.'2. _token меняется при обновлениях'.
                 PHP_EOL.'3. В дампе сессии всегда только _token, при этом в файлах сессий переменные сохранились'.
                 PHP_EOL.'4. Абсолютно пустые куки. Нет ключа сессии.'.PHP_EOL;

        $text .= PHP_EOL.'Для нормальной работы 100% нужны куки.   100% достаточно только ключа myapp_session'.PHP_EOL;

        $text .= PHP_EOL.'Предположительная суть проблемы:'.PHP_EOL;
        $text .= 'Он не может отослать куки с ID сессии прямо из экшена.  Видимо не хватает каких-то посредников и тд.'.PHP_EOL;

        $text .= PHP_EOL.'Как вявлять проблемные места и факт починки: Открывать разные страницы с открытым окном кук. Если появились, значит можно уходить куда надо.'.PHP_EOL;

        $text .= PHP_EOL.'Суть проблемы: При обращении к бэкенду, только некоторые методы приводят к отсылке кук (например метод view()). Если просто дернуть голый @test, то куки не отошлются, нет триггеров для этого.'.PHP_EOL;
        $text .= 'РЕШЕНИЕ: Куки 100% присылаются при открытии ЛЮБОЙ вьюхи через view().   Надо перейти на любой вьюшный роут(напр главная стр), получить куки с сессией. И уже с ними идти на тестовые и работать там с сессией.'.PHP_EOL;

        #dump($text);

        $res = 'Было:  '.self::keyGet('SESSION-BROKEN-TEST').' => '.self::keyGet('_token').PHP_EOL;
        #self::sessionDUMP();

        self::keySet('SESSION-BROKEN-TEST',(string) random_int(10, 99));
        self::sessionSave();

        $res .= 'Стало: '.self::keyGet('SESSION-BROKEN-TEST').' => '.self::keyGet('_token').PHP_EOL.PHP_EOL;
        $res .= 'Работает: Числа меняются, токен одинаковый'.PHP_EOL;
        $res .= 'Сломана: Разные токены, меняются.  1 числа нет.   2 число меняется.'.PHP_EOL;
        $res .= 'РЕШЕНИЕ: Зайти на любую страницу с view() и получить новые куки.'.PHP_EOL;

        dump($res, $text);
        #self::sessionDD();
    }


    public static function sessionDUMP( )
    {
        dump(self::sessionGetAll());
    }
    public static function sessionDD( )
    {
        dd(self::sessionGetAll());
    }

    # Только для тестов.
    public static function sessionSaveAndExit( )
    {
        self::sessionSave();
        exit('Exit: '.__METHOD__);
    }





    # - ### ### ###
    #   NOTE:

    #public static function sessionClearAll( )
    #{  А есть ли смысл в этом методе?
    #    # через цикл по каждому ключу
    #}


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
