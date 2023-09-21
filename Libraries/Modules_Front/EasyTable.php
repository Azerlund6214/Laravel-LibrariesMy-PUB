<?php

namespace LibMy;



/**
 * Быстрый универсальный рисовальщик таблиц по данным из бекенда.
 * Планируется только для дебага и подобного.
 */
class EasyTable
{
    # - ### ### ###
    #   NOTE:

	/*
	CONCEPT: Полностью переписывать
	 *
	 *
		изитейбл   экземплярный.
		метод-сет заголовков столбцов.
		метод - сет стиль ...   много видов
		метод - сет рандом ID тега таблицы
		метод - эхо тега со всеми стилями ксс
		методы статик для фаст принта
		.
		Метод быстро рисующий таблицы из массивов
		хз
		tableFor_ArrOne
		tableFor_ArrAsocSimple
	 *
	 # */

    # - ### ### ###
    #   NOTE:

    # NOTE: https://codebeautify.org/html-table-generator

    public static function echoStyle_1()
    {

        echo '<style>

            .table-dumper {
                border:1px solid #b3adad;
                border-collapse:collapse;
                padding:5px;
            }
            .table-dumper th {
                border:1px solid #b3adad;
                padding:5px;
                background: #f0f0f0;
                color: #313030;
            }
            .table-dumper td {
                border:1px solid #b3adad;
                text-align:center;
                padding:5px;
                background: #ffffff;
                color: #313030;
            }
        </style>';
    }

    public static function tableBegin(){ echo '<table class="table-dumper">'; }
    public static function tableEnd(){ echo '</table>'; }

    public static function bodyBegin(){ echo '<tbody>'; }
    public static function bodyEnd(){ echo '</tbody>'; }


    public static function makeHead( $arrNames )
    {
        echo '<thead><tr>';

        foreach( $arrNames as $one )
            echo "<th>$one</th>";

        echo '</tr></thead>';
    }



    public static function makeRow($arrData)
    {
        echo '<tr>';

        foreach( $arrData as $one )
            echo "<td>$one</td>";

        echo '</tr>';
    }




    # - ### ### ###
    #   NOTE:



    # - ##### ##### ##### ##### ##### ##### ######
    # - #####             #####             ######
    # - ##### ##### ##### ##### ##### ##### ######

} # End class
