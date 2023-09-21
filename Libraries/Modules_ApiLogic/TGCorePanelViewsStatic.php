<?php

namespace LibMy;



/**
 * Более новая версия сосднего класса.
 * В будущем выяснить какая более актуальна и удалить одну.
 *
 */
class TGCorePanelViewsStatic
{
    # - ### ### ###

    public $OPTS_General = [];

    public $MSG_Curr = [];



    public $CALC_INFO = [
        'IS_PROMO_POST' => '',

        'RANDOM_STATIC' => '',

        'VIEWS_CURRENT' => '',          # Скольк осейчас в посте, по факту
        'VIEWS_TARGET_FULL' => '',      # Сколько в итоге должно быть, без рандома
        'VIEWS_TARGET_WITH_RAND' => '', # С добавкой рандома

        'VIEWS_FINAL_TO_ADD' => '', # Сколько не хватает
    ];

    public $POST_INFO = [  ]; # Все в методе

    public $FINAL_INFO = [
        #'INFO_DEV_RESULTS' => '',
        #'INFO_POST' => '',
        #'TEXT_LONG' => '', # Спорно

        'NEED_ADD_BOOL' => true,
        'NEED_ADD_COUNT' => -1,

        'REASON_MAIN' => 'DEF',
        'REASON_COLOR' => 'lightgray', # Дефолтный цвет
        'REASON_DESC' => '...',

        #'TEXT_MASS_ORDER' => '...',
        #'PRICE_RUB' => 0,
        #'TEXT_OLD' => '...',

        #'' => '',

    ]; # Итоги

    # - ### ### ###

    # // public int $OPT_MaxViewsToAdd = 2000; # Если больше, значит что-то криво насчитало.

    public $OPT_PromoWordsArr = ['реклама','промокод','подписка','скачай','скачивай','руб в месяц','БЕСПЛАТНО','мобильное','Акция','бесплатно'];



    # - ### ### ###


    public function optSetGroup($groupId, $optsArr)
    {
        $this->OPTS_General = $optsArr;

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
        $this->POST_INFO['TEXT_RAW'] = $this->MSG_Curr['TEXT_RAW'];
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

    public function calc_RandomCounts()
    {
        # - ###

        # NOTE Делаю рандом сид каждый раз
        srand((int)(microtime(true)*100));

        # - ###

        $st = $this->OPTS_General['STATIC_RANDOM'];
        $this->CALC_INFO['RANDOM_STATIC'] = random_int($st[0],$st[1]);

        # - ###
    }

    public function calc_AllViewsCounts()
    {
        # - ###

        $this->CALC_INFO['VIEWS_CURRENT'] = (int)$this->MSG_Curr['STAT_VIEWS'];

        # - ###

        $this->CALC_INFO['VIEWS_TARGET_FULL'] = $this->OPTS_General['STATIC_COUNT'];

        $this->CALC_INFO['VIEWS_TARGET_WITH_RAND'] =
            $this->CALC_INFO['VIEWS_TARGET_FULL']+$this->CALC_INFO['RANDOM_STATIC'];

        $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] =
            $this->CALC_INFO['VIEWS_TARGET_WITH_RAND'] - $this->CALC_INFO['VIEWS_CURRENT'];

        # - ###
    }


    public function makeFinalDecision(  )
    {
        # Заполнит итоговый массив

        # - ###
        # Проверки надо ли

        if( $this->POST_INFO['VIEWS'] <= 5 )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'NEED_FAST';
            $this->FINAL_INFO['REASON_COLOR'] = 'red';
            $this->FINAL_INFO['REASON_DESC'] = 'Просмотров <= 5';
        }

        # NOTE: По дефолту всегда считаю что 100.
        if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] < 100 )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_MIN';
            $this->FINAL_INFO['REASON_COLOR'] = 'red';
            $this->FINAL_INFO['REASON_DESC'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'].' < 100';
            $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
        }

        $percent = floor( 100 * $this->CALC_INFO['VIEWS_CURRENT'] / $this->CALC_INFO['VIEWS_TARGET_FULL']);
        #if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] < 200 )
        #    if( $this->CALC_INFO['VIEWS_CURRENT'] > 900 )
        if( $percent >= 93 )
            {
                $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_ENOUGH';
                $this->FINAL_INFO['REASON_COLOR'] = 'lime';
                $this->FINAL_INFO['REASON_DESC'] = 'Уже накрутили достаточно (93%+)';
                $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
            }


        #if( $this->CALC_INFO['VIEWS_FINAL_TO_ADD'] >= $this->OPT_MaxViewsToAdd )
        #{
        #    $this->FINAL_INFO['REASON_MAIN'] = 'COUNT_MAX';
        #    $this->FINAL_INFO['REASON_DESC'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'].' >= '.$this->OPT_MaxViewsToAdd;
        #    $this->FINAL_INFO['NEED_ADD_BOOL'] = false;
        #}


        if( $this->CALC_INFO['IS_PROMO_POST'] )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'POST_PROMO';
            $this->FINAL_INFO['REASON_COLOR'] = 'deepskyblue';
            $this->FINAL_INFO['REASON_DESC'] = 'Промо пост';
            $this->FINAL_INFO['NEED_ADD_BOOL'] = true;
        }

        # - ###

        # Если отбраковали
        if( ! $this->FINAL_INFO['NEED_ADD_BOOL'] )
        {
            return;
        }

        # - ###

        if( ! in_array($this->FINAL_INFO['REASON_MAIN'],['NEED_FAST','POST_PROMO']) )
        {
            $this->FINAL_INFO['REASON_MAIN'] = 'NEED';
            $this->FINAL_INFO['REASON_COLOR'] = 'yellow';
            $this->FINAL_INFO['REASON_DESC'] = 'Надо крутить';
        }


        $this->FINAL_INFO['NEED_ADD_COUNT'] = $this->CALC_INFO['VIEWS_FINAL_TO_ADD'];


        #$this->FINAL_INFO['TEXT_MASS_ORDER'] =
        #    $serviceCurr['S_NUM'].' | '.$this->POST_INFO['URL'].' | '.$this->CALC_INFO['VIEWS_FINAL_TO_ADD'];

        #$num = ($this->FINAL_INFO['NEED_ADD_COUNT']/1000*$serviceCurr['PRICE_1k']);
        #$this->FINAL_INFO['PRICE_RUB'] = number_format($num,2,'.');


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


    public function calculateAll( )
    {
        $this->fillPostInfo();

        $this->check_IsPromo();


        $this->calc_RandomCounts();

        $this->calc_AllViewsCounts();


        $this->makeFinalDecision(  );
    }


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
