<?php

namespace LibMy;



/**
 * Все про операции с числами сумм крипты.
 */ # ДатаВремя создания: Реализация-2020 / Пересборка-09.23
class NumbererCrypto
{
    # - ### ### ### ### ### ###
    
    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }
    
    # - ### ### ### ### ### ###
	
	private $currencyData = array(
		'BTC' => [
			'active' => true, # Включена ли эта валюта
			'price_source' => 'COINBASE', # Источник данных о курсе. Будет уходить в свитч
			'fractional_len' => 8, # Сколько нужно знаков после зяпятой
			'rounding_active' => true, # Включено ли округление последних цифр
			'round_begin_char_pos' => 5, # Начиная с какого числа надо округлять 5= 0.12345555
			'description' => 'Bitcoin',
			# при 32000  1$ это 0.00003200
		],
		'ETH' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Ethereum',
		],
		'LTC' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Litecoin',
		],
		'DOGE' => [
			'active' => false,  # - ###
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Dogecoin',
		],
		'DASH' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Dash',
		],
		'BCH' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'BitcoinCash',
		],
		'ZEC' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Zcash',
		],
		'XRP' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 6, # - ###
			'rounding_active' => true,
			'round_begin_char_pos' => 2, # - ###  0.090000 => 0.099999.  0.009999=36копеек=приКурсе0,43usd
			'description' => 'Ripple',
		],
		'TRX' => [
			'active' => false, # - ###
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 5,
			'description' => 'Tron',
		],
		'XLM' => [
			'active' => true,
			'price_source' => 'COINBASE',
			'fractional_len' => 8,
			'rounding_active' => true,
			'round_begin_char_pos' => 3, # - ###  можно и 2
			'description' => 'Stellar',
		], # TODO:  Криво округляет - 189.95555560=30д.   !!! Проверено => Это ТОЧНО косяк платежки.
	);
	
	# 0.00042222 BTC  49.09$
	# 0.07816666 ETH  49.08$
	# 0.44160000 LTC  48.41$  0.6
	# 0.46812222 DASH 49.51$  0.5
	# 0.15596666 BCH  48.92$
	# 0.72431111 ZEC  48.95$
	# 104.455344 XRP  49.04$   !!!!! 6 знаков
	# 294.19711110 XLM  48.93$
	
	# - ### ### ### ### ### ###
	
	
	
	# - ### ### ###
	#   NOTE:
	
	# IMPORTANT - Подсчет точного числа крипты.  Улетает В МЕРЧАНТ!!!
	#  Всегда вернет число с N знаками после точки, даже если там нули
	public static function convertTargetAmountOfCrypto($cryptoPrice, $targetAmount, $accuracy=8)
	{
		# NOTE: 8 знаков точно хватит.  Даже 6 хватит для битка.
		
		# 0.00018388 BTC = 0,0019 USD
		/*  цена = 200
			мне надо на 30
			это 30/200 = 0.15шт  */
		
		# NOTE: Предел точности деления PHP = 15 знаков после запятой = 0.065271013913043
		
		$amount = $targetAmount / $cryptoPrice;
		
		$amount = number_format($amount, $accuracy,'.','');
		
		# NOTE: !!! Недостающие нули в конце дописываются.  Например = 0.50000000  или 1.00000000
		
		# - #####
		
		return $amount;
		
	}
	
	
	
	/**
	 * Делает последние N цифр одиноковыми.  Нужно для удобства ввода суммы платежей.
	 * @param $amount - сумма крипты. ВСЕГДА дробная тк приходит из методы выше.
	 * @param $fractionalPartLen - ожидаемое число чисел после запятой.  должно совпасть
	 * @param $targetCharPos - символ с какой позиции берется за основу и копируется вправо до конца?
	 * @param string $delimiter - Дробный разделитель - исходный и будущий. Должен совпасть с входным числом.
	 * @return array
	 */
	# IMPORTANT - Это улетает в мерчант!!!  Поэтому все писать крайне подробно и очевидно.  Нужна отказоустойчивость.
	#  Напоминалка: Ошибка на 1 разряд стоит 20 долларов за каждую операцию.
	public static function prepareReadableCryptoAmount($amount, $fractionalPartLen, $targetCharPos , $delimiter='.')
	{
		$amountStr = (string)$amount;
		
		# - ###
		
		# Проверяю, что это дробное число
		if( ! strstr($amountStr, $delimiter) )
		{
			return array(
				'error' => true,
				'desc' => '1. В числе не было нужного разделителя.',
				'amount' => $amountStr,
				'delimiter' => $delimiter,
			);
		}
		
		# - ###
		
		$buf = explode($delimiter, $amountStr);
		
		$firstPart = $buf[0];
		$secondPart = $buf[1];
		
		# - ###
		
		$firstPartLen = strlen($firstPart);
		$secondPartLen = strlen($secondPart);
		
		# Совпадают ли точности чисел?
		if( $secondPartLen !== $fractionalPartLen || $firstPartLen === 0 )
		{
			return array(
				'error' => true,
				'desc' => '2. Неверно указано число знаков после запятой. Ожидаемое не совпало с фактическим.  Либо пустая левая часть',
				'secondPart' => $secondPart,
				'secondPartLen' => $secondPartLen,
				'charsCntSum' => $fractionalPartLen,
				'amount' => $amountStr,
				'delimiter' => $delimiter,
			);
		}
		
		# - ###
		
		$targetChar = $secondPart[$targetCharPos-1]; # -1 тк отсчет с 0
		
		#echo "<br>Целевой знак = $targetChar";
		#echo "<br>Было = $secondPart";
		
		for($i=$targetCharPos ; $i<$secondPartLen ; $i++)
		{
			#$charNum = $i+1;
			#echo "<br>Знак номер $charNum => $secondPart";
			
			$secondPart[$i] = $targetChar;
			
			#echo " => $secondPart";
		}
		
		#echo "<br>Стало = $secondPart";
		#dd(123);
		# - ###
		
		$amountFinal = $firstPart . $delimiter . $secondPart;
		
		
		# NOTE: Преобразовывал нормальное строковое число в   numeric 8.888E-5  и дальше шли проблемы.
		#$amountFinal = (float) $amountFinal;
		#dd($amountFinal);
		
		# - ###
		
		return array( 'error' => false, 'amount' => $amountFinal );
		
	}
	
	# - ### ### ###
	#   NOTE:
	
	
	
	# - ### ### ###
    #   NOTE:
	
	
	/** IMPORTANT FINAL TESTED
	 * Округление для денег - КРИПТА. С нулями, 8 знака.
	 * @param int|float|string $amount Обрабатываемое число.
	 * @return string
	 */
	public static function roundBasicCrypto($amount):string
	{
		return Numberer::roundFloat_Down($amount, 8,true);
		# return self::format($amount,8);
	}
	
	# - ### ### ###
    #   NOTE:
	
	/** FINAL TESTED
	 * Конвертировать из сатошей в обычную сумму крипты.  3456 сатош -> 0.00003456
	 * @param int|float|string $satoshi Количество сатош. 1BTC=100000000
	 * @return string
	 */
	public static function convertSatoshiToBasicCrypto( $satoshi ):string
	{
		$amount = $satoshi / 100000000;
		return self::roundBasicCrypto($amount);
	}
	
	# TODO:  convertBasicCryptoToSatoshi   наоборот
	
	/*
	CONCEPT: Не помню, из старой версии.  Возможно это про одинаковые последние цифры    Убрать в криптовое
	public static function readableCrypto($amount)
	{

	}
	*/
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ### ### ###
    #   NOTE:
	
	
	
    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######
	
} # End class
