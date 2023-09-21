<?php

namespace LibMy;

# Из Laravel
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use ErrorException;


/**
 * Класс для подробного парсинга ошибки и вытаскивания базовой инфы в удобном виде.
 * Универсален для стандартных ошибок. Постоянно используется.
 */ # ДатаВремя создания: Концепция-160921 / Реализация-300921
class ExceptionInfoCollector
{
    # - ### ### ###


    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    # IMPORTANT
    public static function getFullExceptionInfo(  \Throwable $exp )
    {
        $INFO = array(
            'LEVEL_MY'   => self::getExpLevelMy($exp),
            'BASIC_INFO' => self::getExpBasicInfo($exp),
            'ERROR_HTTP' => self::getExpHttpCode($exp),
            'ERROR_PHP'  => self::getExpSeverityLevel($exp),
        );

        return $INFO;
    }

    # - ### ### ###
    #   NOTE:

    # POSSIBLE SUSPECT CRITICAL
    public static function getExpLevelMy( \Throwable $exp ):array
    {
        $RES = array(
            'LEVEL' => '',
            'DESC' => '',
            'MSG' => '',
            'PATTERN_MATCHED' => '',
        );

        # - ###
        $arrayErrorsPattern = array(
            # Допустимые ошибки
            'POSSIBLE' => [
                'Unauthenticated.', # Работает - Если без логина зашел на роут с посредником авторизации.
                'CSRF token mismatch.', # У чела протухла форма.
            ],

            # Подозрительные
            'SUSPECT' => [   # NOTE: Нужно ТОЧНОЕ совпадение. Иначе Undefined.
                'The given data was invalid.', # Валидатор адекватности отклонил данные.
                # Можно юзать для отлова плохих правил валидатора. Где чаще всего лажают юзеры.

                'The GET method is not supported for this route. Supported methods: POST.',
                # Переход в пост роут. Очень подозрительно, кто-то ковырял форму и вытащил ссылку.

                'The POST method is not supported for this route. Supported methods: GET, HEAD.',
                # Попытка отправить пост на гет роут. 100% подозрительно.
            ],

            'CRITICAL' => [   # NOTE: Делается через strstr по каждому
				# NOTE: Массивы
                'Undefined index:', #
                'Undefined offset:', # Undefined offset: 0
                'Undefined array key', #
                'Trying to access array offset on value of type', #
                'Cannot use object of type', # Cannot use object of type stdClass as array
                'Cannot access offset of type', #
                'operator not supported for', # [] operator not supported for strings
                'Array to string conversion', # Array to string conversion
                'count(): Parameter must be an array or an object that implements Countable', # count(): Parameter must be an array or an object that implements Countable
                'Illegal string offset', # Illegal string offset 'CURRENCY'
                
				# NOTE: Общее пхп - Файлы
                'Path cannot be empty', # для fopen
                'failed to open stream:', # include( путь ): failed to open stream: No such file or directory
                'Failed to open stream: No such file or directory', # fopen/file_get_contents( путь ): Failed to open stream: No such file or directory
    
				# NOTE: Общее пхп
                'syntax error', # syntax error, unexpected '123'
                'Undefined variable:', #
                'Undefined variable $', #
                'Undefined global variable $_', #
                'Too few arguments to function', #
                'Call to undefined function', # Call to undefined function Nette\Utils\first()
                'Call to a member', # Call to a member function toArray() on array
                'expects parameter', # mt_srand() expects parameter 1 to be integer, string given
                'Invalid argument supplied for foreach()', # Invalid argument supplied for foreach()
                'Return value of', # Return value of LibMy\123::123() must be of the type array, null returned
                '() must return an', # debuginfo() must return an array
                'Division by zero', # Division by zero
                ': Argument #', # LibMy\apiTGBot::setMessage_RawString(): Argument #1 ($msgOneStr) must be of type string, array given, called in G:\...\app\Libraries\Modules_Api\apiTGBot.php on line 261
                'A non-numeric value encountered', # A non-numeric value encountered
                
				# NOTE: Общее пхп - Особое
                'Allowed memory size of ', # Allowed memory size of 134217728 bytes exhausted (tried to allocate 946176 bytes)
				'Cannot modify header information - headers already sent by', #
    
				# NOTE: Общее пхп - ООП
                '\' not found', # Class 'App\MiscCode' not found
                'Cannot declare class ', # Cannot declare class CreateTableLogRequests, because the name is already in use
                'Unsupported operand types', # Unsupported operand types
                'A function with return type must return a value', # A function with return type must return a value
                'Call to undefined method', # Call to undefined method App\User::getTimezone123() (View: путь.blade.php)
                'Trying to get property \'', # Trying to get property 'wallet_code' of non-object
                'Method App\\', # Method App\Http\Controllers\PaymentsController::openCheckoutPage does not exist. => BadMethodCallException
                'Non-static method', # Non-static method App\AllMyClasses\AmountConverter::round_up() should not be called statically
                'Target class [', # Target class [SeederProjectWalletsDonates] does not exist.
                'Class "', # Class "App\Http\Controllers\DB" not found     (Надо прописать Use)
                
				# NOTE: Ларовское
                'View [', # View [dependency] not found. (View: G:\... \page-landing.blade.php)
                '] not found. (', # View [dependency] not found. (View: G:\... \page-landing.blade.php)
                '] not defined. (View: ', # Route [payments.donate-direct-check] not defined. (View: G:\Путь.blade.php)
                '] does not exist on this collection instance.', # Property [invoice_id] does not exist on this collection instance.
                'to fillable property to allow mass assignment on', # Add [id_md5_IpUa] to fillable property to allow mass assignment on [App\LogRequest].
				
				# NOTE: SQL и БД
                'SQLSTATE[', #
                
                #'', #
                #'', #
                #'', #
                #'', #
            ],

            #'' => [],
        );
        # - ###

        $msg = $exp->getMessage();
        $RES['MSG'] = $msg;

        $myPatternMatched = '-'; # Какой из па

        $level = 'UNDEFINED'; # Дефолтный тип

        if( in_array($msg, $arrayErrorsPattern['POSSIBLE']) )
        {
            $level = 'POSSIBLE';
            $myPatternMatched = $msg;
        }

        if( in_array($msg, $arrayErrorsPattern['SUSPECT']) )
        {
            $level = 'SUSPECT';
            $myPatternMatched = $msg;
        }

        # - ###

        if( empty($msg) && self::getExpHttpCode($exp)['HTTP_CODE']===404 )
        {
            $level = 'POSSIBLE';
            $myPatternMatched = 'Manual http 404';
        }

        # - ###

        # NOTE: ТОЧНО критические ошибки.
        foreach( $arrayErrorsPattern['CRITICAL'] as $pattern )
            if( strstr($msg,$pattern) )
                { $level = 'CRITICAL'; $myPatternMatched = $pattern; break; }

        $RES['PATTERN_MATCHED'] = $myPatternMatched;
        $RES['LEVEL'] = $level;

        # - ###

        $levelsMyMsg = array(
            'POSSIBLE'  => 'Допустимое,мелочевка',
            'SUSPECT'   => 'Подозрительные действия',
            'CRITICAL'  => 'Точно вылетело',
            'UNDEFINED' => 'Возможный вылет скрипта',
        );

        $RES['DESC'] = $levelsMyMsg[$level];

        //dd($RES,$exp);
        return $RES;
    }


    public static function getExpBasicInfo( \Throwable $exp ):array
    {
        $INFO = array(
            'CLASS' => '-',
            'MSG'  => '-',
            'MSG_EMPTY'  => true,
            'FILE' => '-',
            'LINE' => '-',
            'FILE_LINE' => '-',
            'TRACE_JSON' => 'EMPTY',
            'TRACE_JSON_LEN' => 0,
        );
        # - ###

        $INFO['CLASS'] = get_class($exp);

        $INFO['MSG'] = $exp->getMessage();
        $INFO['MSG_EMPTY'] = empty($exp->getMessage());
        $INFO['FILE'] = $exp->getFile();
        $INFO['LINE'] = $exp->getLine();
        $INFO['FILE_LINE'] = $INFO['FILE'].' => '.$INFO['LINE'];

        $INFO['TRACE_JSON'] = json_encode($exp->getTrace() );
        $INFO['TRACE_JSON_LEN'] = strlen($INFO['TRACE_JSON']);

        return $INFO;
        #dd('End'.__CLASS__.''.__METHOD__);
    }


    # 404  500 ...  .
    # NOTE: Точно ловит 404
    public static function getExpHttpCode( \Throwable $exp ):array
    {
        $INFO = array(
            'IS_HTTP_EXP' => false,
            'HTTP_CODE' => -1,
        );
        # - ###

        if($exp instanceof HttpExceptionInterface)
        {
            $INFO['IS_HTTP_EXP'] = true;
            $INFO['HTTP_CODE'] = $exp->getStatusCode();
        }

        return $INFO;
    }


    # Notice / crit  warn и тд.
    public static function getExpSeverityLevel( \Throwable $exp ):array
    {
        $INFO = array(
            'IS_PHP_EXP' => false,
            'SEVERITY_RAW' => -1,
            'IF_FATAL' => false,
            'RESOLVED_NAME' => '-',
        );
        # - ###
        $phpErrorCodes = array (
            1   => "E_ERROR",
            2   => "E_WARNING",
            4   => "E_PARSE",
            8   => "E_NOTICE",
            16  => "E_CORE_ERROR",
            32  => "E_CORE_WARNING",
            64  => "E_COMPILE_ERROR",
            128 => "E_COMPILE_WARNING",
            256 => "E_USER_ERROR",
            512 => "E_USER_WARNING",
            1024 => "E_USER_NOTICE",
            2048 => "E_STRICT  E_ALL",
            4096 => "E_RECOVERABLE_ERROR",
            8192 => "E_DEPRECATED",
            16384=> "E_USER_DEPRECATED",
            32767=> "E_ALL");

        $phpErrorCodesFatal = array (
            1   => "E_ERROR",
            16  => "E_CORE_ERROR",
            64  => "E_COMPILE_ERROR",
            4096 => "E_RECOVERABLE_ERROR");

        $phpErrorCodesFatalNumberOnly = array (
            1   ,
            16  ,
            64  ,
            4096);

        # - ###

        if($exp instanceof ErrorException)
        {
            $INFO['IS_PHP_EXP'] = true;
            $INFO['SEVERITY_RAW'] = $exp->getSeverity(); # Номер

            $INFO['IF_FATAL'] = in_array($exp->getSeverity(), $phpErrorCodesFatalNumberOnly);
            $INFO['RESOLVED_NAME'] = $phpErrorCodes[ $exp->getSeverity() ];
        }

        return $INFO;
    }


    # - ### ### ###
    #   NOTE:

    # CONCEPT: Дописать вытаскивание инфы из стека.
    #  Чекать что уже было в старом эксепшене, там точно это было.


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться



    */
} # End class
