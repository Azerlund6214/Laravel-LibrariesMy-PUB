<?php

namespace LibMy;



/**
 * Универсальный генератор расписаний, например для постов в группе.
 */
class GenDatesSchedule
{
    # - ### ### ###

    public static $monthDay = [
        '2023' => [ 1 => 31 , 2 => 28 , 3 => 31 ,  4 => 30 ,  5 => 31 ,  6 => 30 ,
                    7 => 31 , 8 => 31 , 9 => 30 , 10 => 31 , 11 => 30 , 12 => 31 ,  ],
        '2024' => [ 1 => 31 , 2 => 29 , 3 => 31 ,  4 => 30 ,  5 => 31 ,  6 => 30 ,
                    7 => 31 , 8 => 31 , 9 => 30 , 10 => 31 , 11 => 30 , 12 => 31 ,  ],
        '2025' => [ 1 => 31 , 2 => 28 , 3 => 31 ,  4 => 30 ,  5 => 31 ,  6 => 30 ,
	                7 => 31 , 8 => 31 , 9 => 30 , 10 => 31 , 11 => 30 , 12 => 31 ,  ],
        '2026' => [ 1 => 31 , 2 => 28 , 3 => 31 ,  4 => 30 ,  5 => 31 ,  6 => 30 ,
                    7 => 31 , 8 => 31 , 9 => 30 , 10 => 31 , 11 => 30 , 12 => 31 ,  ],
    ]; # https://kalendata.ru/2026/

    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###
	
	# NOTE: Итоговый рабочий метод, ручной вызов.
	public static function mainTestFunc_PostsScheduleJSON(  )
	{
		# /* # = # = # /
		$RES = self::generateWithOptsArr(); # 9-12-15-18-21
		file_put_contents('Эталон = FINAL-DATES = 4 = 8-12-16-20 = 2023-2024-2025 =#.json', $RES['JSON_PRETTY_2']);
		dd($RES);
		/* # = # = # */
	}
	
    # - ### ### ###

    # NOTE: Предполагается ручной вызов и настройка


    public static function generateWithOptsArr():array
    {
        $OPTS = [
            'YEARS' => [
                '2023',
                '2024',
                '2025',
            ],
            'TIMES' => [  # 0-3-6-9-12-15-18-21
                #'00:00:00',
                #'03:00:00',
                #'06:00:00',
                #'09:00:00',
                #'12:00:00',
                #'15:00:00',
                #'18:00:00',
                #'21:00:00',
                
                '08:00:00',
                '12:00:00',
                '16:00:00',
                '20:00:00',
            ], # NOTE: Строго по порядку.

            'NO_UNIX' => true,
        ];

        return self::genWithOpts($OPTS);
    }


    public static function genWithOpts($OPTS)
    {
        $FIN['ARR'] = [ ];

        # - ###

        foreach($OPTS['YEARS'] as $year)
            foreach( range(1 ,12) as $month )
                foreach( range(1 ,self::$monthDay[$year][$month]) as $day )
                    foreach($OPTS['TIMES'] as $time)
                    {
                        $date = "{$year}-";
                        $date .= ($month < 10) ? "0{$month}-" : "{$month}-";
                        $date .= ($day < 10) ? "0{$day}" : "{$day}";

                        $dt_t = "{$date} {$time}";

                        if( $OPTS['NO_UNIX'] )
                            $FIN['ARR'] []= $dt_t;
                        else
                            $FIN['ARR'][$dt_t] = strtotime($dt_t);
                    }

        # - ###

        $timeLast = last($OPTS['TIMES']);

        $FIN['JSON_RAW'] = json_encode($FIN['ARR']);
        $FIN['JSON_PRETTY'] = json_encode($FIN['ARR'],JSON_PRETTY_PRINT);
        $FIN['JSON_PRETTY_2'] = str_replace("{$timeLast}\",\n" ,"{$timeLast}\",\n\n" ,$FIN['JSON_PRETTY']);

        return $FIN;
    }

	# - ### ### ###
	#   NOTE:
	
    # FINAL  2023 = 12/16/21   8/12/16/20   Старый
    public static function scheduleDatesUnivGenerator($noUnix=true):array
    {
        $monthDays = self::$monthDay['2023'];

        $dailyTime = [
            '08:00:00',
            '12:00:00',
            '16:00:00',
            '20:00:00',
        ];


        $FIN = [];


        foreach(range(1,12) as $month)
        {
            foreach(range(1,31) as $day)
            {

                if( $day === $monthDays[$month]+1 )
                    break;

                $date  = '2023-';
                $date .= ($month < 10) ? "0{$month}-" : "{$month}-" ;
                $date .= ($day   < 10) ? "0{$day}"   : "{$day}"   ;

                foreach($dailyTime as $time)
                {
                    # Эталон = '2023-02-10 9:00:00'

                    $dt_t = $date.' '.$time;

                    if( $noUnix )
                    {
                        $FIN []= $dt_t;
                        continue;
                    }

                    $dt_u = strtotime($dt_t);
                    $FIN[$dt_t] = $dt_u;

                }

            }

        }

        return $FIN;
    }

    
    # - ### ### ###
    #   NOTE:

	
    # - ### ### ###
    #   NOTE:
	

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
