<?php

namespace LibMy;



/**
 * Бросок кубиков с заданной вероятностью успеха.
 */ # 180823
class RandomDice
{
    # - ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ###
    
    
	public static function Dice_univPercent(int $targetPercent):bool
	{
		if( $targetPercent >= 100 ) return  true;
		if( $targetPercent <=   0 ) return false;
		
		# NOTE: Точность проверена, все считает ровно как надо, на 1кк итераций.
		
		return ( random_int(1,100) <= $targetPercent );
	}
	
	public static function DEBUG($targetPercent)
	{
		$cnt0 = 0;  $cnt1 = 0;  $iters=100000;
		foreach( range(0,$iters) as $i )
		{
			if(self::Dice_univPercent($targetPercent))
				$cnt1++;
			else
				$cnt0++;
		}
		dump($cnt0,$cnt1, (($cnt1/$iters*100)).'%');
	}
	
    # - ### ### ###
    #   NOTE:
	
	public static function Dice_50p_1k2( ):bool{ return self::Dice_univPercent(50); }
	public static function Dice_33p_1k3( ):bool{ return self::Dice_univPercent(33); }
	public static function Dice_25p_1k4( ):bool{ return self::Dice_univPercent(25); }
	public static function Dice_20p_1k5( ):bool{ return self::Dice_univPercent(20); }
	public static function Dice_10p_1k10():bool{ return self::Dice_univPercent(10); }
	public static function Dice_5p_1k20( ):bool{ return self::Dice_univPercent(5); }
	public static function Dice_4p_1k25( ):bool{ return self::Dice_univPercent(4); }
	public static function Dice_3p_1k33( ):bool{ return self::Dice_univPercent(3); }
	public static function Dice_2p_1k50( ):bool{ return self::Dice_univPercent(2); }
	public static function Dice_1p_1k100():bool{ return self::Dice_univPercent(1); }
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
