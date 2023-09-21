<?php

namespace LibMy;


/**
 * Крайне важный класс для быстрой генерации простенького фронтенда.
 * Вмето писанины html+css просто вызвать 1 метод и готово.
 * Постоянно используется.
 */
class EasyFront
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:

    public static function echoTag_A($url,$blank=true,$text='URL')
    {
        if($text==='URL')$text=$url;
        if($blank) $blank='target="_blank"'; else $blank='';

        echo '<a href="'.$url.'"  '.$blank.'  >'.$text.'</a>';
    }
    public static function echoTag_A_Button($url,$blank=true,$text='URL')
    {
        if($text==='URL')$text=$url;
        if($blank) $blank='target="_blank"'; else $blank='';

        $text = '<button style="font-size: 16px; padding: 12px; border-radius: 12px; border: 2px solid #4D3E96; ">'
            .$text.'</button>';

        echo '<a href="'.$url.'"  '.$blank.'  >'.$text.'</a>';
    }
	
	# - ### ### ###
	#   NOTE:
	
	public static function echoTagForm_Input_Text( $name, $val, $len=30, $brCount=1, $hidden=false)
	{
		$ARR = [
			'<input type="text"',
			'name="'.$name.'"',
			'size="'.$len.'"',  # Дефолт 20
			'value="'.$val.'"',
		];
		if($hidden) $ARR []= 'hidden';
		$ARR []= '>';
		if($brCount) $ARR []= str_pad('',4*$brCount,'<br>');
		
		echo (implode(' ',$ARR));
	}
	
	public static function echoTagForm_Input_TextArea( $name, $val, $rows=3, $cols=50, $brCount=1, $hidden=false)
	{
		$ARR = [
			'<textarea',
			'name="'.$name.'"',
			'rows="'.$rows.'"',
			'cols="'.$cols.'"',
			'value="'.$val.'"',
		];
		if($hidden) $ARR []= 'hidden';
		$ARR []= '>'.$val.'</textarea>';
		
		
		
		if($brCount) $ARR []= str_pad('',4*$brCount,'<br>');
		
		echo (implode(' ',$ARR));
	}
	
	public static function echoTagForm_BtnSend($text='Отправить')
	{
		echo '<button type="submit" style="height: 40px; border-radius: 10px;">'.$text.'</button>';
	}
	
	
	# - ### ### ###
	#   NOTE:
 
	public static function echoTag_Button_Reload($text='Обновить',$fontSize='16px')
	{
		$styleDiv = "font-size: {$fontSize}; padding: 12px; border-radius: 12px; border: 2px solid #4D3E96; ";
		echo "<button onclick=\"window.location.reload();\" style=\"{$styleDiv}\" >{$text}</button>";
	}
	
	public static function echoTag_Button_Copy($content, $text='CONTENT',$fontSize='16px',$withDelete=false)
    {
        if($text==='CONTENT') $text=$content;

        Randomer::SetNewRandomSeed();
        $randomInt = random_int(10000, 99999);

        $idBtn = 'copy_btn_'.$randomInt;
        $idInp = 'copy_input_'.$randomInt;
        $idDiv = 'copyDiv'.$randomInt;

        if( $withDelete )
            $delCode = "{$idDiv}.remove();";
        else
            $delCode = '';

        echo "<div id=\"{$idDiv}\" style=\"display: inline;\">"; # Чтоб не было мешанины тегов


        $styleDiv = "font-size: {$fontSize}; padding: 12px; border-radius: 12px; border: 2px solid #4D3E96; ";

        # NOTE:1 Инпут нельзя скрывать, он обязателен. Поэтому просто ужимаю его до минимума.
        # NOTE:2 Недо чтоб был виден хотяб 1 симв для выделения.
        echo "<input style=\"width: 2ch\"  id=\"{$idInp}\"   value=\"{$content}\" >";

        echo "<button id=\"{$idBtn}\" style=\"{$styleDiv}\" >{$text}</button>";

        echo "<script>
                var {$idBtn} = document.getElementById(\"{$idBtn}\");
                var {$idInp} = document.getElementById(\"{$idInp}\");
                var {$idDiv} = document.getElementById(\"{$idDiv}\");
                {$idBtn}.onclick = function()  {  {$idInp}.select();   document.execCommand(\"copy\");   {$delCode}  };
             </script>";

        echo '</div>';
    }

    # Работает, но только фулл текст в кнопке.
    public static function echoTag_Button_Copy_2( $content,$fontSize='16px',$withDelete=false )
    {
        dd( __METHOD__,'Не юзать' );

        EasyFrontJS::echoScript_CopyToClip_ById_Native();


        Randomer::SetNewRandomSeed();
        $randomInt = random_int(10000, 99999);

        $idDiv = 'copyDiv'.$randomInt;
        $idBtn = 'copy_btn_'.$randomInt;

        if( $withDelete )
            $delCode = "document.getElementById(\"{$idDiv}\").remove();";
        else
            $delCode = '';

        $copyCode = 'copyToClipboard("'.$idBtn.'");';

        $styleDiv = "font-size: {$fontSize}; padding: 12px; border-radius: 12px; border: 2px solid #4D3E96; ";

        echo "<div    id=\"{$idDiv}\" style=\"display: inline;\">"; # Чтоб не было мешанины тегов
        echo "<button id=\"{$idBtn}\" style=\"{$styleDiv}\" onclick=' {$copyCode} {$delCode} '>{$content}</button>";
        echo '</div>';

        # - ###



        /*
        echo "<script>
                var {$idBtn} = document.getElementById(\"{$idBtn}\");
                var {$idInp} = document.getElementById(\"{$idInp}\");
                var {$idDiv} = document.getElementById(\"{$idDiv}\");
                {$idBtn}.onclick = function()  {  {$idInp}.select();   document.execCommand(\"copy\");   {$delCode}  };
             </script>";
        */
    }
	
	
	# - ### ### ###
	#   NOTE:
 
	public static function echoTag_IMG($URI, $height='', $width='', $class='EF-tag-img')
    {
        echo '<img  src="'.$URI.'"  alt="'.$URI.'"  height="'.$height.'"  width="'.$width.'"  class="'.$class.'" '.
            ' referrerpolicy="no-referrer"     />';
    }
    public static function echoTag_IMG_RawData_RawText($dataRawText,$fileType='jpeg', $height='', $width='', $class='EF-tag-img')
    {
        echo '<img  src="data:image/'.$fileType.';base64,'.base64_encode($dataRawText).'"  alt="base64 img"  height="'.$height.'"  width="'.$width.'"  class="'.$class.'" />';
    }
    public static function echoTag_IMG_RawData_base64 ($dataBase64 ,$fileType='jpeg', $height='', $width='', $class='EF-tag-img')
    {
        echo '<img  src="data:image/'.$fileType.';base64,'.$dataBase64.'"  alt="base64 img"  height="'.$height.'"  width="'.$width.'"  class="'.$class.'" />';
    }
	
	
	# - ### ### ###
	#   NOTE:
	
    public static function echoTag_SPAN_StyledForumNick_Random($text)
    {
        echo '<span style="'.EasyFrontCssColors::getNickNameStyle_Random().'">'.$text.'</span>';
    }


    # - ### ### ###
    #   NOTE: Стандартные теги

    public static function echoTag_BR($cnt){ echo str_repeat('<br>',$cnt); }
    public static function echoTag_BR_3(){ self::echoTag_BR(3); }
    public static function echoTag_HR_Red($count=1){ echo str_repeat('<hr color="red">',$count); }


    # - ### ### ###
    #   NOTE: ROW Мои

    public static function echoTag_HrROW_UNIV( $color , $height=30 , $margin=5 )
    {
        $st = implode(' ',[
            'display: block; border-radius: 30px;',
            "height: {$height}px; margin: {$margin}px 0;",
            $color,
        ]);
        echo '<div about="HR-My"><em style="'.$st.'" ></em></div>';
    }
    public static function echoTag_HrROW_GradRand( $height=30 , $margin=5 )
    {
        $c = EasyFrontCssColors::getGradient_Random();
        self::echoTag_HrROW_UNIV( $c , $height , $margin );
    }
    
    public static function echoTag_HrROW_PreDef_GradDEV( $height=30 , $margin=5 )
    {
        $c = 'background: linear-gradient(90deg, #0095dd 0%, #f1094b 100%,#0095dd);';
        self::echoTag_HrROW_UNIV( $c , $height , $margin );
    }
    public static function echoTag_HrROW_PreDef_VendorGoogle1( $height=30 , $margin=5 , $key='GOOGLE_1'){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorGoogle3( $height=30 , $margin=5 , $key='GOOGLE_3'){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorGoogle4( $height=30 , $margin=5 , $key='GOOGLE_4'){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorInstagram($height=30, $margin=5 , $key='INSTA'   ){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorMozilla( $height=30 , $margin=5 , $key='MOZILLA' ){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorImgur  ( $height=30 , $margin=5 , $key='IMGUR_1' ){ self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorMlpTiaD( $height=30 , $margin=5 , $key='MLP_TIA_D'){self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorMlpCadD( $height=30 , $margin=5 , $key='MLP_CAD_D'){self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    public static function echoTag_HrROW_PreDef_VendorMlpRD  ( $height=30 , $margin=5 , $key='MLP_RD'   ){self::echoTag_HrROW_UNIV( EasyFrontCssColors::getGradient_Vendor_ByName($key) , $height , $margin ); }
    # NOTE: Делать методы только для хороших
    
    
    # - ### ### ###
    #   NOTE:

	# !!! Добавить = https://linkanon.com/
	
    public static function echoBackground_Random()
    {
        echo '<style> html { '.EasyFrontCssColors::getGradient_Random().' }</style>';
    }
    
    public static function echoBackground_Vendor_ByName($key)
    {
        echo '<style> html { '.EasyFrontCssColors::getGradient_Vendor_ByName($key).' }</style>';
    }
    public static function echoBackground_Vendor_DEV()
    {
        echo '<style> html { '.EasyFrontCssColors::getGradient_Vendor_ByName('DEV_BG_MY').' }</style>';
    }

    # - ### ### ###
    #   NOTE:


    # - ### ### ###
    #   NOTE:

    public static function echoTag_TextArea($data='', $rows=10, $cols=40)
    {
        echo '<textarea style="font-size=large" wrap="soft" rows="'.$rows.'" cols="'.$cols.'" name="">'.$data.'</textarea>';
    }

    public static function echoTag_TextArea_Array($data='', $rows=10, $cols=40)
    {
        echo '<textarea style="font-size=large" wrap="soft" rows="' . $rows . '" cols="' . $cols . '" name="">' . print_r($data, true) . '</textarea>';
    }
    public static function echoTag_TextArea_JSON($data='', $rows=10, $cols=40)
    {
        echo '<textarea style="font-size=large" wrap="soft" rows="' . $rows . '" cols="' . $cols . '" name="">' . json_encode($data, JSON_PRETTY_PRINT) . '</textarea>';
    }


    public static function echoTag_Iframe_SRC($url='', $height=500, $width=800)
    {
	    echo '<iframe src="'.$url.'" width="'.$width.'px" height="'.$height.'px"
            scrolling="auto"
            style="border: solid 2px #2EC551;"></iframe>';
    }
    public static function echoTag_Iframe_HTML($html='', $height=500, $width=800)
    {
        # NOTE: Запрещены сторонние скрипты через allow
        # http://htmlbook.ru/html/iframe/sandbox

        # Чтоб в Ф12 было сжато а не длинная портянка
        $html = str_replace("\n",'',$html);

        # Нельзя совать тупо строковую переменную, тк при выводе все смешается и гг.
        $html = htmlspecialchars($html);

        echo '<iframe srcdoc="'.$html.'" width="'.$width.'px" height="'.$height.'px"
            scrolling="auto" sandbox="allow-same-origin"
            style="border: solid 2px #2EC551;"></iframe>';

    }


    # - ### ### ###
    #   NOTE:  Готовые модули

    public static function echoModule_ColorsPanel($colorsArr)
    {
        echo "<style> .colors{  padding: 15px;  margin-left: auto;   margin-right: auto;  } </style>";

        echo '<div style="display: -webkit-box;  height:80px;   border: 3px solid red;">';
            foreach($colorsArr as $one)
                echo "<div class='colors' style='background-color: $one '> $one </div>";
        echo '</div>';


    }




    # - ### ### ###
    #   NOTE: Поняхи
	
	
	public static function echoPones_131()
	{
		self::echoPones_GIF(1);
		self::echoPones_IMG(3);
		self::echoPones_GIF(1);
	}
    public static function echoPones_IMG($cnt)
    {
        $arrPones_Img = [
            'https://derpicdn.net/img/2014/8/18/702641/thumb.png',
            'https://derpicdn.net/img/2019/7/15/2092102/thumb.png',
            'https://derpicdn.net/img/2018/8/4/1798029/thumb.png',
            'https://derpicdn.net/img/2020/10/12/2465238/thumb.png',
            'https://derpicdn.net/img/2018/4/5/1700204/thumb.png',
            'https://derpicdn.net/img/2018/10/9/1851874/thumb.jpg',
            'https://derpicdn.net/img/2014/9/7/716893/thumb.png',
            'https://derpicdn.net/img/2015/6/2/909051/thumb.jpg',
            'https://derpicdn.net/img/2015/9/1/970331/thumb.jpg',
            'https://derpicdn.net/img/2017/3/11/1384692/thumb.png',
            'https://derpicdn.net/img/2019/10/13/2167624/thumb.jpg',
            'https://derpicdn.net/img/2018/8/8/1801513/thumb.png',
            'https://derpicdn.net/img/2016/3/21/1113977/thumb.jpg',
            'https://derpicdn.net/img/2020/12/21/2513219/thumb.png',
            'https://derpicdn.net/img/2017/12/9/1603675/thumb.png',
            'https://derpicdn.net/img/2020/9/14/2445369/thumb.jpg',
            'https://derpicdn.net/img/2019/9/22/2150503/thumb.png',
            'https://derpicdn.net/img/2019/12/24/2228439/thumb.jpg',
            'https://derpicdn.net/img/2018/3/11/1677444/thumb.png',
            'https://derpicdn.net/img/2018/7/10/1777807/thumb.png',
            'https://derpicdn.net/img/2014/7/31/687638/thumb.jpg',
            'https://derpicdn.net/img/2019/2/7/1955980/thumb.png',
            'https://derpicdn.net/img/2018/2/4/1648016/thumb.jpg',
            'https://derpicdn.net/img/2016/2/28/1098664/thumb.png',
            'https://derpicdn.net/img/2021/2/28/2561187/thumb.png',
            'https://derpicdn.net/img/2016/1/11/1064150/thumb.png',
            'https://derpicdn.net/img/2012/6/17/4876/thumb.png',
            'https://derpicdn.net/img/2016/2/7/1082311/thumb.png',
            'https://derpicdn.net/img/2019/8/27/2129474/thumb.jpg',
            'https://derpicdn.net/img/2020/2/5/2266546/thumb.png',
            'https://derpicdn.net/img/2021/12/27/2773027/thumb.jpg',
            'https://derpicdn.net/img/2017/12/24/1615021/thumb.png',
            'https://derpicdn.net/img/2016/2/13/1086735/thumb.png',
            'https://derpicdn.net/img/2020/6/10/2371280/thumb.png',
            'https://derpicdn.net/img/2016/11/28/1305296/thumb.png',
            'https://derpicdn.net/img/2015/8/18/960291/thumb.png',
            'https://derpicdn.net/img/2015/12/11/1041920/thumb.png',
            'https://derpicdn.net/img/2019/5/8/2034160/thumb.png',
            'https://derpicdn.net/img/2016/12/31/1329265/thumb.png',
            'https://derpicdn.net/img/2018/12/9/1903696/thumb.png',
            'https://derpicdn.net/img/2019/2/8/1956876/thumb.png',
            'https://derpicdn.net/img/2016/12/2/1308109/thumb.png',
            'https://derpicdn.net/img/2018/7/17/1783229/thumb.png',
            #'',
            # Нач с = https://derpibooru.org/search?page=32&sd=desc&sf=score&q=safe
        ];

        foreach( range(1,$cnt) as $i )
            self::echoTag_IMG($arrPones_Img[ array_rand($arrPones_Img) ]);
    }
    public static function echoPones_GIF($cnt)
    {
        $arrPones_Gif = [
            'https://derpicdn.net/img/2013/1/14/212399/thumb.gif',
            'https://derpicdn.net/img/2018/12/10/1904517/thumb.gif',
            'https://derpicdn.net/img/2017/1/22/1345477/thumb.gif',
            'https://derpicdn.net/img/2013/2/3/232093/thumb.gif',
            'https://derpicdn.net/img/2015/10/11/999920/thumb.gif',
            'https://derpicdn.net/img/2015/3/29/859520/thumb.gif',
            'https://derpicdn.net/img/2017/8/7/1505262/thumb.gif',
            'https://derpicdn.net/img/2013/4/13/296454/thumb.gif',
            'https://derpicdn.net/img/2017/12/13/1607225/thumb.gif',
            'https://derpicdn.net/img/2015/4/6/866777/thumb.gif',
            'https://derpicdn.net/img/2017/3/6/1381099/thumb.gif',
            'https://derpicdn.net/img/2017/9/15/1536036/thumb.gif',
            'https://derpicdn.net/img/2019/7/10/2088367/thumb.gif',
            'https://derpicdn.net/img/2018/7/5/1773434/thumb.gif',
            'https://derpicdn.net/img/2017/10/23/1568584/thumb.gif',
            'https://derpicdn.net/img/2019/10/1/2157827/thumb.gif',
            'https://derpicdn.net/img/2019/7/4/2083032/thumb.gif',
            'https://derpicdn.net/img/2016/8/25/1233214/thumb.gif',
            'https://derpicdn.net/img/2018/7/6/1774450/thumb.gif',
            'https://derpicdn.net/img/2019/10/28/2181528/thumb.gif',
            'https://derpicdn.net/img/2018/6/3/1748336/thumb.gif',
            'https://derpicdn.net/img/2019/8/26/2127912/thumb.gif',
            'https://derpicdn.net/img/2018/7/21/1786032/thumb.gif',
            'https://derpicdn.net/img/2017/2/11/1359868/thumb.gif',
            'https://derpicdn.net/img/2016/9/19/1253168/thumb.gif',
            'https://derpicdn.net/img/2012/6/20/12822/thumb.gif',
            'https://derpicdn.net/img/2017/7/28/1496902/thumb.gif',
            'https://derpicdn.net/img/2017/10/20/1565991/thumb.gif',
            'https://derpicdn.net/img/2017/2/5/1356075/thumb.gif',
            'https://derpicdn.net/img/2019/11/17/2198750/thumb.gif',
            'https://derpicdn.net/img/2019/2/5/1954210/thumb.gif',
            'https://derpicdn.net/img/2014/6/9/649293/thumb.gif',
            'https://derpicdn.net/img/2017/4/15/1411860/thumb.gif',
            'https://derpicdn.net/img/2017/3/27/1397818/thumb.gif',
            'https://derpicdn.net/img/2012/11/26/163882/thumb.gif',
            'https://derpicdn.net/img/2020/11/20/2491994/thumb.gif',
            'https://derpicdn.net/img/2016/10/8/1267744/thumb.gif',
            'https://derpicdn.net/img/2020/3/2/2288071/thumb.gif',
            'https://derpicdn.net/img/2013/10/23/455075/thumb.gif',
            'https://derpicdn.net/img/2022/4/10/2843334/thumb.gif',
            'https://derpicdn.net/img/2019/4/21/2018681/thumb.gif',
            'https://derpicdn.net/img/2017/4/22/1418388/thumb.gif',
            'https://derpicdn.net/img/2022/3/26/2832853/thumb.gif',
            'https://derpicdn.net/img/2017/6/19/1465414/thumb.gif',
            'https://derpicdn.net/img/2018/1/14/1631633/thumb.gif',
            'https://derpicdn.net/img/2015/4/5/865337/thumb.gif',
            'https://derpicdn.net/img/2020/1/22/2253672/thumb.gif',
            'https://derpicdn.net/img/2019/4/21/2018723/thumb.gif',
            'https://derpicdn.net/img/2019/2/18/1965665/thumb.gif',
            'https://derpicdn.net/img/2018/6/2/1747992/thumb.gif',
            'https://derpicdn.net/img/2012/12/24/192497/thumb.gif',
            #'',
            # Нач с = https://derpibooru.org/search?page=22&sd=desc&sf=score&q=safe%2Cgif
        ];

        foreach( range(1,$cnt) as $i )
            self::echoTag_IMG($arrPones_Gif[ array_rand($arrPones_Gif) ]);
    }


    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
