<?php

namespace LibMy;



# - ### ### ### ###
# - ###
/* # = # = # = # */

# Этот класс работает, но 80% планируемого функционала не дописано и не планируется.

/* # = # = # = # */
# - ###
# - ### ### ### ###



/** Автоматизация действий в браузере через playwright через дебаговый порт. (на бале бекенда !!!)
 */
class apiDolphinDev
{
    # - ### ### ###

    # https://anty.dolphin.ru.com/docs/basic-automation/
    # https://documenter.getpostman.com/view/15402503/Tzm8Fb5f#9673ef48-fca9-490d-b77f-da3797e9bea5

    # - ### ### ###

    # NOTE: Хронология
    #  - Поставить nodejs
    #  - npm init playwright@latest
    #  -

    # - ### ### ###

    public function __construct() {    }
    public function __destruct()  {    }

    # - ### ### ###

    public $OPT_ProfileId = ''; # Чекать в редактуре профиля, в начале
    public $OPT_ProfilePort = ''; #
    public $OPT_ProfilePoint = ''; #

    public $OPT_JS_FileName = 'mainTest.spec.js'; #
    public $OPT_JS_TestName = 'MyTest'; #

    # - ### ### ###

    # npx playwright nodeTest.js
    # npx playwright test nodeTest.js
    # npx playwright test mytest

    # npx playwright mytest nodeTest2

    # npx playwright test nodeTest2.spec.js   WORK

    # npx playwright test nodeTest2 mytest

    # npx playwright test nodeTest2.spec.js --list  WORK
    # npx playwright test -h  WORK

    # G:\Laravel\public\pupTest.js
    # node "G:\Laravel\public\pupTest.js"
    #
    # node node_modules/puppeteer-core  WORK
    #

    # pytest "C:\Users\123\playwrightTest.py"
    #

    # - ### ### ###

    public function setOpt_ProfileID($id){ $this->OPT_ProfileId = (string) $id; }
    public function setOpt_ProfilePort($port){ $this->OPT_ProfilePort = (string) $port; }
    public function setOpt_ProfilePoint($point){ $this->OPT_ProfilePoint = (string) $point; }

    # - ### ### ###
    #   NOTE: Работа с антиком ипрофилями.

    # WORK
    public function actionAnty_Profile_START()
    {
        $URL = "http://localhost:3001/v1.0/browser_profiles/{$this->OPT_ProfileId}/start?automation=1";

        $RES = RequestCURL::GET($URL);

        dump($RES);
        dd($RES['ANSWER_JSON']);

        /*
        if( isset($RES['ANSWER_JSON']) )
            if( $RES['ANSWER_JSON']['errorObject']['code'] === 'E_BROWSER_RUN_DUPLICATE' )
                return true;

        return [
            'CURL_CODE' => $RES['HTTP_CODE'],
            'ANSW_SUCC' => $RES['ANSWER_JSON']['success'],
            'ANSW_PORT' => $RES['ANSWER_JSON']['automation']['port'],
            'ANSW_POINT' => $RES['ANSWER_JSON']['automation']['wsEndpoint'],
        ];  # */
    }

    public function actionAnty_Profile_STOP()
    {
        $URL = "http://localhost:3001/v1.0/browser_profiles/{$this->OPT_ProfileId}/stop";

        $RES = RequestCURL::GET($URL);

        dump($RES);
        dd($RES['ANSWER_JSON']);
    }

    # - ### ### ###
    #   NOTE: Унив генерация файла тестов.

    public function ACTION_DOFAST($arrCodeLines=[])
    {
        $this->ACTION_GEN_FILE($arrCodeLines);
        return $this->ACTION_EXEC();
    }

    public function ACTION_GEN_FILE($arrCodeLines=[])
    {
        $ARR_STRINGS =
            [
            "// #### #### ####",
            "// #### BEGIN",
            "// #### #### ####",
            "",
            "import { test, browser, chromium } from 'C://Users/UserName/node_modules/@playwright/test';",
            "",
            "const port = {$this->OPT_ProfilePort};",
            "const wsEndpoint = '{$this->OPT_ProfilePoint}';",
            "",
            "// #### #### ####",
            "",
            "test('{$this->OPT_JS_TestName}', async (  ) => {",
            "",
            'const browser = await chromium.connectOverCDP(`ws://127.0.0.1:${port}${wsEndpoint}`);',
            "",
            "###_CODE_###", # NOTE: !!!
            "",
            "});",
            "",
            "// #### #### ####",
            "// #### END",
            "// #### #### ####",
        ];

        # CONCEPT^
        #  - Добавить трайкатч
        #  - Добавить станд формат вывода
        #  - Добавить метод для жсона
        #  -
        #  - ФОРМАТ<===>данные    все через консоль лог.
        #  -   JSON<===>{...}
        #  - Добавить в ALL поле с этим
        #  -  Если не нашли разделитель, то тип OTHER
        #  -
        #  -
        #  -



        foreach($arrCodeLines as &$line)
            $line = '    '.$line;

        $text = str_replace(
            '###_CODE_###',
            implode(PHP_EOL,$arrCodeLines),
            implode(PHP_EOL,$ARR_STRINGS)
        );

        file_put_contents($this->OPT_JS_FileName,$text);

        return $text;
    }

    public function ACTION_EXEC()
    {
        $ALL = [
            'COMM' => "npx playwright test {$this->OPT_JS_FileName}",
            'FILE_TEXT_FULL' => [ file_get_contents($this->OPT_JS_FileName) ],
            #'FILE_TEXT_CODE' => [  ],
            'RES_OUT_RAW' => [ ],
            'RES_OUT_MY' => [ ],
            'RES_CODE' => -999,
            'RES_TIME_MS' => -1,
            'EXEC_TIME_MS_MY' => -1,
        ];

        $TIMER = new TimerMy();
        exec(escapeshellcmd($ALL['COMM']), $ALL['RES_OUT_RAW'],$ALL['RES_CODE']);
        $ALL['EXEC_TIME_MS_MY'] = $TIMER->getTimeMs(false);


        $last = last($ALL['RES_OUT_RAW']); # 7 => "\e[32m  1 passed\e[39m\e[2m (594ms)\e[22m"

        #$t = explode('passed',$last)[1];
        #$t = explode('ms)',$t)[0];
        #$t = explode(' (',$t)[1];
        #$ALL['RES_TIME_MS'] = $t;


        $raw = $ALL['RES_OUT_RAW'];
        $rawCnt = count($raw);
        unset( $raw[0] , $raw[1] , $raw[2] );
        unset( $raw[$rawCnt-1] , $raw[$rawCnt-2] , $raw[$rawCnt-3] );

        $ALL['RES_OUT_MY'] = array_values($raw);

        return $ALL;
    }

    # NOTE: await = требует дождаться фулл выполнения. Будет строго сверху вниз.

    # - ### ### ###
    #   NOTE: Действия через тесты

    public function doAct_LogTest()
    {
        return $this->ACTION_DOFAST([
            "console.log('56478597342897239785345897438597435');",
            "console.log('65784768728937483647832469834258623');"
        ]);
    }


    public function doAct_DevTests()
    {
        $RES =  $this->ACTION_DOFAST([
            "const URL_G = 'https://google.com';",
            "const URL_E = 'https://example.com';",
            "",
            "const B_context = browser.contexts()[0];",
            "const B_context_pages = browser.contexts()[0].pages();",
            "",
            "const P_Curr = browser.contexts()[0].pages()[0];", # NOTE: Текущая открытая вкладка.
            "const P_FrameMain = P_Curr.mainFrame();", # NOTE: Текущая открытая вкладка.
            "",
            "",
            "function log(myVar){ console.log( myVar ); }",
            "function logAsJSON_Normal(myVar){ console.log( JSON.stringify(myVar) ); }",
            "function logAsJSON(myVar){ console.log( 'JSON<===>'+JSON.stringify(myVar) ); }",
            "function logAsBASE(myVar){ console.log( 'BASE<===>'+btoa(myVar) ); }",
            "",
            "",
            "",  #TODO:  Метод, который сгребет фулл инфу о странице и вернет в жсоне
            "",
            #"const context = await browser.newContext();",
            "",
            #"const function logAsJSON(let VAR){ console.log('JSON'+'<===>'+JSON.stringify(VAR) ); }",
            "",
            #"await P_Curr.goto(URL_G);", # WORK
            "await logAsJSON_Normal( await P_Curr.content() );", # WORK
            #"await page.content();", #  html full
            "",
            "",
            "",
            #"await page.setContent(html);",
            #"await page.reload();",
            #"await page.close();",
            #"page.context();",
            #"page.frames();",
            #"page.url();",
            #"page.viewportSize();",
            "",
            "",
            #"logAsText('greger');",
            #"await page.goto('https://google.com');",
            #"logAsJSON( B_context.newPage('') );",
            #"",
            #"logAsJSON( B_context.pages().length );",
            "", # browserContext.pages();
            "",
            #"const page = await browser.contexts()[0].newPage();",
            "",
            "",
            "",
            "",
            #"await page.goto('https://playwright.dev/');",
            "",
            #"const allPages = context.pages();",
            #"console.log(allPages);",
        ]);
        dump($RES);


        dd($RES['RES_OUT_MY']);
    }

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:
	
	# Из контроллера тестов
	public static function mainTestFunc_AnticAuto(  )
	{
		
		# - ### ### ### ###
		# - ### Антик-Авто
		/* # = # = # /
		$ANTY = new apiDolphinDev();
		$ANTY->setOpt_ProfileID('123123'); # Тестовый
		$ANTY->setOpt_ProfilePort('57301'); # Тестовый
		$ANTY->setOpt_ProfilePoint('/devtools/browser/b2e3993d-1f37-4d28-a796-123123'); # Тестовый
		
		#$ANTY->actionAnty_Profile_START(); # NOTE: Руками
		$ANTY->doAct_DevTests();
		
		dd($ANTY->doAct_LogTest());
		/* # = # = # */
		
		
	}

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
