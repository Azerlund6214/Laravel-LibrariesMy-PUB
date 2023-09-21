<?php

namespace LibMy;


/**
 * Простенький класс для замера времени исполнения кода.
 *
 */ # ДатаВремя создания: 250921 0027
class TimerMy
{
    # - ### ### ###

    public $timeStart = 0.0;

    # - ### ### ###

    public function __construct( $startNow = true )
    {
        if($startNow)
            $this->startNow();
    }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    /**
     * Установить точку отсчета.
     */
    public function startNow()
    {
        $this->timeStart = microtime(true);
    }
    public function startCustom($customTimeFloat)
    {
        $this->timeStart = $customTimeFloat;
    }
    
    
    /**
     * Просто вычислить прошедшее время и сразу перегнать в мс
     */
    private function getTimeIntervalRawMs()
    {
        # Сразу вычисляю разницу в МС.
        return (microtime(true) - $this->timeStart) * 1000;
    }

    /**
     * Универсальный получатель разницы времени. Дергается из публичных геттеров.
     * @param string $type ms ms4 s s2 raw
     * @param bool $needPrefix Добавлять ли расшифровку в конец
     * @return string
     */
    private function getCurrentTime($type='ms',$needPrefix=true)
    {
        $timeIntervalMsRaw = $this->getTimeIntervalRawMs();

        switch($type)
        {
            case 'ms':
                $res = (string)(int) $timeIntervalMsRaw; # Целое
                if($needPrefix)
                    return $res.'ms';
                else
                    return $res;
                break;

            case 'ms4':
                $res = number_format($timeIntervalMsRaw,4,'.','');
                if($needPrefix)
                    return $res.'ms';
                else
                    return $res;
                break;

            case 's':
                $res = (string)(int)($timeIntervalMsRaw/1000);
                if($needPrefix)
                    return $res.'s';
                else
                    return $res;
                break;

            case 's2':
                $res = number_format(($timeIntervalMsRaw/1000),2,'.','');
                if($needPrefix)
                    return $res.'s';
                else
                    return $res;
                break;

            case 'raw':
                    return $timeIntervalMsRaw;
                break;
        }

        return $timeIntervalMsRaw;
    }

    # - ### ### ###
    #   NOTE: Итоговые геттеры.

    public function getTimeMs($needPrefix=true)
    {
        return $this->getCurrentTime('ms',$needPrefix);
    }
    public function getTimeMs4($needPrefix=true)
    {
        return $this->getCurrentTime('ms4',$needPrefix);
    }

    public function getTimeSec($needPrefix=true)
    {
        return $this->getCurrentTime('s',$needPrefix);
    }
    public function getTimeSec2($needPrefix=true)
    {
        return $this->getCurrentTime('s2',$needPrefix);
    }

    public function getTimeRawMs($needPrefix=true)
    {
        return $this->getCurrentTime('raw',$needPrefix);
    }

    public function dumpTimeMs($text='')
    {
        dump($text.$this->getTimeMs(false).' ms');
    }
	
	
	# Быстрый расчет для кастом 2 дат.
	public static function calcBetween_GetMs($from,$to)
	{
		return (string)(int)(($to - $from) * 1000);
	}

    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
