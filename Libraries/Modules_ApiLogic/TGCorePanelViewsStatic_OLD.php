<?php

namespace LibMy;



/** Полный расчет цифр для таблицы накрутки постов в телеграме.
 * Тут только сами расчеты, цифры.
 * Крайне узкопрофильный класс.
 */
class TGCorePanelViewsStatic_OLD
{
    # - ### ### ###

    public $OPTS = [

        '-111' => [ # KINO-1
            'SMOOTH_DATE_BEG'  => '2023-01-01 00:00:00', 'SMOOTH_DATE_END'  => '2023-02-01 00:00:00',
            'SMOOTH_COUNT_MIN' => 100, 'SMOOTH_COUNT_MAX' => 1000,
            'SMOOTH_RANDOM' => [ 0 , 50 ], 'STATIC_RANDOM' => [ -150 , 150 ],
            'STATIC_COUNT' => 1000,
        ],

        '-222' => [ # KINO-2 N
            'SMOOTH_DATE_BEG'  => '2023-01-01 00:00:00', 'SMOOTH_DATE_END'  => '2023-02-01 00:00:00',
            'SMOOTH_COUNT_MIN' => 100, 'SMOOTH_COUNT_MAX' => 1000,
            'SMOOTH_RANDOM' => [ 0 , 50 ], 'STATIC_RANDOM' => [ -150 , 150 ],
            'STATIC_COUNT' => 1000,
        ],

        '-333' => [ # KINO-3 M
            'SMOOTH_DATE_BEG'  => '2023-01-01 00:00:00', 'SMOOTH_DATE_END'  => '2023-02-01 00:00:00',
            'SMOOTH_COUNT_MIN' => 100, 'SMOOTH_COUNT_MAX' => 1000,
            'SMOOTH_RANDOM' => [ 0 , 50 ], 'STATIC_RANDOM' => [ -150 , 150 ],
            'STATIC_COUNT' => 1000,
        ],

        '-444' => [ # game
            'SMOOTH_DATE_BEG'  => '2023-01-01 00:00:00', 'SMOOTH_DATE_END'  => '2023-02-01 00:00:00',
            'SMOOTH_COUNT_MIN' => 100, 'SMOOTH_COUNT_MAX' => 1000,
            'SMOOTH_RANDOM' => [ 0 , 50 ], 'STATIC_RANDOM' => [ -150 , 150 ],
            'STATIC_COUNT' => 1000,
        ],

        #'' => [ ],
        ];

    public $OPTS_CURR = [];
    public $MSG_Curr = [];

    # - ###

    public $OPT_PromoWordsArr = ['реклама','промокод','подписка','скачай','скачивай','руб в месяц','БЕСПЛАТНО','мобильное'];

    public $OPT_MinViewsToAdd = 100; # Если меньше, значит скип.
    public $OPT_MaxViewsToAdd = 2000; # Если больше, значит что-то криво насчитало.

    #public int $OPT_PerekrutCount = 100; #

    public $serviceNumber = '708';
    public $costPer1000 = 0.3;

    # - ### ### ###

    public $CALC_INFO = [
        'IS_PROMO_POST' => '',

        'IS_SMOOTH' => '',
        'IS_STATIC' => '',

        'POWER_PERCENT_INT' => '',
        'POWER_VIEWS_INT' => '',

        'RANDOM_STATIC' => '',
        'RANDOM_SMOOTH' => '',


        'VIEWS_CURRENT' => '',          # Скольк осейчас в посте, по факту
        'VIEWS_TARGET_FULL' => '',      # Сколько в итоге должно быть, без рандома
        'VIEWS_TARGET_WITH_RAND' => '', # С добавкой рандома

        'VIEWS_FINAL_TO_ADD' => '', # Сколько не хватает
    ];

    public $POST_INFO = [  ]; # Все в методе

    public $FINAL_INFO = [
        'INFO_DEV_RESULTS' => '',
        'INFO_POST' => '',
        #'TEXT_LONG' => '', # Спорно

        'NEED_ADD_BOOL' => true,
        'NEED_ADD_COUNT' => -1,

        'REASON_MAIN' => 'NEED',
        'REASON_DESC' => '...',

        'TEXT_MASS_ORDER' => '...',
        #'TEXT_OLD' => '...',
        'PRICE_RUB' => 0,

        #'' => '',

    ]; # Итоги


    # - ### ### ###
    #   NOTE:

    public function optSetGroup($groupId)
    {
        $this->OPTS_CURR = $this->OPTS[$groupId];

        $this->POST_INFO['GROUP'] = $groupId;
    }

    public function optSetMsgOne($msgPrepArr)
    {
        $this->MSG_Curr = $msgPrepArr;
    }


    public function fillPostInfo()
    {
        $this->POST_INFO['GROUP'] = '-'.$this->MSG_Curr['CHANNEL_ID'];
        $this->POST_INFO['URL'] = $this->MSG_Curr['MSG_URL'];
        $this->POST_INFO['DATE_T'] = $this->MSG_Curr['DATE_T'];
        $this->POST_INFO['DATE_U'] = $this->MSG_Curr['DATE_U'];
        $this->POST_INFO['TEXT_ONE'] = $this->MSG_Curr['TEXT_IN_ONE_STR'];
        $this->POST_INFO['TEXT_FIRST'] = $this->MSG_Curr['TEXT_FIRST_STR'];
        $this->POST_INFO['VIEWS'] = $this->MSG_Curr['STAT_VIEWS'];
    }


    # - ### ### ###
    #   NOTE:


    public function check_IsPromo()
    {
        $msgTextLower = strtolower($this->MSG_Curr['TEXT_IN_ONE_STR']);
        foreach( $this->OPT_PromoWordsArr as $word )
        {
            # Все в нижнем регистре
            if( str_contains($msgTextLower,strtolower($word)) )
            {
                $this->CALC_INFO['IS_PROMO_POST'] = true;
                return;
            }
        }

        $this->CALC_INFO['IS_PROMO_POST'] = false;

    }

    public function check_SmoothOrStatic()
    {
        $msgDate_U = $this->MSG_Curr['DATE_U'];

        $dateSmoothMax_U = DaterUC::convertClassicToUnix($this->OPTS_CURR['SMOOTH_DATE_END']);

        if( $msgDate_U >= $dateSmoothMax_U )
        {
            $this->CALC_INFO['IS_STATIC'] = true;
            $this->CALC_INFO['IS_SMOOTH'] = false;
        }
        else
        {
            $this->CALC_INFO['IS_STATIC'] = false;
            $this->CALC_INFO['IS_SMOOTH'] = true;
        }
    }


    public function calc_Smooth_ViewsPower()
    {
        # - ###
        if( $this->CALC_INFO['IS_STATIC'] )
        {
            $this->CALC_INFO['POWER_PERCENT_INT'] = -123;
            return;
        }
        # - ###

        $msgDate_U = $this->MSG_Curr['DATE_U'];

        # Преобразую даты
        $dateSmoothMin_U = DaterUC::convertClassicToUnix($this->OPTS_CURR['SMOOTH_DATE_BEG']);
        $dateSmoothMax_U = DaterUC::convertClassicToUnix($this->OPTS_CURR['SMOOTH_DATE_END']);

        # Сколько идет весь отрезок
        $intervalTimeFull = $dateSmoothMax_U - $dateSmoothMin_U;
        $intervalTimeCurrent = $msgDate_U - $dateSmoothMin_U;

        # Насколько % его прошел текущий пост.
        $postPower_PercentInt = (int) floor(($intervalTimeCurrent / $intervalTimeFull)*100);

        $this->CALC_INFO['POWER_PERCENT_INT'] = $postPower_PercentInt;

    }

    public function calc_Smooth_ViewsCount()
    {
        # - ###
        if( $this->CALC_INFO['IS_STATIC'] )
        {
            $this->CALC_INFO['POWER_VIEWS_INT'] = -123;
            return;
        }
        # - ###

        $postPower_Views = (int) floor(($this->CALC_INFO['POWER_PERCENT_INT'] / 100)*$this->OPTS_CURR['SMOOTH_COUNT_MAX']);

        # Если вышло меньше минималки, то ставлю минималку
        if( $postPower_Views < $this->OPTS_CURR['SMOOTH_COUNT_MIN'] )
            $postPower_Views = $this->OPTS_CURR['SMOOTH_COUNT_MIN'];

        $this->CALC_INFO['POWER_VIEWS_INT'] = $postPower_Views;

    }


    public function calc_RandomCounts()
    {
        # - ###

        # NOTE Делаю рандом сид каждый раз
        srand((int)(microtime(true)*100));

        # - ###

        $sm = $this->OPTS_CURR['SMOOTH_RANDOM'];
        $this->CALC_INFO['RANDOM_SMOOTH'] = random_int($sm[0],$sm[1]);

        $st = $this->OPTS_CURR['STATIC_RANDOM'];
        $this->CALC_INFO['RANDOM_STATIC'] = random_int($st[0],$st[1]);

        # - ###
    }


    public function calc_AllViewsCounts()
    {
        # - ###

        $this->CALC_INFO['VIEWS_CURRENT'] = (int)$this->MSG_Curr['STAT_VIEWS'];

        # - ###

        if( $this->CALC_INFO['IS_SMOOTH'] )
        {
            $this->CALC_INFO['VIEWS_TARGET_FULL'] = $this->CALC_INFO['POWER_VIEWS_INT'];

            $this->CALC_INFO['VIEWS_TARGET_WITH_RAND'] =
                $this->CALC_INFO['VIEWS_TARGET_FULL']+$this->CALC_INFO['RANDOM_SMOOTH'];

        }
        else
        {
            $this->CALC_INFO['VIEWS_TARGET_FULL'] = $this->OPTS_CURR['STATIC_COUNT'];

            $this->CALC_INFO['VIEWS_TARGET_WITH_RAND'] =
                $this->CALC_INFO['VIEWS_TARGET_FULL']+$this->CALC_INFO['RANDOM_STATIC'];

        }

        $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] =
            $this->CALC_INFO['VIEWS_TARGET_WITH_RAND']-$this->CALC_INFO['VIEWS_CURRENT'];



        # - ###
    }





    public function makeFinalDecision()
    {
        # Заполнит итоговый массив

        # - ###
        # Проверки надо ли

        if( $this->POST_INFO['VIEWS'] <= 10 )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'NEED_FAST';
            $this->FINAL_INFO['REASON_DESC'] = 'Около 0 просмотров';
        }


        if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] < $this->OPT_MinViewsToAdd )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_MIN';
            $this->FINAL_INFO['REASON_DESC'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'].' < '.$this->OPT_MinViewsToAdd;
            $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
        }

        if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] < 300 )
            if( $this->CALC_INFO['VIEWS_CURRENT'] > 800 )
            {
                $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_ENOUGH';
                $this->FINAL_INFO['REASON_DESC'] = 'Уже накрутили достаточно';
                $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
            }



        if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] >= $this->OPT_MaxViewsToAdd )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_MAX';
            $this->FINAL_INFO['REASON_DESC'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'].' >= '.$this->OPT_MaxViewsToAdd;
            $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
        }

        if( $this->CALC_INFO['IS_PROMO_POST'] )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'POST_PROMO';
            $this->FINAL_INFO['REASON_DESC'] = 'Промо пост';
            $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
        }

        # - ###

        # Если отбраковали
        if( ! $this->FINAL_INFO['NEED_ADD_BOOL'] )
        {

            return;
        }

        # - ###

        $this->FINAL_INFO['NEED_ADD_COUNT'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'];


        $this->FINAL_INFO['TEXT_MASS_ORDER'] =
            $this->serviceNumber.' | '.$this->POST_INFO['URL'].' | '.$this->CALC_INFO['VIEWS_FINAL_TO_ADD'];

        $num = ($this->FINAL_INFO['NEED_ADD_COUNT']/1000*$this->costPer1000);
        $this->FINAL_INFO['PRICE_RUB'] = number_format($num,2,'.');


    }

    # - ### ### ###
    #   NOTE:

    public function calculateAll()
    {
        $this->fillPostInfo();

        $this->check_IsPromo();

        $this->check_SmoothOrStatic();

        $this->calc_Smooth_ViewsPower();

        $this->calc_Smooth_ViewsCount();

        $this->calc_RandomCounts();

        $this->calc_AllViewsCounts();



        $this->makeFinalDecision();
    }


    public function getResult()
    {
        return [
            'CALC' => $this->CALC_INFO,
            'POST' => $this->POST_INFO,
            'FINAL' => $this->FINAL_INFO,
        ];
    }


    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
