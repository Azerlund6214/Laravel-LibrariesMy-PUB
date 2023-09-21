<?php

namespace LibMy;



/** Класс для автоматизации оформления заказов в одном конкретном телеграм-боте.
 * Есть большой бот с кучей менюшек и подменю(Десятки кнопок, 7лвл вложенности), для заказов услуг.
 * Этот класс позволяет автоматически прокликать нужные кнопки и совершить заказ нужных услуг.
 * У того бота нет апи, поэтому такие костыли. Счет заказов на сотни в день, нужна была автоматизация.
 * Стабильно отработал в продакшене.
 */
class TGCoreBotClicker
{
    # - ### ### ###

    public $TGC; # apiTGCore

    public $stagesChain = [ ];


    public $STAGE_INFO_DEF = [ ];
    public $STAGE_INFO = [
        'MAIN' => [
            'CURR_STAGE' => 'UNDEF',
            'CURR_MSG_FULL' => '',
            'CURR_MSG_JSON' => '',
            'CURR_MSG_ONESTR' => '',

            'ACTION_FULL' => '',
            'ACTION_ANSWER_RES' => '',
            'ACTION_SENDED' => false,

            'WAIT_HAS' => false,
            'WAIT_COUNT' => 0,
            'WAIT_SUM_SEC' => 0,
        ],

        'RESULT' => [
            'STAGE_FAIL' => false,
            'STAGE_SUCCESS' => false,
            'REASON' => '',

            'DATA' => [ ],
        ],
    ];


    public $TASK_INFO_DEF = [ ];
    public $TASK_INFO = [
        'TIME' => [
            'TIME_BEGIN_U' => 0.0,
            'TIME_BEGIN_T' => 0.0,
            'TIME_ENDED_U' => 0.0,
            'TIME_ENDED_T' => 0.0,
            'TIME_FINAL_FULL' => 0,
            'TIME_FINAL_MS' => 0,
            'TIME_FINAL_S' => 0,
        ],
        'STAGES_INFO' => [ ],
        'RESULT' => [
            'TASK_ENDED' => false,
            'TASK_SUCCESS' => false,
            'TASK_FAIL' => false,
            'LAST_END_REASON' => '',

            'FINAL_DATA' => [ ],
        ],
    ];
    

    # - ### ### ###
    #   NOTE: Стартовое

    public function __construct($TGC)
    {
        $this->TGC = $TGC;

        $this->STAGE_INFO_DEF = $this->STAGE_INFO;
        $this->TASK_INFO_DEF = $this->TASK_INFO;
    }

    public function flushToDefaults_StageInfo()
    {
        $this->STAGE_INFO = $this->STAGE_INFO_DEF;
    }
    public function flushToDefaults_TaskInfo()
    {
        $this->TASK_INFO = $this->TASK_INFO_DEF;
    }


    # - ### ### ###
    #   NOTE: Время работы

    public function setTimeTaskBegin()
    {
        $this->TASK_INFO['TIME']['TIME_BEGIN_U'] = microtime(true);
        $this->TASK_INFO['TIME']['TIME_BEGIN_T'] = date("Y-m-d H:i:s");
    }
    public function setTimeTaskEnd()
    {
        $this->TASK_INFO['TIME']['TIME_ENDED_U'] = microtime(true);
        $this->TASK_INFO['TIME']['TIME_ENDED_T'] = date("Y-m-d H:i:s");

        $this->TASK_INFO['TIME']['TIME_FINAL_S_FULL'] =
            $this->TASK_INFO['TIME']['TIME_ENDED_U'] - $this->TASK_INFO['TIME']['TIME_BEGIN_U'];

        $this->TASK_INFO['TIME']['TIME_FINAL_MS'] = (int) ($this->TASK_INFO['TIME']['TIME_FINAL_S_FULL'] * 1000 );

        $this->TASK_INFO['TIME']['TIME_FINAL_S'] =
            number_format(($this->TASK_INFO['TIME']['TIME_FINAL_MS'] / 1000),2);
    }

    public function get_StageInfo()
    {
        return $this->STAGE_INFO;
    }
    public function get_TaskInfo()
    {
        return $this->TASK_INFO;
    }

    public function fill_StageResult($stageSucc,$reason='',$data=[])
    {
        $this->STAGE_INFO['RESULT']['STAGE_SUCCESS'] = $stageSucc;
        $this->STAGE_INFO['RESULT']['STAGE_FAIL'] = (! $stageSucc);
        $this->STAGE_INFO['RESULT']['REASON'] = $reason;
        $this->STAGE_INFO['RESULT']['DATA'] = $data;
    }
    public function fill_TaskResult($taskEnd, $taskSucc, $reason='', $data=[])
    {
        $this->TASK_INFO['RESULT']['TASK_ENDED'] = $taskEnd;
        $this->TASK_INFO['RESULT']['TASK_SUCCESS'] = $taskSucc;
        $this->TASK_INFO['RESULT']['TASK_FAIL'] = (! $taskSucc);
        $this->TASK_INFO['RESULT']['LAST_END_REASON'] = $reason;
        $this->TASK_INFO['RESULT']['FINAL_DATA'] = $data;
    }


    # - ### ### ###
    #   NOTE: Цепочки действий

    # Сильно доделывать
    public function setStagesChainFor_ViewCheap($url, $count)
    {
        $time_uSleepDefault = 0.5;
        $time_waitAgainDefault = 1;

        #$url   = $this->targetUrl;
        #$count = $this->targetCount;


        $this->stagesChain = [
            /*'0_INFO' => [
                'TYPE'=>'SEND_MSG / BTN_CLICK / NOTHING', # Тип действия - отправить мсг / кликнуть кнопку / ждать
                'DATA'=>'btn_order', # Текст для отправки или коллбек
                'sleepMs'=>$time_uSleepDefault, # Сколько спать после действия
                'waitAgain'=>$time_waitAgainDefault, # Если этап еще не сменился - сколько ждать перед повторным чеком.
                'stageDetectText' => '',
            ], # */

            #'0_UNIV__START' => [ 'TYPE' => 'SEND_MSG', 'DATA' => '/start',
            #    'sleepMs' => 4, 'waitAgain' => $time_waitAgainDefault,
            #    'stageDetectText' => '123123123',   ],

            '1_UNIV__BEGIN' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_order',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Главное меню',   ],

            '2_UNIV__CATEGORY_PLATFORM' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_category_2',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Выберите интересующую вас категорию',   ],
            # - ############

            '3__CATEGORY_TYPE' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_subcategory_2_3',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Выберите интересующую вас подкатегорию или услугу:',   ],

            '4__LIST' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_service_2_sub_3_0',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Выберите интересующую вас услугу',   ],

            '5_ORDER_INFO' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_purchase_272',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Дешевые просмотры',   ],

            # - ############
            '11_UNIV__ORDER_COUNT_NEED' => [ 'TYPE' => 'SEND_MSG', 'DATA' => $count,
                'sleepMs' => 4, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Введите количество для заказа',   ],
            '12_UNIV__ORDER_COUNT_ACCEPT' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_order.next',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Чтобы выбрать другое количество',   ],
            '13_UNIV__ORDER_URL_NEED' => [ 'TYPE' => 'SEND_MSG', 'DATA' => $url,
                'sleepMs' => 4, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Введите ссылку',   ],

            # /* # Если это закомментить, то после 13 этапа будет постоянно видеть 5 этап и цикл ожидания.
            '14_UNIV__ORDER_URL_ACCEPT' => [ 'TYPE' => 'BTN_CLICK', 'DATA' => 'btn_order.yes',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Чтобы изменить ссылку',   ],


            '20_UNIV__ORDER_PROCESSING_WAIT' => [ 'TYPE' => 'WAIT_CHANGE', 'DATA' => '',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Обработка заказа',   ],
            '21_UNIV__ORDER_SUCCESS' => [ 'TYPE' => 'NOTHING', 'DATA' => '',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'Ваш заказ успешно принят в обработку',   ],

            '99_NO_BALANCE' => [ 'TYPE' => 'NOTHING', 'DATA' => '',
                'sleepMs' => $time_uSleepDefault, 'waitAgain' => $time_waitAgainDefault,
                'stageDetectText' => 'На вашем счету недостаточно средств.',   ],
            # */
        ];


        #$this->TGC = $tgc;
    }
    
    
    # - ### ### ###

    public function ACTION_PerformOneFullTask_ViewsCheap($url,$count)
    {
        # - ###

        $this->setTimeTaskBegin();
        $this->setStagesChainFor_ViewCheap($url,$count);

        # - ###

        $this->ACT_sendStart();
        Sleeper::sleeper(3,'Ждем после /Start');

        # - ###
        foreach( range(1,30) as $i )
        {
            $this->flushToDefaults_StageInfo();

            $msgFullCurr = $this->ACT_getMsgCurrent();
            $msgFullText = $msgFullCurr['TEXT_IN_ONE_STR'];
            $currStage = $this->ACT_detectCurrentStage();

            # - ###

            if( $currStage === '99_NO_BALANCE' ) dd('DD - Нет баланса. 99_NO_BALANCE',$this->get_StageInfo());

            #if( $i === 5 )
            #    dd("DD - Дебаг. i=$i",$this->get_StageInfo(),$this->get_TaskInfo());

            #if( $currStage === '13_UNIV__ORDER_URL_NEED' )
            #    dd('DD - Дебаг. 13_UNIV__ORDER_URL_NEED',$this->get_StageInfo(),$this->get_TaskInfo());


            # Не смогли определить текущий этап. Что-то пошло криво.
            if( $currStage === 'UNDEF')
            {
                $this->fill_StageResult(false,'STAGE_CURRENT_UNDEF');
                $this->fill_TaskResult(true,false,'STAGE_CURRENT_UNDEF');
            }

            # Если сейчас уже финал заказа, то вытащить данные и завершиться.
            if( $currStage === '21_UNIV__ORDER_SUCCESS')
            {
                $arrData = [];
                $arrData['TEXT_IN_ONE_STR'] = $msgFullText;
                $arrData['PRODUCT'] = explode('Услуга: ',$msgFullCurr['TEXT_ARR'][1])[1];
                $arrData['URL']    = explode('Ссылка: ',$msgFullCurr['TEXT_ARR'][2])[1];
                $arrData['COUNT'] = explode('Количество: ',$msgFullCurr['TEXT_ARR'][3])[1];
                $arrData['PRICE'] = explode('Цена: ',$msgFullCurr['TEXT_ARR'][5])[1];
                $arrData['PRICE'] = explode(' ',$arrData['PRICE'])[0];
                $this->fill_StageResult(true,'FINAL_ORDER',$arrData);
                $this->fill_TaskResult(true,true,'FINAL_ORDER',$arrData);
            }



            if( $this->TASK_INFO['RESULT']['TASK_ENDED'] === true )
            {   # Если финал или андеф

                $this->setTimeTaskEnd();
                $this->TASK_INFO['STAGES_INFO']["I=$i=$currStage"] = $this->get_StageInfo();
                return true;
            }

            # - ###

            $this->ACT_performCurrentStageAction();
            $this->ACT_waitChange();



            $this->TASK_INFO['STAGES_INFO']["I=$i=$currStage"] = $this->get_StageInfo();


            if( $this->TASK_INFO['RESULT']['TASK_ENDED'] === true )
            {   # Если финал или андеф

                $reason = $this->get_StageInfo()['RESULT']['REASON'];
                $this->fill_TaskResult(true,false,$reason );
                $this->setTimeTaskEnd();
                return true;
            }


        }# Цикл по этапам 1-30

        # - ###

        $this->setTimeTaskEnd();
        dd(__LINE__,$this->get_StageInfo());
        # - ###
    }

    
    # - ### ### ###
    #   NOTE:

    # FINAL
    public function ACT_sendStart()
    {
        $this->TGC->api_msgSend_Any('/start');
    }
    
    public function ACT_getMsgCurrent( $onlyReturn=false )
    {
        $this->TGC->action_groupGet_PostsWall_ContentALL_Last(3);

        $res = $this->TGC->getResult();
        $MsgCurrent_FULL = current($res); # Вязть 1 элемент


        if( $onlyReturn )
            return $MsgCurrent_FULL;


        $this->STAGE_INFO['MAIN']['CURR_MSG_FULL'] = $MsgCurrent_FULL;
        $this->STAGE_INFO['MAIN']['CURR_MSG_JSON'] = json_encode($MsgCurrent_FULL);
        $this->STAGE_INFO['MAIN']['CURR_MSG_ONESTR'] = $MsgCurrent_FULL['TEXT_IN_ONE_STR'];

        return $MsgCurrent_FULL;
    }
    
    public function ACT_detectCurrentStage()
    {
        $allStages = $this->stagesChain;
        $msgFullText = $this->STAGE_INFO['MAIN']['CURR_MSG_ONESTR'];

        $StageCurrent = 'UNDEF';

        # NOTE: Реверс, чтоб нормально отличал поздние этапы ибо там не меняется начало текста.
        foreach( array_reverse($allStages) as $stage_1 => $data_1  )
        {
            if( str_contains($msgFullText,$data_1['stageDetectText']) )
            {
                $StageCurrent = $stage_1;
                $StageCurrentArr = $data_1;
                break;
            }
        }

        $this->STAGE_INFO['MAIN']['CURR_STAGE'] = $StageCurrent;

        return $StageCurrent;
    }
    
    public function ACT_performCurrentStageAction()
    {

        $msgFullText = $this->STAGE_INFO['MAIN']['CURR_MSG_FULL']['TEXT_IN_ONE_STR'];
        $msgFull    = $this->STAGE_INFO['MAIN']['CURR_MSG_FULL'];
        $currStage = $this->STAGE_INFO['MAIN']['CURR_STAGE'];

        $currStageArr = $this->stagesChain[$currStage];

        switch( $currStageArr['TYPE'] )
        {
            case'SEND_MSG' : $this->TGC->api_msgSend_Any($currStageArr['DATA']); break;
            case'BTN_CLICK': $this->TGC->api_botButton_Click($msgFull['MSG_ID'],$currStageArr['DATA']); break;
            case'WAIT_CHANGE':  break;  # Будет просто цикл ожидания смены.
            #case'':  break;
        }

        $this->STAGE_INFO['MAIN']['ACTION_FULL'] = $currStageArr;
        $this->STAGE_INFO['MAIN']['ACTION_ANSWER_RES'] = $this->TGC->getResult();
        $this->STAGE_INFO['MAIN']['ACTION_SENDED'] = true;


    }

    public function ACT_waitChange()
    {
        $msgFullText = $this->STAGE_INFO['MAIN']['CURR_MSG_FULL']['TEXT_IN_ONE_STR'];
        $msgFull    = $this->STAGE_INFO['MAIN']['CURR_MSG_FULL'];
        $currStage = $this->STAGE_INFO['MAIN']['CURR_STAGE'];
        $currStageArr = $this->stagesChain[$currStage];

        #$timeInterval_RecheckUpdate_sec = 0.3;
        #$timeInterval_MaxWait_sec = 20;

        # - ###

        Sleeper::sleeper($currStageArr['sleepMs'], 'Ждем время после действия. ('.$currStage.')');

        # - ###

        #$intervals = (int) ceil($timeInterval_MaxWait_sec / $timeInterval_RecheckUpdate_sec);


        foreach( range(1,30) as $i )
        {

            if( $i === 30 )
            {   # Если за все разы не поменялось

                $this->fill_StageResult(false,'WAIT_LIMIT');
                $this->fill_TaskResult(true,false,'MSG_CHANGED');

                break;
            }


            $newMsgFull = $this->ACT_getMsgCurrent(true);
            $newMsgFullText = $newMsgFull['TEXT_IN_ONE_STR'];

            if( $msgFullText === $newMsgFullText )
            { # Остался старым

                # Если придется ждать, то возможно что-то пошло не так.
                # Поэтому для дебага вывожу инфу.
                if( $i === 1 )
                    dump('Придется ждать смены.  Текущая инфа:',$this->STAGE_INFO['MAIN']);

                $timeWaitSec = $currStageArr['waitAgain'];

                $this->STAGE_INFO['MAIN']['WAIT_HAS'] = true;
                $this->STAGE_INFO['MAIN']['WAIT_COUNT'] += 1;
                $this->STAGE_INFO['MAIN']['WAIT_SUM_SEC'] = $i*$timeWaitSec;

                Sleeper::sleeper($timeWaitSec,
                    'Текст не сменился - ждем. ($i='.$i.')',true);
                #usleep($oneStageArr['waitAgain']);

                continue;
            }
            else
            { # Сменился
                $this->fill_StageResult(true,'MSG_CHANGED');
                break;
            }

        } # Цикл ожидания

    }

    
    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
