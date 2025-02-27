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

switch (filter('op')) {
    case 'update':
        $descrizione = filter('descrizione');
        $vettore = post('esterno');
        $predefined = post('predefined');

        if ($dbo->fetchNum('SELECT * FROM `dt_spedizione` LEFT JOIN `dt_spedizione_lang` ON (`dt_spedizione`.`id`=`dt_spedizione_lang`.`idrecord` AND `dt_spedizione_lang`.`id_lang`='.prepare(\App::getLang()).') WHERE `name`='.prepare($descrizione).' AND `dt_spedizione`.`id`!='.prepare($id_record)) == 0) {
            if (!empty($predefined)) {
                $dbo->query('UPDATE `dt_spedizione` SET `predefined` = 0');
            }
            $dbo->update('dt_spedizione', [
                'predefined' => $predefined,
                'esterno' => $vettore,
            ], ['id' => $id_record]);

            $dbo->update('dt_spedizione_lang', [
                'name' => $descrizione,
            ], ['id_record' => $id_record, 'id_lang' => \App::getLang()]);

            flash()->info(tr('Salvataggio completato!'));
        } else {
            flash()->error(tr("E' già presente una tipologia di _TYPE_ con la stessa descrizione", [
                '_TYPE_' => 'spedizione',
            ]));
        }
        break;

    case 'add':
        $descrizione = filter('descrizione');

        if ($dbo->fetchNum('SELECT * FROM `dt_spedizione_lang` WHERE `name`='.prepare($descrizione)) == 0) {
            $dbo->insert('dt_spedizione', [
                'predefined' => 0,
            ]);
            $id_record = $dbo->lastInsertedID();

            $dbo->insert('dt_spedizione_lang', [
                'id_record' => $id_record,
                'id_lang' => \App::getLang(),
                'name' => $descrizione,
            ]);

            flash()->info(tr('Aggiunta nuova tipologia di _TYPE_', [
                '_TYPE_' => 'spedizione',
            ]));
        } else {
            flash()->error(tr("E' già presente una tipologia di _TYPE_ con la stessa descrizione", [
                '_TYPE_' => 'spedizione',
            ]));
        }

        break;

    case 'delete':
        $documenti = $dbo->fetchNum('SELECT `id` FROM `dt_ddt` WHERE `idspedizione`='.prepare($id_record).'
            UNION SELECT `id` FROM `co_documenti` WHERE `idspedizione`='.prepare($id_record));

        if (isset($id_record) && empty($documenti)) {
            $dbo->query('DELETE FROM `dt_spedizione` WHERE `id`='.prepare($id_record));

            flash()->info(tr('Tipologia di _TYPE_ eliminata con successo!', [
                '_TYPE_' => 'spedizione',
            ]));
        } else {
            flash()->error(tr('Sono presenti dei documenti collegati a questo porto.'));
        }

        break;
}
