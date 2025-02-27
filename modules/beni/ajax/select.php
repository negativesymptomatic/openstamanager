<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

include_once __DIR__.'/../../../core.php';

switch ($resource) {
    case 'aspetto-beni':
        $query = 'SELECT `dt_aspettobeni`.`id`, `dt_aspettobeni_lang`.`name` as descrizione FROM `dt_aspettobeni` LEFT JOIN `dt_aspettobeni_lang` ON (`dt_aspettobeni`.`id`=`dt_aspettobeni_lang`.`id_record` AND `dt_aspettobeni_lang`.`id_lang`='.prepare(\App::getLang()).') |where| ORDER BY `name` ASC';

        foreach ($elements as $element) {
            $filter[] = '`dt_aspettobeni`.`id`='.prepare($element);
        }

        if (!empty($search)) {
            $search_fields[] = '`name` LIKE '.prepare('%'.$search.'%');
        }

        break;
}
