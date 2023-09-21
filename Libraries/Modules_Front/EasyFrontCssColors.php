<?php

namespace LibMy;



/**
 * Класс для хранения и выдачи всего, что связано с цветами и градиентами.
 */
class EasyFrontCssColors
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###


    public static function generateColorHex_Random():string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
    public static function generateGradient_Random($colorsCnt=2,$rotate=true)
    {
        $ColorsArr = [];
        $GRAD = 'background: linear-gradient( ';

        if($rotate)
            $GRAD .= str_pad(random_int(0,360),3,' ',STR_PAD_LEFT).'deg , ';

        foreach(range(1,$colorsCnt) as $i)
        {
            $c = self::generateColorHex_Random();
            $ColorsArr []= $c;

            $GRAD .= $c.' ';
            if( $i !== $colorsCnt ) $GRAD .= ', ';
        }

        $GRAD .= ');';

        return [$GRAD,$ColorsArr];
    }



    # - ### ### ###

    public static function getGradient_ALL()
    {
        return [ # Рандомные фоны страницы, надоело белое полотно.
	            'background: linear-gradient(45deg,#fff25c,#f39ed9,#868adc);',
	            'background: linear-gradient(90deg,#bc69dc,#5d2df4);',
	            'background: linear-gradient(90deg,#fa7ed1,#8886fc);',
	            'background: linear-gradient(90deg, #0095dd 0%, #f1094b 100%,#0095dd);',
	            'background: linear-gradient(to left, #743ad5, #d53a9d);',
	            'background: linear-gradient(90deg, #0078d4, #c218ec);',
	            'background: linear-gradient(to right,#ff483a 0,#3b29de 100%);',
	            'background: linear-gradient(90deg,  #2551a4,#ae44be 51%,  #ef2d1d);',
                'background: linear-gradient(90deg, #0095dd 0%, #f1094b 100%, #0095dd);',
                'background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);',
                'background: linear-gradient(to right, #94FAFF, #BDFA8C, #FFDB78, #FFC8DC, #FFC8DC, #FFDB78, #BDFA8C, #94FAFF);',
                'background: linear-gradient(116.7deg, rgb(255, 241, 165), rgb(254, 255, 186), rgb(225, 255, 160));',
                'background: linear-gradient(116deg,#2f8fff,#d927ff 97%);',
                #'background: ',
                #'',
        ];
        
        
        #'background-blend-mode: screen; background: linear-gradient(limegreen, transparent), linear-gradient(90deg, skyblue, transparent), linear-gradient(-90deg, coral, transparent);',
		#'background: linear-gradient(91.28deg,#9816bd .37%,#7d1ed7 39.53%,#5e47df 85.33%,#0345ca 142.22%),linear-gradient(96deg,#6116ec 6.8%,#6557e0 39.4%,#44a1be 70.16%,#0cd5a7 103.43%);',
        /*  Тот красный ромбами
        background-color: #f3a183; background-image: linear-gradient(transparent 0px, transparent 49px, rgba(255,255,255, 0.2) 50px, transparent 51px, transparent 99px, rgba(255,255,255, 0.2) 100px),
	                linear-gradient(120deg, transparent 0, transparent 48px, rgba(255,255,255, 0.2) 49px, transparent 50px, transparent 98px, rgba(255,255,255, 0.2) 99px, transparent 100px),
	                linear-gradient( 60deg, transparent 0, transparent 48px, rgba(255,255,255, 0.2) 49px, transparent 50px, transparent 98px, rgba(255,255,255, 0.2) 99px, transparent 100px),
	                linear-gradient( 90deg, #f3a183, #eC6f66); background-size: 100px 100px, 115px 100px, 115px 100px, auto;
        */
    }
    public static function getGradient_Random(){  $ALL = self::getGradient_ALL();  return $ALL[array_rand($ALL)];  }

    
    # NOTE: Оч годный - http://www.brandgradients.com/mozilla-colors/
    public static function getGradient_Vendor_ALL()
    {
        return [
        	# Дефолт направление - сверху вниз.
            'DEV_BG_MY'=> 'background: linear-gradient(90deg,#bc69dc,#5d2df4);',
            'CANVA'    => 'background: radial-gradient(85.02% 325.07% at 9.63% 9.16%,#00c4cc 0,rgba(0,196,204,0) 100%),radial-gradient(58.42% 145.14% at 2.39% 66.6%,#00c4cc 25%,rgba(0,196,204,0) 100%),radial-gradient(21.01% 67.35% at 51.22% 110.85%,#6420ff 0,rgba(100,32,255,0) 100%),radial-gradient(29.43% 73.9% at 42.69% 99.93%,#6420ff 0,rgba(100,32,255,0) 100%),radial-gradient(36.41% 77.61% at 5.6% 117.38%,#6420ff 0,rgba(100,32,255,0) 100%),linear-gradient(274.37deg,#7d2ae7 .86%,#7d2ae7 63.84%,rgba(125,42,231,0) 82.56%);',
            'IMGUR_1'  => 'background: linear-gradient(165deg, rgb(105, 216, 202) 0%, rgb(53, 146, 255) 50%, rgb(156, 49, 255) 100%);',
            'TIKTOK'   => 'background: linear-gradient(90deg, #25f4ee, #fe2c55)',
            'INSTA'    => 'background: linear-gradient(88.84deg,#ffd600 .21%,#ff7a00 16.66%,#ff0069 36.35%,#d300c5 54.42%,#7638fa 71.99%);',
            'GOOGLE_1' => 'background: linear-gradient(90deg, rgb(66 133 244), rgb(234 67 53), rgb(251 188 5), rgb(52 168 83));',
            'GOOGLE_2' => 'background: linear-gradient(90deg, rgb(66 133 244), rgb(234 67 53), rgb(251 188 5), rgb(52 168 83), rgb(234 67 53));',
            'GOOGLE_3' => 'background: linear-gradient(-120deg, #4285f4, #34a853, #fbbc05, #ea4335);',
            'GOOGLE_4' => 'background: linear-gradient(-120deg, #ea4335, #fbbc05, #34a853, #4285f4);',
            'MOZILLA'  => 'background: linear-gradient(-120deg, #0B529D, #1EB2E9, #FFEC4A, #F72336);',
            'YANDEX_AD_V1' => 'background: linear-gradient(180deg,#562aac 0.01%,#e13533 99.98%);',
            'YANDEX_AD_V2' => 'background: linear-gradient( 90.02deg, #5243ad 0%, #bc1579 55.25%, #dd3832 100% );',
            'MLP_TIA_D' => 'background: linear-gradient(-120deg,#44B1CE,#50CDA5,#80A4EE,#E599F2);',
            'MLP_TIA_L' => 'background: linear-gradient(-120deg,#8CDEE4,#CBF5C0,#AEDEFC,#F2C4FD);',
            'MLP_CMC' => 'background: linear-gradient(-120deg,#F5415F,#F6B8D2,#BF5D93);',
            'MLP_TWI' => 'background: linear-gradient(-120deg,#243870,#652D87,#EA428B,#243870);',
            'MLP_CAD_D' => 'background: linear-gradient(-120deg,#732DA2,#D03092,#F4E9A8);',
            'MLP_RAR' => 'background: linear-gradient(-120deg,#794897,#4A1767,#5E50A0);',
            'MLP_RD' => 'background: linear-gradient(-120deg,#EC4141,#EF7135,#FAF5AB,#5FBB4E,#1B98D1,#632E86);',
            #'' => '',
            #'' => 'background: linear-gradient(-120deg,,,,);',
        ];
    }
	public static function getGradient_Vendor_Random(    ){ $ALL = self::getGradient_Vendor_ALL();  return $ALL[array_rand($ALL)];  }
	public static function getGradient_Vendor_ByName($key){ return self::getGradient_Vendor_ALL()[$key];  }




    public static function getNickNameStyle_ALL()
    {
        return [
            'color:white;text-shadow: 0px 2px 1px #86C3CF, 0px -1px 1px #86C3CF, 2px 0px 1px #86C3CF,0px 0px 5px rgb(152, 174, 221),0px 0px 5px rgb(154, 196, 168),0px 0px 5px rgb(185, 152, 228), 1px 2px 0px #9797C8, 2px 3px 0px #9797C8,  0px 3px 5px #ACCAD9,  0px -3px 5px #82A8B3,  3px 0px 5px #A8D2E6,  -3px 0px 5px #87BBA5; -webkit-background-clip:text',
            'background:linear-gradient(90deg, #0095dd 0%, #f1094b 100%, #0095dd);-webkit-background-clip:text;-webkit-text-fill-color:transparent',
            'background:linear-gradient(35deg, #006eff, #00ff81 52%, #fff 50%, #93cbff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;text-shadow:0 0 7px #00ffcf80',
            'color: #ff00ff;text-shadow: 0 0 5px #0008ff, 0 0 5px #00b8ff, 0 0 5px #00ffff, 0 0 5px #00f3ff, 0 0 5px #00ff95, 0 0 5px #ba14ff, 0 0 5px #ff14d7, 0 0 5px #ff1493, 1px 2px 0 #0008ff, 1px 3px 0 #00e8ffb8, 0 -1px 5px rgb(0 196 255 / 49%), 0 -3px 5px rgb(0 55 255 / 50%), 0 -4px 5px rgb(239 0 255), 0 -5px 5px rgb(169 0 255 / 50%), 0 1px 5px rgb(0 208 255 / 49%), 0 3px 5px rgb(0 114 255 / 50%), 0 4px 5px rgb(239 0 255), 0 5px 5px rgb(169 0 255 / 50%)',
            'Color: #ffff03;text-shadow: 0 0 5px #FE5149, 0 0 5px #9D4841, 0 0 5px #ffff03, 0 0 5px #FF221D, 0 0 5px #ffff03, 0 0 4px #ffff03, 0 0 5px #ffff03, 0 0 5px #ffff03',
            'color: black;text-shadow: 0px 0px 3px rgb(214,210,210), 0px 0px 4px rgb(214,210,210), 0px 0px 5px rgb(214,210,210), 0px 0px 5px rgb(214,210,210), 0px 0px 5px rgb(214,210,210), 0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210),  0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210),  0px 0px 5px rgb(214,210,210),0px 0px 5px rgb(214,210,210);border-radius: 5px',
            'text-shadow:  0 0 5px #01fdff;    color: #01fdff',
            'color: #FFFFFF;text-shadow: 0px 2px 4px #e844d4, 0 -2px 1px #ff00d5, 0px -5px 5px #ff00e3, 0px -5px 5px #ff00d0, 0px 1px 1px #ff00c3, 0px 0px 5px #ff00ea, 0px 0px 5px #ff00ea',
            'color: #d2f4f2;text-shadow: -5px 0px 5px rgb(250 121 198/10%),-5px -5px 5px rgb(250 121 198/10%),-5px -5px 5px rgb(250 121 198/10%),-2px -5px 5px rgb(250 121 198/5%),5px 3px 5px rgb(62 103 249/25%),5px 5px 5px rgb(62 103 249/25%),-1px 5px 5px rgb(62 103 249/20%),5px 5px 5px rgb(62 103 249/20%),1px 1px 1px#e887db,-3px -3px 5px#f77944,-1px -3px 5px #f87c69,-3px -1px 5px#f97e9f,0px 0px 5px #fa79c7,1px 0px 5px#fb73ec,5px 1px 5px#a137fb, -2px 2px 5px#ce56f4,4px 3px 5px#7050fa,4px 5px 5px#f7c5',
            'color: #fff;text-shadow: 0 0 5px #8b00ff, 0 0 5px #8b00ff, 0 0 5px #8b00ff, 0 0 5px #8b00ff, 0 0 5px #3e00ff, 0 0 4px #3e00ff, 0 0 5px #3e00ff, 0 0 5px #3e00ff, 0 1px 0 #ccc,  0 2px 0 #FF1493,               0 3px 0 #bbb,               0 4px 0 #b9b9b9,               0 5px 5px rgba(0,0,0,.15)',
            'color: #000000;text-shadow:              0px 0px 3px rgb(255,215,0), 0px 0px 4px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),  0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),  0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0);border-radius: 5px',
            'color: #ffdcb8;text-shadow: 1px 0px 0px #ffab92, -1px 0px 0px #ffab92, 1px 0px 0px #ff4a4a, -1px 0px 0px #ff4a4a, 0 1px 0px #ff4a4a, 0px -1px 0px #ff4a4a, -1px -1px 0px #ff4a4a, 1px -1px 0px #ff4a4a, -1px 1px 0px #ff4a4a, 1px 1px 0px #ff4a4a, 0px 1px 5px #ff4a4a, 1px 1px 3px #ff4a4a, 1px 1px 5px #ff3636, 1px 1px 5px #ff0000, 1px 1px 5px #ff0000, 1px 1px 5px #ff0000, 1px 1px 5px #ff0000, 0px -2px 0px #ff0000, -1px -2px 0px #ffe900, 1px -2px 0px #ffe900',
            'text-shadow: 0px 0px 4px #FF1493; background:linear-gradient(90deg, #00FFFF, #00FFFF 60%, #FF0000 50%, #FF0000); color:transparent;  -webkit-background-clip:text',
            'background: radial-gradient(circle at 20% 135%, #fdf497 0%, #fff372 9%, #fd8b49 24%,#c438ac 39%,#4069c8 90%),radial-gradient(circle at 20% 135%,#fdf497 0%,#fff372 14%, #fd5949 30%,#c438ac 39%,#4069c8 90%),radial-gradient(circle at 20% 135%,#fdf497 0%,#fff372 14%, #fd9b49 30%,#c438ac 39%,#3c77ff 90%),radial-gradient(circle at 20% 135%, #fdf497 0%, #fff372 14%, #fd5949 30%,#c438ac 39%,#4069c8 90%);text-shadow: 0px 0px 5px #ce00ff40;-webkit-background-clip: text;-webkit-text-fill-color: #cb459d00',
            'background: linear-gradient(0deg, #e34e9be6, #ff0048e6, #ff00cb);-webkit-background-clip: text;text-shadow: -1px 2px 5px #eb00ffe6, 0px -2px 5px #eb00ffe6, 0px 2px 5px #eb00ffe6, 0px -2px 5px #eb00ffe6;-webkit-text-fill-color: #ffffff1a;color: #fff',
            'background:#C6DEE3 100%;color:transparent;text-shadow: 1px 1px 5px black, 0px 0px 5px black;-webkit-background-clip:text',
            'background: linear-gradient(90deg, #ffcb00 0%, rgb(255, 0, 220) 100%, #0de14b);-webkit-background-clip: text;-webkit-text-fill-color: transparent;color: #000000;text-shadow: 0 0 5px #ff5400c9',
            'background: linear-gradient(11deg, #ff00eb 0%, #ff00eb 38%,transparent 24%, transparent 43%,#ff00eb 33%, #00def9 62%,transparent 65%, transparent 68%, #00def9 70%, #127024 100%);	-webkit-background-clip: text;	-webkit-text-fill-color: transparent;text-shadow: -5px 2px 5px rgba(165, 16, 219, 0.54), 4px -2px 5px rgba(6, 102, 251, 0.57), 0px 0px 5px rgba(129, 6, 251, 0.82)',
            'color: #7878f5;text-shadow: -1px -1px 5px #8461eb, 1px 1px 5px #5946e8',
            'border-radius:5px;text-shadow:  0px 0px 3px rgb(201, 247, 23), 0px 0px 4px rgb(201, 247, 23), 0px 0px 5px rgb(201, 247, 23), 0px 0px 5px rgb(201, 247, 23), 0px 0px 5px rgb(201, 247, 23), 0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23),  0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23),  0px 0px 5px rgb(201, 247, 23),0px 0px 5px rgb(201, 247, 23); color:black',
            'background: linear-gradient(100.4deg, #FF5EBE -23.67%, #FF3CB0 22.1%, #FF5EBE 43.66%, #FF84CD 67.29%, #FF5FAF 87.07%, #FF2E86 104.69%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;text-shadow:         0px 3px 5px rgba(255, 76, 159, 0.29), 1px 1px 5px rgba(255, 131, 197, 0.29), -4px -3px 5px rgba(255, 30, 164, 0.31);-webkit-background-clip:text',
            'color: #fff;text-shadow:    0 0 5px #c5005b, 0 0 5px #c5005b, 0 0 5px #c5005b, 0 0 5px #c5005b, 0 0 5px #c5005b, 0 0 4px #c5005b, 0 0 5px #c5005b, 0 0 5px #c5005b',
            'color: #ff6;text-shadow:              0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 4px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500',
            'background: radial-gradient(#e00000, #c524c6, #d40000, #ff0101, #ff07f5, #f46e03, #b42c2c, #de21f3, #f36121, #983636, #b43fb5, #b5843f, #6c0c0c, #d21ad3, #b7a53a, #d30000);    -webkit-background-clip: text;    -webkit-text-fill-color: #17ffbed1;text-shadow: 0px -1px 2px #9100ff, 0px -2px 2px #108cfe85, 0px -3px 2px #108cfe85, 0px 1px 2px #108cfe85, 0px 3px 2px #108cfe85, 0px 4px 2px #108cfe85, 0px -5px 2px #002bff85, 0px -3px 2px #002bff85, 0px -4px 2px #002bff85, 0px -2px 2px #002bff85',
            'color: #ff6;text-shadow: 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500, 0 0 4px #FF4500, 0 0 5px #FF4500, 0 0 5px #FF4500',
            'color: rgb(255, 255, 255);text-shadow: rgb(234, 234, 234) 0px 1px 5px, rgb(234, 234, 234) 0px 0px 5px, rgb(228, 228, 228) 0px 0px 2px, rgb(241, 241, 241) 1px 1px 0px, rgb(202, 202, 202) 0px 0px 5px, rgb(171, 171, 171) 0px 0px 1px, rgb(249, 249, 249) 1px 1px 1px, rgba(255, 255, 255, 0.44) 2px 3px 1px',
            'color: #fff;text-shadow: 5px 5px 0px #8b00ff, 5px -3px 3px #8b00ff, 3px -2px 1px #8b00ff, -1px -3px 0px #8b00ff, 0 1px 0px #8b00ff, 0px -1px 0px #3e00ff, -1px -1px 0px #3e00ff, 1px -1px 0px #3e00ff, -1px 1px 1px #3e00ff, 3px 3px 0px #3e00ff, 0px 1px 5px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 5px #3e00ff, 1px 1px 5px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 5px #3e00ff, 0px -2px 0px #3e00ff, -1px -2px 0px #3e00ff, 1px -2px 0px #3e00ff',
            'color: #fff;text-shadow:    5px 5px 0px #8b00ff, 5px -3px 3px #8b00ff, 3px -2px 1px #8b00ff, -1px -3px 0px #8b00ff, 0 1px 0px #8b00ff, 0px -1px 0px #3e00ff, -1px -1px 0px #3e00ff, 1px -1px 0px #3e00ff, -1px 1px 1px #3e00ff, 3px 3px 0px #3e00ff, 0px 1px 5px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 5px #3e00ff, 1px 1px 5px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 3px #3e00ff, 1px 1px 5px #3e00ff, 0px -2px 0px #3e00ff, -1px -2px 0px #3e00ff, 1px -2px 0px #3e00ff',
            'color: #fff;text-shadow:     0 0 5px #2693ff, 0 0 5px #2693ff, 0 0 5px #2693ff, 0 0 5px #2693ff, 0 0 5px #FF1493, 0 0 4px #FF1493, 0 0 5px #FF1493, 0 0 5px #FF1493, 0 1px 0 #ccc,  0 2px 0 #c9c9c9,               0 3px 0 #bbb,               0 4px 0 #b9b9b9,               0 5px 5px rgba(0,0,0,.15)',
            'color: #000000;text-shadow: 2px 2px 0px #ff00ff, 0 0 2px #00ffff, 0 0 3px #8A2BE2, 0 0 4px #8A2BE2, 0 0 5px #8A2BE2, 0 0 5px #d800ff, 0 0 5px #ff00ff, 0 0 5px #cc00ff, 0 0 5px #00a1ff, 0 0 5px #0000ff',
            'color: #FCFCFC;text-shadow: 0px 0.5px 1px rgba(252, 252, 252, 0.6), 0px 2px 3px rgba(0, 0, 0, 0.8), 0px 4px 4px rgba(0, 0, 0, 0.8), 0px 0px 5px rgba(252, 252, 252, 0.8), 0px 1px 5px rgba(252, 252, 252, 0.8), 0px 2px 5px rgba(252, 252, 252, 0.6), 0px 3px 5px rgba(252, 252, 252, 0.4), 0px 4px 5px rgba(252, 252, 252, 0.4)',
            'background: linear-gradient(90deg, #B6BDF8 0%, #DED5F3 100%);    -webkit-background-clip: text;    -webkit-text-fill-color: transparent;text-shadow: 0 0 1px #ffffff, 0 0 2px #dbddff, 0 0 5px #b2b5ff, 0 0 5px #af83ff63, 0 2px 5px #bba5ffb0, 0px 2px 0px #a070ff, 0px 4px 0px #e1d2ff, 0 -2px 5px #bba5ff73',
            'background: linear-gradient(to bottom right, #8b00ff 38%, #3e00ff 40% , #FF1493 80% );    -webkit-background-clip: text;    -webkit-text-fill-color: transparent;    color: #FFF;text-shadow: 0 0 5px #8b00ff',
            'background: radial-gradient(circle, #ff8001 10%, #ff0b56 63%, #d50007 97%);text-shadow: 0 0 5px #ff0052;-webkit-background-clip: text;-webkit-text-fill-color: transparent;-webkit-background-clip:text',
            'color: #08e8de;text-shadow:             2px 2px 0px #181657, 0 1px #00ffff, 0 0 1px #0095ff, 0 0 5px #0000ff, 0 0 5px #8500ff, 0 0 5px #808080, 0 0 1px #2b297b, 0 1px #2c0636, 0 0 1px #00a1ff, 0 0 0px #160c2c',
            'color: #000000;text-shadow: 0px 0px 3px rgb(255,215,0), 0px 0px 4px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0), 0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),  0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0),  0px 0px 5px rgb(255,215,0),0px 0px 5px rgb(255,215,0);border-radius: 5px',
            'color: #fff;text-shadow: -5px 0px 5px rgb(250 121 198/10%),-5px -5px 5px rgb(250 121 198/8%),-5px -5px 5px rgb(250 121 198/5%),-2px -5px 5px rgb(250 121 198/5%),5px 3px 5px rgb(62 103 249/25%),5px 5px 5px rgb(62 103 249/25%),-1px 5px 5px rgb(62 103 249/20%),5px 5px 5px rgb(62 103 249/10%),1px 1px 1px#af99ac,-3px -3px 5px#f77933,-1px -3px 5px #f87c69,-3px -1px 5px#f97e9f,0px 0px 5px #fa79c6,1px 0px 5px#fb73ec,3px 1px 5px#a139fb, -2px 2px 5px#ce56f4,4px 3px 5px#7050fa,4px 4px 5px#3e67f9',
            'background:radial-gradient(50% 43% at 50% 56%,#ffffff2e 70%,#431d5a2b 71%),linear-gradient(180deg, #FFFFFF54 58%,#fff0 59%),radial-gradient(40% 76% at 82% 39%,#FF5CBE 46%,#cf64ff00 46%),radial-gradient(53% 65% at 77% 29%,#CD64FF 94%,#875CFF 95%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;text-shadow: 0px 4px 3px #411c3275,0px 3px 5px #dc8fff33, 0px -4px 5px #7A4AFF, 0px -4px 5px #fff3, 0px 2px 5px #7C59E2;-webkit-background-clip:text',
            'background: linear-gradient(to right,  #5aa848 0%,#00fce7 34%,#5ba9ee 58%,#fa19fa 100%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;text-shadow:    0 0 5px #00ffcf80;-webkit-background-clip:text',
            'background: linear-gradient(70.8deg,#1c137f 15.37%,#79095d 72.17%)',
            #'',
        ];
    }
    public static function getNickNameStyle_Random(){  $ALL = self::getNickNameStyle_ALL();  return $ALL[array_rand($ALL)];  }


    public static function getColorsHex_My_ALL()
    {
        return [
            '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107',
            'rgb(255 125 0)', '#fd5c63', '#00d8ff', '#ea1d5d','#0f99d6','#ff3300','#ff4274',
			'#10d54c','#DA627D',
            
            '#2B2B2B','#64F200', # Для DUMP  черный и зеленый
        ];
    }
    public static function getColorHex_Random(){  $ALL = self::getColorsHex_My_ALL();  return $ALL[array_rand($ALL)];  }



    /*
    public static function getNickNameStyle11_ALL()
        {
            return [
                '',
            ];
        }
    public static function getNickNameStyle11_Random(){  $ALL = self::getNickNameStyle_ALL();  return $ALL[array_rand($ALL)];  }
    # */

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
