<?php

namespace LibMy;

# Из Laravel
use Illuminate\Support\Facades\Cookie;


/**
 * Главный класс для работы с куками. Все операции только через него.
 * Много методов-прокладок, но так удобнее и проще.
 */ # ДатаВремя создания: 280921, на основе хелпера сессий
class CookieMy
{
    # - ### ### ###

    /**
     * IMPORTANT:
     *  Laravel хранит все значения кук в зашифрованом виде, чтоб нельзя было их изменить на клиенте.
     *  Нормальное состояние - это тарабанщина по типу eyJpdiI6IjJoM2hrQlFDWHp5b2...
     *  Если значение не такого вида, например нормально читаемое, то лара его скипнет и заменит на NULL. Будет пустой ключ.
     *  .
     *  Использовать метод Cookie::queue('key','value',300минут);
     *  .
     *  Все куки в реквесте хранятся в виде строк (массивы тоже нельзя). (Записали число 123 -> в след запросе получили "123")
     */

    /**
     * IMPORTANT: Для сохранения изменений кук надо отправить заголовки.
     *  Ситуации:
     *  return; из экшена = Сохранилось.  (тк отправился пустой response наверх)
     *  echo + сразу view() = НЕ сохранилось
     *  dump(...) = НЕ сохранилось
     *  dd(...) = НЕ сохранилось
     *  exit() = НЕ сохранилось (тк скрипт прерывается и новые заголовки не отправлены автоматически(на ларой, ни пхп))
     *  exit('fff') = НЕ сохранилось
     *  .
     *  Выводы:
     *  - При операциях с куками недопустим НИКАКОЙ промежуточный вывод на страницу. Нельзя отправлять заголовки.
     *  - Только голая логика с последующим вызовом view();
     */

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Операции с отдельными ключами.

    # NOTE: ВысокоУровневые операции, Вторичные.

    /**
     * Получить значение одного ключа + сразу распаковать JSON в массив.
     * @param string $key
     * @return string|array
     */
    public static function keyGetJson($key)
    {
        return json_decode(self::keyGet($key),1);
    }

    /**
     * Установить значение одного ключа + сразу запаковать в JSON.
     * @param string $key
     * @param mixed|string|array $val
     * @param int $min Срок годности, минут. Дефолт=1440. <br>1д=1440м 7д=10080 30д=43200 365д=525600
     */
    public static function keySetJson($key,$val,$min=1440)
    {
        self::keySet($key,json_encode($val),$min);
    }


    /**
     * Полностью удалить ключ.
     * @param string $key
     */
    public static function keyForget($key)
    {
        # Без проверок
        self::keySet($key,'Любое',-1);

        #setcookie($key,null,-1); # Вроде тоже робит, но напрямую, без шифрования. Не юзать.
        #cookie()->forget($key); # Тупо не работает
    }

    /**
     * Очистить значение одного ключа + создать если нет.
     * @param string $key
     * @param string $default Значение.
     */
    public static function keyClear($key, $default='')
    {
        # Без проверок
        self::keySet($key,$default);
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
        return Cookie::has($key); # Даже если значение=null
        #return cookie()->has($key);
        # Эти 2 полностью одинаковые
    }

    /** NOTE: Первичный.
     * Получить значение одного ключа. Без проверок.
     * @param string $key
     * @return null|mixed  Если ключа нет - вернет null
     */
    public static function keyGet($key)
    {
        # Без проверок

        return Cookie::get($key,null);
        #return request()->cookie($key,null);
        # Эти 2 полностью одинаковые
    }

    /** NOTE: Первичный. Стоит dd()
     * Установить значение одного ключа.
     * @param string $key
     * @param mixed|string|array $val
     * @param int $min Срок годности, минут. Дефолт=1440. <br>1д=1440м 7д=10080 30д=43200 365д=525600
     */
    public static function keySet($key, $val, $min=1440)
    {
        $file = ''; $line = '';
        if(headers_sent($file,$line))
        {
            dump(__METHOD__.'АХТУНГ!!!!1 - Заголовки уже отправлены!!!',$file.':'.$line);
            @dump(StackTraceUntangler::getFullStackCurrent(true,true,true,true));
            dd(debug_backtrace());
        }

        Cookie::queue($key,$val,$min);

        #$dateWhenExpired = time() + (86400 * $days);
        #setcookie($key,$val,$min);

        #cookie($key,$val,$min);
    }


    # Быстрая установка ключа TEST
    public static function debug_keySet($val='DEF')
    {
        if($val === 'DEF')
            $val = random_int(10, 99);
        self::keySet('TEST',$val);
    }

    # - ### ### ###
    #   NOTE: Операции над всей сессией целиком.

    /** NOTE: Первичный.
     * Получить полную сессию.
     * @return array  Может вернуть пустой массив "[ ]"
     */
    public static function cookieGetAll( ):array
    {
        $res = request()->cookie(); # Array|null
        if(empty($res)) # Перестраховка.
            return [];

        return $res;
    }


    # - ###

    /**
     * Получить все куки разов, в виде JSON.
     * @return string Если куков нет, то вернет "[ ]"
     */
    public static function cookieGetAllJson( ):string
    {
        return json_encode(self::cookieGetAll());
    }

    /**
     * Полностью очистить куки.  Сессия слетает(не проверял, но должна).
     */
    public static function cookieForgetAll( )
    {
        foreach( self::cookieKeysList() as $key )
            self::keyForget($key);

        # Дефолтного метода flush() нет
    }

    /**
     * Есть ли хоть 1 кука.
     * @return bool
     */
    public static function cookieIsEmpty( ):bool
    {
        return (bool) ! self::cookieKeysCount();
    }

    /**
     * Получить количество кук.
     * @return int
     */
    public static function cookieKeysCount( ):int
    {
        $keys = array_keys(self::cookieGetAll());
        # NOTE: Если куки пусты, то он так и вернет пустой массив.

        if( empty($keys) )
            return 0;
        else
            return count($keys);
    }

    /**
     * Получить массив со списком существующих ключей 1лвл.
     * @return array Всегда массив. Может быть пустым "[ ]".
     */
    public static function cookieKeysList( ):array
    {
        # NOTE: Если куков нет, то он так и вернет пустой массив.
        return array_keys(self::cookieGetAll());
    }


    # - ### ### ###
    #   NOTE: Тестовые


    public static function cookieDUMP( )
    {
        dump(self::cookieGetAll());
    }
    public static function cookieDD( )
    {
        dd(self::cookieGetAll());
    }


    # - ### ### ###
    #   NOTE:


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
