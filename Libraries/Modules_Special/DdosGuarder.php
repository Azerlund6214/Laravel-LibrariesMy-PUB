<?php

namespace LibMy;

# Из Laravel
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

# Хелперы
use LibMy\CarbonDT;
use LibMy\SessionMy;
use LibMy\Requester;
use Illuminate\Support\Facades\Request;


/**
 * Модуль для отражения простейших ддос атак.
 * В целом написано немного сыровато, не стал сильно додумывать и переписывать с 0, не приоритетно. Приемлемо.
 */ # ДатаВремя создания: 240221 и отложен.  Фулл дописан и внедрен 240321 / Перепись 031021
class DdosGuarder
{
    # - ### ### ###

    public static $mainKey = 'DDOS-GUARDER'; #

    public static $keyIP = 'user-ip'; #
    public static $keyUA = 'user-ua'; #
    public static $keyUAExt = 'user-ua-ext'; #
    public static $keyStatReqCount         = 'user-requests-count-all'; # Всего (обычные+забаненые)
    public static $keyStatReqCountAfterBan = 'user-requests-count-banned'; # Только после бана.

    public static $keySwitch = 'ddos-switch'; #
    public static $keyDates  = 'dates'; #

    # - ###

    # 12-3
    public static $datesMaxCount       = 10; # Пришло N запросов
    public static $datesMinIntervalSec = 3; #      за N секунд

    # - ###

    public static $onDdosAction = 'MSG'; # REDIR / MSG
    public static $onDdosRedirTarget = 'https://natribu.org/ru/';
    public static $onDdosMsg = 'Хватит ддосить!';

    public static $logChannelName = 'ddos';

    # - ### ### ###

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###

    # IMPORTANT: Цель - не допустить ддосера до логики экшенов. Надо его отрубить на самой 1 строчке.
    #  Что бы не дать пихать инъекции в параметрах, либо брутить админку и тд.

    # IMPORTANT: Что учитывать:
    #  - Поисковые системы могут парсить слишком быстро и попасть.
    #  - При ддосе по факту весь ддос улетит на сайт куда стоит редирект. (Хотя IP будет юерский, а не сервера.)
    #  - Могут быть коллизии доступа к файлу сессии.
    #  - Могут быть коллизии доступа к файлу лога доса(но там не критично ибо в 1 строку)
    #  - 99% что будет вылет БД из-за превышения 15 подключений. (тк лог запросов стоит раньше антиддоса)
    #  -

    # - ### ### ###
    #   NOTE:

    # NOTE: В этот метод может поступать до 1000 тяжелых запросов в секунду. Поэтому делать все максимально просто и топорно.
    public static function entry():void
    {
        # - ###
        # 1 - Проверить ключ сессии.

        if( ! SessionMy::keyExist(self::$mainKey))
        {   # NOTE: Это первый заход.  Создать ключ и выйти.

            self::createMainKey();

            return;
        }

        # - ###
        # Ключ уже точно есть. Просто получаю.

        # К этому моменту главный ключ точно есть.
        $mainArr = self::mainKeyGet();

        $mainArr[self::$keyStatReqCount] += 1;

        # - ###

        if( $mainArr[self::$keySwitch] === true )
        {
            self::onDDOSer(); # Внутри жесткий редирект или exit()
            dd('Никогда не вызовется'); #
        }

        # - ###
        # Пока не ддосер
        # 2 - Достаточно ли дат?

        # Полностью ли заполнен массив дат? - Достаточно ли инфы для сравнения?
        if( count($mainArr[self::$keyDates]) < self::$datesMaxCount  )
        {
            # Пишу новую дату в конец
            $mainArr[self::$keyDates] []= self::getDateNow();

            SessionMy::keySet(self::$mainKey,$mainArr);
            SessionMy::sessionSave();

            #echo('Дат мало - Добавлена 1 дата');
            return;
        }

        # - ###
        # Есть нужное кол-во дат
        # Обновляю старейшую

        # Убираю самую старую дату, если уже лимит.
        $dateShifted = array_shift( $mainArr[self::$keyDates] ); # Сам выберет 1 элемент + изменит ключи
        # Итого на 1 меньше

        # Добавляю новую дату
        $mainArr[self::$keyDates] []= self::getDateNow();
        # Итого ровно максимум дат

        # Промежуточный сейв.
        SessionMy::keySet(self::$mainKey, $mainArr);
        SessionMy::sessionSave();

        # - ###
        # Получаю крайние и сравниваю

        $dateOldest = $mainArr[self::$keyDates][0];
        $dateNewest = $mainArr[self::$keyDates][self::$datesMaxCount-1];

        $carbon1 = Carbon::createFromTimeString($dateOldest);
        $carbon2 = Carbon::createFromTimeString($dateNewest);

        # Вычисляю интервал секунд между крайними датами.
        $diffSec = $carbon1->diffInSeconds($carbon2);

        #echo "Убрана дата: $dateShifted";
        #echo "Старейшая=$dateOldest  Новейшая=$dateNewest";
        #echo "Интервал секунд = $diffSec";

        # Сравниваю его в минимальным.
        if( $diffSec <= self::$datesMinIntervalSec )
        {
            self::setDdoserSwitchVal(true,false);

            self::afterDdoserBanActions();

            return; # Так как надо сохранить сессию.
        }

        # - ###
        # Все норм, уже сохранялся, пожтому просто выхожу.

        return;
    }


    # - ### ### ###
    #   NOTE:

    # Просто создать ключ
    public static function createMainKey()
    {
        $IpArr = Requester::getIpInfo();
        $UaArr = Requester::getUserAgentInfo();
        $arrayDefs = [
            self::$keyIP => $IpArr['SUB_39'],
            self::$keyUA => $UaArr['SUB_256'],
            self::$keyUAExt => $UaArr['EXTENDED_JSON'],
            self::$keyStatReqCount => 1, # Всего запросов за все время.
            self::$keyStatReqCountAfterBan => 0, # Попыток захода после бана(сколько отразили)
            self::$keySwitch => false, # Забанен ли
            self::$keyDates => array()
        ];

        $arrayDefs[self::$keyDates] []= self::getDateNow();

        SessionMy::keySet(self::$mainKey,$arrayDefs);
        SessionMy::sessionSave();

        return;
    }

    public static function setDdoserSwitchVal(bool $isDdoser, $needAfterBanActions=true)
    {
        $mainArr = self::mainKeyGet();
        $mainArr[self::$keySwitch] = $isDdoser;

        SessionMy::keySet(self::$mainKey, $mainArr);
        SessionMy::sessionSave();

        if($needAfterBanActions)
            self::afterDdoserBanActions();
    }


    # CONCEPT: Дописать запись в бд.
    public static function afterDdoserBanActions()
    {
        $mainArr = self::mainKeyGet();

        $ipFin = $mainArr[self::$keyIP];

        $msg = "$ipFin => Выдан бан => ".json_encode($mainArr);

        Log::channel(self::$logChannelName)->info($msg);

        try{ # На случай перегрузки БД
            #AccountHistory::addLog(0, 'MISC', 'DDOS_CATCHED',[SF::Get_User_Ip().' Забанен','Сессия json в этом логе'],$mainArr);
            Log::channel(self::$logChannelName)->info($ipFin.' => В лог БД успешно добавлена запись');
        }catch(\Throwable $e){
            Log::channel(self::$logChannelName)->info($ipFin.' => БД была перегружена - не смог добавить лог в бд => '.$e->getMessage());
        }

    }



    # Если нашли ддосера.
    public static function onDDOSer()
    {
        # NOTE: Уже не факт    Тут уже пофиг на сессию, ибо ключи уже сохранены.


        $mainArr = self::mainKeyGet();
        $mainArr[self::$keyStatReqCountAfterBan] += 1;
        SessionMy::keySet(self::$mainKey, $mainArr);
        SessionMy::sessionSave();

        $tryCount = $mainArr[self::$keyStatReqCountAfterBan];

        # Пишу лог в файл только каждые 4 попытки. Чтоб не грузить систему.
        if( $tryCount % 4 === 0 )
        {
            $ipInfo = Requester::getIpInfo();
            $ipFin = $ipInfo['SUB_16'];

            $msg = "$ipFin => Попытка №$tryCount -> ".Request::url();
            Log::channel(self::$logChannelName)->info($msg);
        }

        switch( self::$onDdosAction )
        {
            case 'REDIR':
                header("Location: ".self::$onDdosRedirTarget);
                exit;
                break;

            case 'MSG':
                echo self::$onDdosMsg;
                exit;
                break;
        }

        dd('Вылет из свитча');
    }


    # - ### ### ###
    #   NOTE:

    # Единое место получения дат.
    public static function getDateNow():string
    {
        return CarbonDT::getNowMsk(true);
    }

    # Единое место получения
    public static function mainKeyGet()
    {
        return SessionMy::keyGet(self::$mainKey);
    }

    public static function mainKeyForget()
    {
        SessionMy::keyForget(self::$mainKey);
        SessionMy::sessionSave();

        Log::channel(self::$logChannelName)->info('Сброшен ключ');
    }


    # - ### ### ###
    #   NOTE:


    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

    /* Убрано насовсем, но может пригодиться

    */
} # End class
