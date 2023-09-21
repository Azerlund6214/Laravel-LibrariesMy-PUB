<?php

namespace LibMy;

# Из Laravel
use Illuminate\Support\Facades\View;

# Хелперы
use LibMy\Requester;


/* NOTE: Места, где есть вызовы получения страниц.
    .
    .
    .
*/

/** - Универсальная открывашка любого нужного VIEW. С удобной передачей всех нужных параметров в неё.
 * - Задача модуля - ТУПО вывести нужную страницу. + Засунуть туда удобный массив с переменными.
 * - Никаких обработок к кому она относится, посредников и тд.  ТУПО вывести.
 * - 1
 */ # ДатаВремя создания: Первая версия-120121 / Перепись с нуля / Перепись 2 - 080123
class FrontPageLoader
{
    # - ### ### ###
    #   NOTE:

    public function __construct() {  dd(__CLASS__.' - Только статичный вызов.');  }
    public function __destruct()  {    }

    # - ### ### ###
    #   NOTE:



    # - ### ### ###
    #   NOTE:

    public static function openPageView($fullPathToView, array $params=[])
    {

        if( $params !== [] ) # Если что-то пришло
        {
            if( is_string($params) )
                dd(__METHOD__.' - Пришли параметры в виде строки. Надо массив, в тч []', $params);

            if( ! is_array($params) )
                dd(__METHOD__.' - Пришли параметры в виде не массива. Надо массив, в тч []', $params);

            if( count($params) ) # Пришел не пустой массив
                if (isset($array[0])) # Есть значение $params[0] - значит это обычный массив, НЕ асоциативный
                    dd(__METHOD__.' - Параметры пришли в виде не пустого и НЕ асоц массива. Нужен асоц массив либо пустой[]!', $params);

        }

        # - ###

        #$routeName   = Route::currentRouteName();
        #$routeAction = Route::currentRouteAction();  # Юзлесс, на всякий
        #$routeUri    = Route::current()->uri;

        $INFO = [
            #'FOLDER' => [
                #'views' => self::$folderSetsTemplates,  # bladeViews
                #'publicDep' => self::$folderSetsDepend,  # depsFiles
            #],
            'ROUTING' => Requester::getRoutingInfo(),
            'PARAMS' => $params
        ];

        View::share('INFO',$INFO);

        # - ###

        if( ! View::exists($fullPathToView) )
            dd(__METHOD__.' - View не существует!!', $fullPathToView, $INFO);
        else
            return view($fullPathToView);
    }

    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
