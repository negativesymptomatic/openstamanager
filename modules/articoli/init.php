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

include_once __DIR__.'/../../core.php';

use Modules\Articoli\Articolo;

if (isset($id_record)) {
    $articolo = Articolo::withTrashed()->find($id_record);

    $record = $dbo->fetchOne('SELECT *, `mg_articoli_lang`.`name` as descrizione, (SELECT COUNT(id) FROM `mg_prodotti` WHERE `id_articolo` = `mg_articoli`.`id`) AS serial FROM `mg_articoli` LEFT JOIN `mg_articoli_lang` ON (`mg_articoli_lang`.`id_record` = `mg_articoli`.`id` AND `mg_articoli_lang`.`id_lang` = '.prepare(\App::getLang()).') WHERE `mg_articoli`.`id`='.prepare($id_record));
}
