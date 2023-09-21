<?php

namespace LibMy;



/**
 * Хранение и выдача готового к исполнению JS кода.
 *
 */
class EasyFrontJS
{
    # - ### ### ###

    public function __construct() { dd(__CLASS__.' - Только статичный вызов.'); }
    public function __destruct()  {    }

    # - ### ### ###

    #public static function echoScript_UNIV(){ }  пока спорно

    public static function echoJsVar_String($varName,$contentStr)
    {
        # NOTE: Запрет на ' в контенте

        echo '<script>';
        echo 'var '.$varName.' =\''.($contentStr).'\';';
        echo '</script>';
    }

        # - ### ### ###
    #   NOTE:

    # public static function echoScript_CopyToClip_OLD_Native(){  }


    public static function echoScript_CopyToClip_ById_Native()
    {
        # http://www.brandgradients.com/google-colors/
        /*  JS
        function copyToClipboard(element) {
          var $temp = $("<input>");
          $("body").append($temp);
          $temp.val($(element).text()).select();
          document.execCommand("copy");
          $temp.remove();
        }
        */

        echo '<script>

        function copyToClipboard(element)
        {
            var $temp = document.createElement("input");
            document.body.append($temp);
            $temp.value = document.getElementById(element).textContent;  // NOTE: Может понадобиться просто "text"
            $temp.select();
            document.execCommand("copy");
            $temp.remove();
        }

        </script>
        ';

    }

    # - ### ### ###
    #   NOTE:


    public static function echoJS_ReloadPage_PHP_WithDump(int $timeSec){  dump(__METHOD__.' => Сплю '.$timeSec.' и релоад JS'); flush(); sleep($timeSec); echo '<script>window.location.reload(true);</script>'; }
    public static function echoJS_ReloadPage(){ echo '<script>window.location.reload(true);</script>'; }
    public static function echoJS_Redirect($url){ echo "<script>window.location.replace('{$url}');</script>"; }
	
    
    public static function echoJS_reloadOnDomLoaded(){ echo '<script>document.addEventListener("DOMContentLoaded", function(event) {  window.location.reload(true);  });</script>'; }
    public static function echoJS_alertOnDomLoaded($text='DOM Loaded'){ echo '<script>document.addEventListener("DOMContentLoaded", function(event) {  alert("'.$text.'");  });</script>'; }

    public static function echoJS_ScrollToBottomFast(){ echo '<script>window.scrollBy(0, document.body.scrollHeight);</script>'; }
    public static function echoJS_AutoScrollBottom($time=500)
    {
        echo '<script>
               function scrollToBottom(timedelay=200)
               {
                var scrollId;
                //var height = 0;
                //var minScrollHeight = 100;
                scrollId = setInterval(function () {
                    //if (height <= document.body.scrollHeight) {
                        window.scrollBy(0, document.body.scrollHeight); /* minScrollHeight */
                    //}
                    //else {
                        // clearInterval(scrollId);
                    //}
                    //height += minScrollHeight;
                }, timedelay);
            }
            </script>';
        echo '<script>scrollToBottom('.$time.');</script>';

    }

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
