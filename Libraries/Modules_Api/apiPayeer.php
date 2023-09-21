<?php

namespace LibMy;


# - ### ### ### ###
# - ###
	/* # = # = # = # */
	
	# Это оооооочень старый класс, сейчас бы писал вообще по другому.
	# Не стал разбирать и переписывать.
	# Был в продакшене.
	
	/* # = # = # = # */
# - ###
# - ### ### ### ###



	# Написан с нуля 111020 1500 - 121020 0100 = 10ч почти подряд
    # Замена официальному классу

    # IMPORTANT Класс должен работать полностью самостоятельно, без сторонних методов и зависимостей.
    #  Никаких запросов в БД!!!

    # Сейчас не парюсь и делю вывод только на payeer.  Без других валют и платежек
    # Потом можно сделать вывод в крипту и в рублевые платежки

    # NOTE: Офф гайд по API: https://payeerru.docs.apiary.io/#reference/0

class apiPayeer   # CPayeer
{

    #private $allowedPaySystemsInfo = array( );
    private $allowUseReferenceId = true; # Вставлять ли это поле или игнорить.

    # - ######

    private $auth_WalletAddress;
    private $auth_MerchantId;
    private $auth_SecretKey;

    # Юзлесс, по факту не используется нигде в логике. Но пусть будет.
    private $isValidAccount = false; # Рабочие ли это данные


    private $response = null; # Присланный ответ

    private $payoutArray; # Куча POST параметров для отправки.

    # - ######################################
    #   NOTE: Все про подключение к API

    # $account, $apiId, $apiPass
    # Выставление всех полей разом
    public function authSetData( $wallet, $merchId, $secret )
    {
        $this->auth_WalletAddress = $wallet;
        $this->auth_MerchantId = $merchId;
        $this->auth_SecretKey = $secret;

        $this->isValidAccount = false;
    }

    # Выставление по 1 полю
    public function authSetWallet( $wallet )
    {
        $this->auth_WalletAddress = $wallet;

        $this->isValidAccount = false;
    }
    public function authSetMerchId( $merchId )
    {
        $this->auth_MerchantId = $merchId;

        $this->isValidAccount = false;
    }
    public function authSetSecret( $secret )
    {
        $this->auth_SecretKey = $secret;

        $this->isValidAccount = false;
    }

    public function authHasValidData():bool
    {
        return $this->isValidAccount;
    }

    public function authHasAllRows():bool
    {
        if( empty($this->auth_WalletAddress) || empty($this->auth_MerchantId) || empty($this->auth_SecretKey) )
            return false;

        return true;
    }

    public function authGetRowsArray():array
    {
        if( ! $this->authHasAllRows() )
            dd('PayeerAPI@authMakeArray - указаны не все строки для авторизации');

        return array(
            'account' => $this->auth_WalletAddress,
            'apiId'   => $this->auth_MerchantId,
            'apiPass' => $this->auth_SecretKey,
        );

    }

    # IMPORTANT
    public function authTryConnect():bool
    {

        $this->sendRequest(array()); # Метод сам подставит данные авторизации

        if ($this->response['auth_error'] === '0')
        {
            $this->isValidAccount = true;
            return true;
        }

        return false;

    }

    # - ######################################
    #   NOTE: Все про получение ошибок и ответа сервера

    # Учитывать, что массив может быть асоциативным(с ключами)
    # И еще в нем может стоять сразу булевское значение

    # Если это именно ошибка авторизации
    public function errorsIsAuthError():bool
    {
        return ( $this->response['auth_error'] === '1' ) ;
    }

    # В массиве описания ошибок больше 1 элемента
    public function errorsIsMany():bool
    {
        # При сложном запросе приходит 'errors'=false/true  вместо пустого массива
        if( is_bool($this->response['errors']) ) return false;

        return (bool) ( count($this->response['errors']) >= 2 );
    }

    # Произошли ошибки в запросе
    public function errorsHas():bool
    {
        return ( isset($this->response['errors']) && !empty($this->response['errors']) ) ;
    }

    # Просто получить массив 'как есть'
    public function errorsGetAllAsArr():array
    {
        if( is_bool($this->response['errors']) ) return array();

        return $this->response['errors'];
    }

    # Объединить массив ошибок в одну строку (если ошибок много)
    public function errorsGetAllAsString():string
    {
        if( is_bool($this->response['errors']) ) return '';
        # TODO: Поменять на ! is_array()

        return implode( '   <#-#-#>   ',$this->response['errors'] );
    }

    # Получить текст только первой ошибки
    public function errorsGetFirstString():string
    {
        if( is_bool($this->response['errors']) ) return '';

        return current($this->response['errors']);
        #return array_shift($this->response['errors']);
    }

    # Вывести массив ошибок и завершить
    public function errorsEchoDd()
    {
        dd($this->response['errors'] );
    }



    # Получить полный массив ответа
    public function responseGet()
    {
        return $this->response;
    }

    # Очистить, что бы позже записать новый
    public function responseClear()
    {
        return $this->response = null;
    }

    # Массив с ответом существует
    public function responseExist()
    {
        return !empty($this->response);
    }

    # - ######################################
    #   NOTE: Инициализация(отправка) запроса. Главный метод.

    # IMPORTANT
    # Автоматически получает и вставляет данные авторизации. БЕЗ проверки их валидности.
    # Вернет Bool - запрос без ошибок.   false-ошибка  true-васе норм
    public function sendRequest( $arPost ):bool
    {

        if ( ! function_exists('curl_init') )
            die('curl library not installed');

        # - ###

        # Без проверки на валидность.
        $arPost = array_merge($arPost, $this->authGetRowsArray());

        # - ###

        $this->responseClear();

        # - ###

        $data = array();
        foreach ($arPost as $k => $v)
            $data[] = urlencode($k) . '=' . urlencode($v);

        $data[] = 'language=' . 'ru';
        $data = implode('&', $data);

        # - ###

        $handler  = curl_init();
        curl_setopt($handler, CURLOPT_URL, 'https://payeer.com/ajax/api/api.php');
        curl_setopt($handler, CURLOPT_HEADER, 0);
        curl_setopt($handler, CURLOPT_POST, true);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $data);
        curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($handler, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0');
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);

        # - ###

        $content = curl_exec($handler);

        # Вся CURL инфа о запросе.  Есть все тайминги. Вызывать dd только после закрытия
        #$arRequest = curl_getinfo($handler);
        #dd($arRequest);

        curl_close($handler);

        # - ###

        $content = json_decode($content, true);

        $this->response = $content;


        if (isset($content['errors']) && !empty($content['errors']))
            return false;

        return true;

        # Можно переписать в одну строку
    }


    # - ######################################
    #   NOTE: Получение информации от мерча


    # WORK
    # TODO: Сделать кучу методов для обработки каждого метода.
    # TODO: Сделать для себя табличку, которая удобно все выводит
    public function infoGetPaySystems()
    {
        $arPost = array(
            'action' => 'getPaySystems',
        );

        if( $this->sendRequest($arPost) )
            return true;

        return false;
    }

    # WORK
    # Пока осталю так
    # TODO: добавить параметр output = Y N     N - получить курсы ввода  Y - получить курсы вывода
    public function infoGetExchangeRates($output='N')
    {
        $arPost = array(
            'action' => 'getExchangeRate',
            'output' => $output,
        );

        if( $this->sendRequest($arPost) )
            return true;

        return false;
    }


    # 'action' => 'history',  - вся история, см там параметры в ответе
    # WORK
    public function infoGetHistoryInfo($historyId)
    {
        $arPost = array(
            'action' => 'historyInfo',
            'historyId' => $historyId
        );

        # Операция есть - вернет кучу инфы
        # Операции нет - info='0'   Без ошибок

        if( $this->sendRequest($arPost) )
        {
            if( $this->response['info'] !== '0' )
                return true;
        }

        return false;
    }



    # - ###############
    # NOTE: Важные операции

    # WORK
    # CONCEPT: Как переделать в унив метод:  Параметры - кошелек и idПлатежки.
    #  Внутри получаем данные о платежке и вытаскиваем отуда regexp
    public function actionCheckUserPayeer($targetWallet):bool
    {
        $arPost = array(
            'action' => 'checkUser',
            'user' => $targetWallet,
        );

        if( $this->sendRequest($arPost) )
            return true;

        # Если есть: Пустой массив ошибок
        # Если нету: ошибка - User is incorrect type

        # Если массив errors пустой - значит пользователь существует.

        return false;
    }

    # dd() || integer || bool-есть ли ошибки зпроса
    public function actionGetBalance($currency='ALL')
    {
        $arPost = array(
            'action' => 'getBalance',  # balance
            'type' => 'array', # object (дефолт)
        );

        if( $currency != 'ALL' )
            $arPost['type'] = 'object';


        if( $this->sendRequest($arPost) )
        {
            if( $currency === 'ALL' )
                return true;

            if( ! in_array($currency, array_keys($this->responseGet()['balance'])) )
            {
                #dump($this->responseGet()); # Для дебага, ибо вдруг выскочит у юзера
                dd('PayeerAPI@actionGetBalance - кривая валюта - такой нет - '.$currency);
            }

            return $this->responseGet()['balance'][$currency]['available'];
        }

        return false;
    }


    # - ######################################
    #   NOTE: Сложная межсистемная выплата на любую другую платежку.
    #    Пока оставил только переводы внутри Payeer

    # Получить массив, хранящий данные для POST запроса. (что отправляем)
    public function payoutGetArray()
    {
        return $this->payoutArray;
    }

    # Вытащить из ответа сервера нужное поле. (БЕЗ проверок на успех)
    public function payoutGetHistoryId()
    {
        return $this->response['historyId'];
    }

    # Очистить параметры запроса, что бы случайно не запутаться(если неск запросов подряд)
    public function payoutClear()
    {
        return $this->payoutArray = null;
    }

    # - ###

    # Выполнять ли реальный перевод или только прислать результаты
    public function payoutSetTypeTest()
    {
        $this->payoutArray['action'] = 'payoutChecking';
    }
    public function payoutSetTypeReal()
    {
        $this->payoutArray['action'] = 'payout';
    }


    # TODO: Добаить валидацию + асоц массив id и моих сокращений.  Только для валютных платежек
    public function payoutSetPaymentSystem($ps)
    {
        $this->payoutArray['ps'] = $ps;
    }

    # TODO: Добаить валидацию
    public function payoutSetCurrency($in, $out)
    {
        $this->payoutArray['curIn'] = $in;
        $this->payoutArray['curOut'] = $out;
    }

    public function payoutSetAmount($amount, $whoPayComm)
    {
        if( $whoPayComm !== 'PROJECT' && $whoPayComm !== 'CLIENT' )
            dd('PayeerAPI@payoutSetAmount - кривое значение комиссий в PD -> '.$whoPayComm);

        # На всякий случай
        $amount = number_format($amount,2,'.','');

        # $amount = floatval($amount); # Перевожу из строки в float

        # NOTE: IN  и 1.0 долларов.   С меня сняли 1.00  - на другой пришло 0.99  (те комиссию платил он)
        # NOTE: OUT и 1.0 долларов.   С меня сняли 1.01  - на другой пришло 1.00  (те комиссию платил Я)

        if( $whoPayComm === 'PROJECT' )
            $this->payoutArray['sumOut'] = $amount; # PROJECT  # Плачу я

        if( $whoPayComm === 'CLIENT' )
            $this->payoutArray['sumIn'] = $amount; # CLIENT  # Платит клиент

    }

    # TODO: Пока только для одного параметра   param_ACCOUNT_NUMBER    менять на массив
    public function payoutSetAccountTo($target)
    {
        $this->payoutArray['param_ACCOUNT_NUMBER'] = $target;
    }

    public function payoutSetComment($text)
    {
        $this->payoutArray['comment'] = $text;
    }

    # ID из моей системы учета.   Уникальный, не должен повторяться в другими платежами за все время.
    public function payoutSetReferenceId($text)
    {
        # NOTE: Parameter Reference Id has an invalid format (correct format: [a-zA-Z0-9-_]{1,50})

        if( ! $this->allowUseReferenceId )
            return;

        $this->payoutArray['referenceId'] = $text;
    }

    # ТОЛЬКО для Payeer. (Про другие ПС не знаю)
    public function payoutSetAnonim( $statusBool )
    {

        if( $statusBool )
            $this->payoutArray['anonim'] = 'Y'; # Анонимный перевод - работает - стоит "От: @anonim"
        else
            $this->payoutArray['anonim'] = 'N';

    }

    # - ###

    # Отправляет собраный массив параметров на сервер.
    public function payoutSendRequest()
    {
        if( $this->sendRequest($this->payoutArray) )
            return true;

        return false;
    }

    # Пробный запрос для проверка, не заблокированы ли платежи у аккаунта. (данные валидны, а выплаты недоступны)
    public function payoutSendRequestDebugTest():bool
    {
        # Пример использования
        # $P->payoutSendRequestDebugTest();
        # dd( $P->responseGet() );

        $this->payoutSetTypeTest();
        $this->payoutSetPaymentSystem(1136053); # Payeer
        $this->payoutSetCurrency('USD','USD');
        $this->payoutSetAmount(0.05,'CLIENT'); # PROJECT
        $this->payoutSetAccountTo('P-вписать'); # 2 тестовый кошелек
        $this->payoutSetComment('Коммент 123 4324 2342');
        $this->payoutSetAnonim(true);

        return $this->payoutSendRequest();

    }

    # - ######################################
    #   NOTE:



    # - ######################################
    #   NOTE:
	
    # TODO: Блок - Перевод средств
    # TODO: Блок - Проверка возможности выплаты
    # TODO: Блок - Выплата

    # Не делать
    # TODO: Блок - Информация о платеже
    # TODO: Блок - История операций
    # TODO: Блок - Информация по выплате


}
