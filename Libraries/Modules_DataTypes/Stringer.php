<?php

namespace LibMy;




/**
 * Главный класс для УДОБНОЙ работы со строками.
 * Много методов-прокладок, но мне так удобнее и проще.
 *
 * ДатаВремя создания: 190821 0006
 */
class Stringer
{
    # - ### ### ###

    public static $alphabet_eng_big = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; # (26 символов)
    public static $alphabet_eng_sml = "abcdefghijklmnopqrstuvwxyz"; # (26 символов)
    public static $alphabet_eng_all = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"; # (52 символов)

    public static $alphabet_rus_big = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ"; # (33 символа)
    public static $alphabet_rus_sml = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя"; # (33 символа)
    public static $alphabet_rus_all = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя"; # (66 символа)

    public static $alphabet_ruseng_all = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя"; # (118 символа)


    public static $alphabet_nums = "0123456789"; # (10 символов)
    public static $alphabet_symbols = "\"#!$%&'()*+,-./:;<=>?@[\]^_`{|}~ "; # (33 символа) # С ПРОБЕЛОМ
    # Eng => все буквы = 52   + цифры = 62   + символы = 95

    public static $alphabet_eng_sml_nums = "abcdefghijklmnopqrstuvwxyz0123456789"; # (36 символов)
    public static $alphabet_eng_big_nums = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"; # (36 символов) # Для MAC-адресов
    public static $alphabet_eng_all_nums = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; # (62 символа)

    public static $alphabet_hexchars = "ABCDEF0123456789";
	public static $base58chars = "123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";

    public static $crlf          = "\x0d\x0a";

    # - ###
    #  Готовые алфавиты, используются.

    public static $alphabet_for_usernames = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; # 58
    public static $alphabet_for_passwords = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#!$%&()*+,-.:;<=>?@[]^_{}"; # Набросок     добавить часть выше
    public static $alphabet_for_all = "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюяABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789\"#!$%&'()*+,-./:;<=>?@[\]^_`{|}~ "; # 161
    # ВСЕ в одном. Нужен например для проверки комментариев к платежам и тд. Всяких больших текстов, вводимых юзером.
    # Проверка НУЖНА, тк никто не отменял другие языки(напр индия), юникод и прочие псевдосимволы.

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Первичные методы, просто удобные обертки.

    /** FINAL TESTED
     * Обрезка до нужной длины. Просто обертка.
     * @param string $str Исходная строка
     * @param int $len Целевая длина.
     * @param bool $fromEnd Брать первые или последние символы.
     * @return string
     * @todo Нет защиты от дурака, если длины противоречащие.
     */
    public static function sliceTo($str, $len, $fromEnd=false)
    {
        if(! $fromEnd)
            return substr($str,0,$len);

        return substr($str,strlen($str)-$len,$len);
    }
    

    # - ### ### ###
    #   NOTE:

    /** FINAL TESTED
     * Просто рандомный смайл.
     * @return string
     */
    public static function getRandomCuteSmile()
    {
        $arrSmiles = array(
            'ฅ^•ﻌ•^ฅ',
            'ʕ •`ᴥ•´ʔ',
            'ʕᵔᴥᵔʔ',
            'ʕ•ᴥ•ʔ',
            '\(ᵔᵕᵔ)/',
            '( ͡° ͜ʖ ͡°)',
            '☆*:.｡.o(≧▽≦)o.｡.:*☆',
            '╰(▔∀▔)╯',
            '( ‾́ ◡ ‾́ )',
            '(ﾉ◕ヮ◕)ﾉ*:･ﾟ✧',
            'o( ❛ᴗ❛ )o',
            '(.❛ ᴗ ❛.)',
            '(っ˘ω˘ς )',
            '＼(≧▽≦)／',
            '\(★ω★)/',
            '(￣▽￣)ノ',
            '(^◕ᴥ◕^)',
            '(^◔ᴥ◔^)',
            '(＾• ω •＾)',
            '(^˵◕ω◕˵^)',
            '(^=◕ᴥ◕=^)',
            'ʕ ᵔᴥᵔ ʔ',
            '╰( ͡° ͜ʖ ͡° )つ──☆*:・ﾟ',
            '( ͡° ͜ʖ ͡°)',
            '(◕‿◕✿)',
            #'',
        );

        return $arrSmiles[array_rand($arrSmiles)];
    }



    # - ### ### ###
    #   NOTE: Любые проверки строк.

    /** FINAL TESTED IMPORTANT - Проверяет все входные данные в экшенах.
     * Проверяет строку на соответствие заданному алфавиту
     * @param string $string Проверяемая строка
     * @param string $alphabet Любой свой  ЛИБО  "FOR_NAMES" = ENG eng 123  | "FOR_PASS" = то же + спецсимв. | "FOR_ALL" = Все в кучу.
     * @param array &$badCharsArr Сюда добавится недопустимые символы.
     * @param bool $getStats Просто вывести длину строки и мощность алфавита
     * @return bool
     */
    public static function verifyStringAlphabet($string , $alphabet = "FOR_NAMES", array &$badCharsArr=[] , $getStats = false )
    {
        if ($alphabet === "FOR_NAMES")
            $alphabet  = self::$alphabet_for_usernames;

        if ($alphabet === "FOR_PASS")
            $alphabet  = self::$alphabet_for_passwords;

        if ($alphabet === "FOR_ALL")
            $alphabet  = self::$alphabet_for_all;

        if( empty($string) )
            return true;


        if ($getStats)
        {
            echo "<br>Строка = " . $string;
            echo "<br>Алфавит = " . $alphabet;
            echo "<br>Длина строки = " . strlen($string);
            echo "<br>Мощность алфавита = " . strlen($alphabet);
            exit('<br>Exit');
        }

        # - ###

        $arrSymbols = str_split($string);
        $arrSymbolsUnique = array_unique($arrSymbols); # Этот подход резко снижает сложность алгоритма.
        #dump($arrSymbols,$arrSymbolsUnique);

        # Бью целевую строку на массив отдельных букв и прогоняю его
        foreach ( $arrSymbolsUnique as $charFromString)
        {
            #dump(" $charFromString - ".substr_count($alphabet, $charFromString).'шт');

            # Если символе нет в алфавите.
            if ( ! substr_count($alphabet, $charFromString) )
            {
                $badCharsArr []= $charFromString;
            }
        }

        if( count($badCharsArr) )
            return false; # Есть недопустимые символы

        return true; # Все символы допустимы
    }

    
    # - ### ### ###
    #   NOTE: Любые генераторы строк.

    /** FINAL TESTED
     * Возвращает случайную строку заданной длинны состоящую из заданного алфавита.
     * @param integer $length - Длина желаемой строки
     * @param string $alphabet - Алфавит для генерации (есть дефолтный)
     * @return string
     */
    public static function generateRandom( $length, $alphabet = "Default" )
    {
        if ( $alphabet === "Default" )
            $alphabet = self::$alphabet_eng_all;

        srand((double)microtime()*1000000); # Увеличиваем рандомность

        $alphLen = strlen($alphabet);

        $random = '';

        # Альтернатива - $random = substr(str_shuffle($alphabet), 0, $length);
        for ($i = 0; $i < $length; $i++)
            $random .= $alphabet[rand(0, $alphLen - 1)];

        return $random;
    }

    /** FINAL TESTED
     * Возвращает случайную удобночитаемую строку заданной длинны. (алфавит - маленькие англ буквы)
     * @param integer $length - Длина желаемой строки. Не менее 4, иначе баги.
     * @param string $lang - Язык = ENG / RUS
     * @return string
     */
    public static function generateRandomReadable($length = 8, $lang = 'ENG')
    {
        if($lang === 'ENG')
        {
            $c = array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','z');
            $v = array('a','e','i','o','u','y');
        }
        else  # NOTE: Есть удобный метод str_split для разбивки на символы, чтоб не писать.
        {
            $c = array('б','в','г','д','ж','з','й','к','л','м','н','п','р','с','т','ф','ч','ц','ч','ш','щ','ъ','ь');
            $v = array('а','и','о','у','ы','э','е','ё','ю','я');
        }

        srand((double)microtime()*1000000); # Увеличиваем рандомность

        $slogov = (int) ($length / 2); # Количество слогов

        if( $slogov === 0 ) # Иначе вылет по делению на 0
            return '';

        $cntC = count($c)-1;
        $cntV = count($v)-1;

        $random = '';
        for ($i = 1; $i <= $slogov; $i++)
        {
            $random .= $c[rand(0,$cntC)];
            $random .= $v[rand(0,$cntV)];
        }

        # Если нечетное количество, то добавляю в конец еще одну согласную букву.
        if( $length % $slogov !== 0 )
            $random .= $c[rand(0,$cntC)];

        # Вырезано за ненадобностью.
        #if( $bigFirstChar )
        #    $random[0] = strtoupper( $random[0] );

        return $random;
    }



    # - ### ### ###
    #   NOTE: Обработка строк.

    /**
     * Метод экранирует все неподобающие символы в присланной строке.
     * В том числе НЕПЕЧАТНЫЕ символы!
     * @param $param - Строка, которую надо экранировать
     * @return mixed
     */
    public static function getEscapedString( $param )
    {
        //$escaped = $this->db->real_escape_string( $var );
        /* https://www.php.net/manual/ru/mysqli.real-escape-string */

        //$more_escaped = addcslashes($escaped, '%_');

        return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', '\\\$0', $param);

        /*
            00 = \0 (NUL)
            0A = \n
            0D = \r
            1A = ctl-Z
            22 = "
            25 = %
            27 = '
            5C = \
            5F = _
            # Note: preg_replace() is in PCRE_UTF8 (UTF-8) mode (`u`).
        */

    }

    /** FINAL TESTED
     * Добавить тексту обрамление с обоих сторон. Одинаковой длины, из заданных символов.
     * @param string $text Исходная строка
     * @param string $oneEdgeLen Длина 1 обрамления
     * @param string $chars Из чего будет состоять обрамление, можно много символов.
     * @param string $delimiter Разделитель между текстом и рамкой. Не учитывается в длинах.
     * @return string
     */
    public static function getTextWithEdgesHard($text, $oneEdgeLen, $chars='=', $delimiter=' ')
    {
        $edgeText = str_pad('', $oneEdgeLen,$chars);
        return $edgeText.$delimiter.$text.$delimiter.$edgeText;
    }

    /** FINAL TESTED
     * Дополнить текст обрамлением с обоих сторон, до указанной длины, иначе не трогать.
     * Одинаковой длины, из заданных символов.
     * @param string $text Исходная строка
     * @param string $len Длина 1 обрамления
     * @param string $char Из чего будет состоять обрамление, можно много символов.
     * @param string $delimiter Разделитель между текстом и рамкой. Не учитывается в длинах.
     * @return string
     */
    public static function getTextWithEdgesSoft($text, $len, $char='=', $delimiter=' ')
    {
        $textLen = strlen($text);
        $edgesTotalLen = $len - $textLen;

        if($len <= $textLen)
            return $text;

        if($edgesTotalLen <= 1)
            return $text;

        #dd($edgesTotalLen,1234123123);
        #dump('=======');

        if( $edgesTotalLen % 2 === 0 ) # Если равные
        {
            #dump('чет');
            $oneEdgeLen = $edgesTotalLen / 2; # Будет целое.
            $edgeTextLeft = str_pad('', $oneEdgeLen,$char);
            $edgeTextRight = $edgeTextLeft;

            return $edgeTextLeft.$delimiter.$text.$delimiter.$edgeTextRight;
        }
        else
        {
            #dump('нечет');
            #  3->1 5->2
            $oneEdgeLen = (int)($edgesTotalLen / 2); # Будет дробное.
            #dd(($edgesTotalLen / 2),$oneEdgeLen,$edgesTotalLen);
            $edgeTextLeft = str_pad('', $oneEdgeLen,$char);
            $edgeTextRight = $edgeTextLeft;

            return $edgeTextLeft.$char.$delimiter.$text.$delimiter.$edgeTextRight;
        }
    }
	
	
	# CONCEPT: Ооооочень древний.   На полную переработку.
	# TESTING - Не проверялся основательно.
	/**
	 * Метод экранирует все неподобающие символы в присланной строке.
	 * @param $param - Строка, которую надо экранировать
	 * @return mixed
	 */
	public static function Get_Escaped_String( $param )
	{
		//$escaped = $this->db->real_escape_string( $var );
		/* https://www.php.net/manual/ru/mysqli.real-escape-string */
		
		
		//$more_escaped = addcslashes($escaped, '%_');
		
		return preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', '\\\$0', $param);
		
		/*
			00 = \0 (NUL)
			0A = \n
			0D = \r
			1A = ctl-Z
			22 = "
			25 = %
			27 = '
			5C = \
			5F = _
			# Note: preg_replace() is in PCRE_UTF8 (UTF-8) mode (`u`).
		*/
		
	}
	
	
	
	# - ### ### ###
    #   NOTE:

    /** FINAL TESTED
     * Транслитерация текста, с русского.
     * @param string $string Исходная строка
     * @param bool $reverse Сделать наоборот: Eng -> Rus
     * @return string
     */
    public static function translit($string, $reverse=false)
    {
        $rus = array( 'ц',  'Ц', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё',  'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц',  'Ч',  'Ш',   'Щ', 'Ъ', 'Ы', 'Ь', 'Э',  'Ю',  'Я',    'а', 'б', 'в', 'г', 'д', 'е', 'ё',  'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц',  'ч',  'ш',   'щ', 'ъ', 'ы', 'ь', 'э',  'ю',  'я', 'в', 'В' );
        $lat = array('ts', 'Ts', 'A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya',    'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'w', 'W' );

        if($reverse)
            return str_replace($lat,$rus,$string); # Eng -> Rus
        else
            return str_replace($rus,$lat,$string); # Rus -> Eng
    }



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
