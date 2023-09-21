<?php

namespace LibMy;

# IMPORTANT Класс = должен работать полностью самостоятельно, без сторонних методов и зависимостей.
#  Никаких запросов в БД!!!

# IMPORTANT = Работаю только с USD - Жестко зашито.



# - ### ### ### ###
# - ###
	/* # = # = # = # */
	
	# Это оооооочень старый класс, сейчас бы писал вообще по другому.
	# Не стал разбирать и переписывать.
	# Был в продакшене.
	
	/* # = # = # = # */
# - ###
# - ### ### ### ###



class apiPerfectMoney   # 220221 перепись почти с 0. Заняло 2-3часа.
{
    # - ######

    private $auth_accountID;
    private $auth_accountPass;
    private $auth_accountWallet;

    # - ######

    private $responseUrl = null; # Полная ссылка запроса

    private $responseReceived = null; # Пришел ли ответ. BOOL
    private $responseRaw = null; # Ответ в 1 строчку

    private $responseIsValid = null; # Удалось ли вытащить поля с ответом. BOOL
    private $responseFetched = null; # Спарсили таблицу
    private $responseAsoc = null; # Асоц массив только нужных данных

    private $responseFinalResult = null; #

    # - ######################################
    # NOTE: Авторизация

    public function auth_fillData($accId, $accPassReal, $accWalletFrom)
    {
        $this->auth_accountID = $accId;
        $this->auth_accountPass = $accPassReal;
        $this->auth_accountWallet = $accWalletFrom;
    }

    public function auth_getAuthParamsString( )
    {
        #$url = 'https://perfectmoney.is/acct/confirm.asp?';
        #$url  =  'AccountID='    .$this->auth_accountID;
        #$url .= '&PassPhrase='   .$this->auth_accountPass;
        #$url .= '&Payer_Account='.$this->auth_accountWallet;

        return "AccountID=$this->auth_accountID&PassPhrase=$this->auth_accountPass&Payer_Account=$this->auth_accountWallet";
    }


    # Для теста из роута
    public function authDebug_checkConnect($accId, $accPassReal, $accWalletFrom)
    {
        $this->auth_fillData($accId, $accPassReal, $accWalletFrom);
        $this->action_getBalance();
        $this->debugDumpAllVars();
        dd('@authDebug_checkConnect - end');
    }

    # Метод проверки авториз - через экшен получения баланса.

    # - ######################################



    # - ######################################
    # NOTE: Работа с ошибками - ТОЛЬКО при успешном ответе.

    # Ответ не удалось получить. # Либо не валидный ответ.  # Либо есть поле ошибок.
    # Либо ошибки при получении и разборе, либо ошибки в тексте ответа.
    # ЛЮБЫЕ виды ошибок.  Жестко - ДА/НЕТ
    public function error_hasAny()
    {
        if( ! $this->responseReceived )
            return true;

        if( ! $this->responseIsValid )
            return true;

        if( in_array('ERROR',array_keys($this->responseAsoc) ) )
            return true;

        return false;
    }


    # Вызывать только при error_hasAny = TRUE (Если точно есть ошибки)
    public function error_getMyReasonCode()
    {
        if( ! $this->responseReceived )
            return 'ANSWER_NOT_RECEIVED';

        if( ! $this->responseIsValid )
            return 'ANSWER_IS_INVALID';

        # - ###

        if( in_array('ERROR',array_keys($this->responseAsoc) ) )
        {
            # Can not login with passed AccountID and PassPhrase or API is disabled on this account/IP
            if( strstr($this->responseAsoc['ERROR'],'Can not login with passed AccountID and PassPhrase or API is disabled on this account/IP') )
                return 'AUTH_DENY';
                # Это же при несовпадении IP

            # Too many transfers between your accounts.     Когда делал много переводов сам себе.
            if( strstr($this->responseAsoc['ERROR'],'Invalid Payer_Account') )
                return 'TOO_MANY_REAL_TRANSFERS_TO_MYSELF';
                # Банит только реальный перевод.   Баланс и проверочный работают.
                # Не пропадает как минимум 10 минут. Возможно это насовсем.

            # You have been banned on for excessive behaviour for 1 minute(s).
            if( strstr($this->responseAsoc['ERROR'],'You have been banned on for excessive behaviour for') )
                return 'BAN_TEMPORAL';

            # - ###

            # Invalid Payer_Account
            if( strstr($this->responseAsoc['ERROR'],'Invalid Payer_Account') )
                return 'INVALID_WALLET_MY';

            # Invalid Payee_Account
            if( strstr($this->responseAsoc['ERROR'],'Invalid Payee_Account') )
                return 'INVALID_WALLET_TARGET';

            # Too small amount. Minimum amount for this currency: 0.01
            if( strstr($this->responseAsoc['ERROR'],'Too small amount. Minimum amount for this currency:') )
                return 'AMOUNT_TOO_SMALL';

            # Invalid Amount
            if( strstr($this->responseAsoc['ERROR'],'Invalid Amount') )
                return 'AMOUNT_INVALID';

            # Not enough money to pay
            if( strstr($this->responseAsoc['ERROR'],'Not enough money to pay') )
                return 'BALANCE_NOT_ENOUGH';

        }

        return 'UNDEFINED';
    }

    public function error_getErrorMessage()
    {
        return $this->responseAsoc['ERROR'];
    }


    # - ######################################

    # Округлить прописанным способом.
    public function prepareAmount( $amount )
    {
        # NOTE: Напоминалка - ТОЛЬКО USD !!!

        return number_format($amount, 2, '.','');
        # Округлит к ближайшему.  .123 -> .12    .126 -> .13

        # Вынес, что бы можно было отдельно тестить + все в 1 месте.
    }

    public function prepareDescription($desc)
    {
        # Точно нельзя пробелы.
        #  Возможно нельзя диезы #   и /
        # Точно можно _ точки, запятые и равно  ( , . = _ )
        return str_replace(' ', '_', $desc);
    }

    # Только исполнить, БЕЗ разбора ответа.
    # Если ответ не получен - вернет false и дальше явно раскручивать response
    private function executeUrl2($url):bool
    {
        $this->responseUrl = $url;

        # - ###

        // trying to open URL to process PerfectMoney Spend request
        $f=fopen($url, 'rb');

        if($f===false)
        {
            $this->responseReceived = false;
            return false;
        }

        // getting data
        $out = "";
        while( ! feof($f) )
            $out .= fgets($f);
        fclose($f);

        $this->responseRaw = $out;
        $this->responseReceived = true;

        # - ###

        if( ! $this->responsePerform() )
        {
            $this->responseIsValid = false;
            return false;
        }

        $this->responseIsValid = true;

        # - ###

        return true;
    }

    #
    public function responsePerform():bool
    {
        // searching for hidden fields
        if( ! preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $this->responseRaw, $this->responseFetched, PREG_SET_ORDER))
            return false; # Если нет скрытых полей - невалидный ответ


        foreach( $this->responseFetched as $one )
        {
            $key = $one[1];   $val = $one[2];
            $this->responseAsoc[$key] = $val;
        }

        return true;
    }


    # IMPORTANT - Вызывается ТОЛЬКО после проверки на успешность запроса.
    public function responseGetFinalResult()
    {
        return $this->responseFinalResult;
    }

    public function responseGetAsoc()
    {
        return $this->responseAsoc;
    }


    # Если надо неск транзакций в 1 классе
    public function responseClearAll()
    {
        $this->responseUrl         = null;
        $this->responseReceived   = null;
        $this->responseRaw       = null;
        $this->responseIsValid  = null;
        $this->responseFetched   = null;
        $this->responseAsoc       = null;
        $this->responseFinalResult = null;
    }


    # - ######################################
    # NOTE: ВСЕ Действия
    #   Либо полный успех, либо полный провал.
    #   Разбор полетов в методе получения кода ошибки.


    public function action_sendVerify($targetWall, $amount, $orderId='NOT-NEED-123')
    {
        $amount = $this->prepareAmount($amount);

        $url  = 'https://perfectmoney.is/acct/verify.asp?';
        $url .= $this->auth_getAuthParamsString();

        # - ###

        $url .= "&Payee_Account=$targetWall";
        $url .= "&Amount=$amount";
        $url .= "&PAY_IN=USD";

        if( $orderId !== 'NOT-NEED-123' ) # Что бы никогда не совпало случайно
            $url .= "&PAYMENT_ID=$orderId";

        # - ###

        $this->executeUrl2($url); # Вернет BOOL

        $hasErrorsBool = $this->error_hasAny();

        $this->responseFinalResult = ! $hasErrorsBool; # Прокатит ли транзакция?

        return ! $hasErrorsBool; # Инвертирую!!! (ошибок нет=все хорошо(true))

        /*
          "Payee_Account_Name" => "Hover LTD"
          "Payee_Account" => "U29937471"
          "Payer_Account" => "U29937471"
          "PAYMENT_AMOUNT" => "0.50"
          --> "В реальном здесь будет батч"
          "PAYMENT_ID" => "Заказ"
        */
    }

    public function action_sendReal($targetWall, $amount, $orderId='NOT-NEED-123', $desc='NOT-NEED-123')
    {
        $amount = $this->prepareAmount($amount);

        $url  = 'https://perfectmoney.is/acct/confirm.asp?';
        $url .= $this->auth_getAuthParamsString();

        # - ###

        $url .= "&Payee_Account=$targetWall";
        $url .= "&Amount=$amount";
        $url .= "&PAY_IN=USD";

        if( $orderId !== 'NOT-NEED-123' ) # Что бы никогда не совпало случайно
            $url .= "&PAYMENT_ID=$orderId";

        if( $desc !== 'NOT-NEED-123' ) # Что бы никогда не совпало случайно
            $url .= "&Memo=".$this->prepareDescription($desc);

        # - ###

        $this->executeUrl2($url); # Вернет BOOL

        $hasErrorsBool = $this->error_hasAny();

        if( ! $hasErrorsBool )
            $this->responseFinalResult = $this->responseAsoc['PAYMENT_BATCH_NUM'];

        return ! $hasErrorsBool; # Инвертирую!!! (ошибок нет=все хорошо(true))


        /* При успехе
          "Payee_Account_Name" => "Hover LTD"
          "Payee_Account" => "U29937471"
          "Payer_Account" => "U29937471"
          "PAYMENT_AMOUNT" => "0.50"
          "PAYMENT_BATCH_NUM" => "370713893"
          "PAYMENT_ID" => "Test"
         */
    }

    public function action_getBalance(  )
    {

        $url  = 'https://perfectmoney.is/acct/balance.asp?';
        $url .= $this->auth_getAuthParamsString();

        # - ###

        $this->executeUrl2($url); # Вернет BOOL

        $hasErrorsBool = $this->error_hasAny();

        if( ! $hasErrorsBool )
            $this->responseFinalResult = $this->responseAsoc[$this->auth_accountWallet];

        return ! $hasErrorsBool; # Инвертирую!!! (ошибок нет=все хорошо(true))

        /*
          "U29937471" => "2.00"
          "E28862029" => "0.00"
          "G28821718" => "0.00"
        */
    }








    public function debugDumpAllVars()
    {
        dump($this->auth_accountID);
        dump($this->auth_accountPass);
        dump($this->auth_accountWallet);
        dump($this->responseUrl);
        dump($this->responseReceived);
        dump($this->responseRaw);
        dump($this->responseIsValid);
        dump($this->responseFetched);
        dump($this->responseAsoc);
        dump($this->responseFinalResult);
    }


    # - ######################################

    # Старый метод - пгуглить коды валют.
    /*public function field_Currency($action, $value='', $fieldName='CURRENCY')
    {
        if( $value !== '' )
        {
            switch($value)
            {
                case  'USD': $value='1'; break;
                case  'EUR': $value='2'; break;
                case 'GOLD': $value='3'; break;
                case  'BTC': $value='7'; break;
                default: dd('pemonAPI@field_Currency - недопустимый тип - '.$value);
            }
        }
        return $this->fieldGetSet($action, $fieldName, $value);
    }*/



    # - ######################################



}
