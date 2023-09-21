<?php

namespace LibMy;

# Из Laravel
use ReflectionClass;
use ReflectionException;


/**
 * ЗАДАЧА: Размотать весь стек, вырезать лишнее, представить ценную информацию в УДОБНОМ виде.
 * Предназначен для использования в дебаге + для логов 500 вылетов.
 *
 * Написан по-быстренькому, надо рефакторить и форматировать код.
 */ # Реализован 131021 DebugInfoCollector
class StackTraceUntangler
{
    # - ### ### ###
	
    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE: Общие методы, независимые

    /** Получить имена аргументов заданного метода класса.
     * @param string $class  'App\MyHelpers\CarbonDT'
     * @param string $method 'название метода'
     * @return array
     * @throws ReflectionException
     * @version FINAL
     */
    public static function getFunctionArgumentsNames( $class, $method )
    {
        $c = new ReflectionClass($class);

        $f = $c->getMethod($method);

        $result = array();
        foreach ($f->getParameters() as $param)
            $result[] = $param->name;

        return $result;
    }


    # - ### ### ###

    # IMPORTANT
    # FINAL
    public static function getFullStackCurrent( bool $onlyGoodFrames, bool $prepared, bool $onlyFullCalls, bool $dd=false )
    {
        $RES = debug_backtrace();

        if( $onlyGoodFrames )
            $RES = self::stackEraseUselessFrames($RES);

        if( $prepared )
            $RES = self::prepareFullStack($RES);

        if( $onlyFullCalls )
            foreach( $RES as $key=>$arr )
                $RES[$key] = @$arr['CALL_FULL']; # NOTE: Возможно @ лишняя. Как минимум 1 раз вылетало без неё.

        if( $dd )
            dd($RES );

        return $RES;
    }

    # - ### ### ###
	
	/** Распарсить инфу о 1 фрейме.
	 * @param array $frame Сырой фрейм из трейса
	 * @return array Капсовый массив с инфой
	 * @version WORK
	 */
    public static function prepareOneFrame( $frame )
    {

        $fin = array(
            'CALL_SHORT' => '', # App\Http\Controllers\DevTestController -> test( 2шт --> $var1 , $var2 )
            'CALL_FULL' => '', # App\MyHelpers\CarbonDT :: shiftToTimezone( 2шт --> object(Carbon\Carbon) $carbonDT="2021-10-13T16:12:43.104161Z" , integer $targetZone=3 )
            'CALL_FULL_LEN' => 0,
            'ARGS' => [ ],
        );

        # - ###

        # BUG
        #dump($frame);
        if( ! isset( $frame['args'] ) )
            $frame['args'] = [];

        # - ###

        try {
            $argsNamesArr = self::getFunctionArgumentsNames( @ $frame['class'], @ $frame['function']);
        } catch (ReflectionException $e) {
            # Создаю пустой массив с числовыми ключами и значениями.   Костыль.   Протестировано!
            $argsNamesArr = array();
            for($i=0; $i<count($frame['args']) ; $i++ )
                $argsNamesArr[$i] = $i;
        }

        # - ###

        $i = 0; # Костыль
        $fin['ARGS'] = array();
        foreach($frame['args'] as $argVal )
        {
            $fin['ARGS'][ $argsNamesArr[$i] ]['TYPE'] = get_debug_type($argVal);

            if( $fin['ARGS'][ $argsNamesArr[$i] ]['TYPE'] === 'object')
                $fin['ARGS'][ $argsNamesArr[$i] ]['TYPE'] .= '('.get_class($argVal).')';

            $fin['ARGS'][ $argsNamesArr[$i] ]['JSON'] = json_encode($argVal);

            $i++;
        }

        # - ###

        $argsStringShort = '( '.count($frame['args']).'шт';

        for($i=0; $i < count($frame['args']) ; $i++ )
        {
            if($i === 0 ) # Аргументы есть, иначе бы не попали в цикл.
                $argsStringShort .= ' --> ';

            if($argsNamesArr[0] === 0) # Не смогли получить имена
            {
                $argsStringShort .= ' Имена не получены';
                break;
            }

            # Имена есть
            $argsStringShort .= '$'.$argsNamesArr[$i];

            # Будут еще переменные
            if( $i+1 < count($frame['args']) )
                $argsStringShort .= ' , ';
        }
        $argsStringShort .= ' )';

        # - ###

        $argsStringFull = '( '.count($frame['args']).'шт';

        for($i=0; $i < count($frame['args']) ; $i++ )
        {
            if($i === 0 ) # Аргументы есть, иначе бы не попали в цикл.
                $argsStringFull .= ' --> ';

            # Не смогли получить имена
            if($argsNamesArr[0] === 0)
                $argsStringFull .= $fin['ARGS'][ $argsNamesArr[$i] ]['TYPE'].'='.$frame['args'][$i];
            else
                $argsStringFull .= $fin['ARGS'][ $argsNamesArr[$i] ]['TYPE'].' $'.$argsNamesArr[$i].'='.$fin['ARGS'][ $argsNamesArr[$i] ]['JSON'];
            # Имена есть

            # Будут еще переменные
            if( $i+1 < count($frame['args']) )
                $argsStringFull .= ' , ';
        }

        $argsStringFull .= ' )';

        # - ###

        $desc = $frame['class'].' '.$frame['type'].' '.$frame['function'];

        $fin['CALL_SHORT'] = $desc.$argsStringShort;
        $fin['CALL_FULL'] = $desc.$argsStringFull;
        $fin['CALL_FULL_LEN'] = strlen($fin['CALL_FULL']);

        return $fin;
    }


	/** Распарсить все фреймы сразу. Прокладка для prepareOneFrame()
	 * @param array $fullStack Полный стек фреймов из трейса
	 * @return array Капсовый массив с инфой
	 * @version FINAL
	 */
    public static function prepareFullStack( array $fullStack )
    {
        foreach( $fullStack as $key=>$frame )
            $fullStack[$key] = self::prepareOneFrame($frame);

        return $fullStack; # Индексы совпадут тк ничего не удаляю.
    }



    # - ### ### ###
    #   NOTE: Работа с исходным, полным массивом вызовом. (где 40+шт)

	
	/** Вырезать из полного стека все ненужные фреймы.
	 * @param array $fullStack Полный стек фреймов из трейса
	 * @return array Номерной массив хороших фреймов
	 * @version FINAL
	 * @todo Еще можно добавить инициатора
	 */
    public static function stackEraseUselessFrames( array $fullStack )
    {
        # Если в полном имени класса есть это, то фрейм вырезается.
        # В будущем при лишних вырезаниях можно добавить белый список
        $badClassesParts = array(
            'Illuminate\\', # 99% Ларовских фреймов.
            'Fideloper\\Proxy', # 38 frame
            'Fruitcake\\Cors', # 36 frame
            'App\\Http\\Middleware', # Мой посредник для логгера
            __CLASS__, # Убираю все вызовы этого хелпера.  App\MyHelpers\StackTraceUntangler
        );


        foreach( $fullStack as $key=>$frame )
        {
            foreach( $badClassesParts as $onePart )
            {
				if( empty($onePart) || $onePart===null )
					continue;

				if( empty($frame['class']) || $frame['class']===null )
					continue;

				#if( str_contains($frame['class'], '') )
				#	continue;

				if( str_contains($frame['class'], $onePart) )
                    unset( $fullStack[$key] );
                    # Дальше он сам пропустит ненужные циклы тк элемент пропал.
            }
        }

        # Перегенерация ключей, чтоб без дырок
        return array_values($fullStack);
    }

    
	/** Попытка понять, в каком месте был вызван debug_backtrace()
	 * @param array $fullStack Полный стек фреймов из трейса
	 * @return string CONTROLLER_ACTION / ROUTE_CLOSURE / EXCEPTION_HANDLER / UNDEFINED
	 * @version FINAL
	 * @todo В целом теперь юзлесс, но пусть будет.
	 */
    public static function tryIdentifyInitiator( array $fullStack ):string
    {
        $firstFrame = $fullStack[0];

        if( in_array('CALL_FULL', array_keys($firstFrame)) ) # Защита от дурака.
            dd(__METHOD__.' Ошибка - прислал сюда не исходный стек, а УЖЕ обработанный моими методами.',$firstFrame);

        # - ###
        # Проверка по первому фрейму
	    
	    # LibMy\...\StackTraceUntangler -> 'LibMy'
	    $libsNamespaceName = explode('\\',self::class)[0];
	    
        if( isset($firstFrame['class']) && str_contains( @ $firstFrame['class'],$libsNamespaceName) )
            return 'HELPER_CLASS';
        
        # - ###
        # Проверка всех, в цикле.

        foreach( $fullStack as $frame )
        {
	        # TESTING - тестить
	        # Если метод вызван внутри хендлера вылетов + стек явно передан через аргумент,  он взят из Throwable $exp
	        if( @ $frame['class'] === 'Illuminate\Foundation\Bootstrap\HandleExceptions' )
		        if( $frame['function'] === 'handleError' )  # "line" => 160
			        return 'EXCEPTION_HANDLER';
        	
            # Если вызвали метод прямо в роуте, в замыкании. (код в web.php)
            if( @ $frame['class'] === 'Illuminate\Routing\RouteFileRegistrar' )
                if( $frame['function'] === '{closure}' )  # "line" => 225
                    return 'ROUTE_CLOSURE';

            # Если вызвали где-то в экшене контроллера
            # Это последний вызов перед вызовом непосредственно экшена контроллера
            if( @ $frame['class'] === 'Illuminate\Routing\Controller' )
                if( $frame['function'] === 'callAction' )  # "line" => 45
                    return 'CONTROLLER_ACTION';

        }

        return 'UNDEFINED';
    }



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться

    */
} # End class
