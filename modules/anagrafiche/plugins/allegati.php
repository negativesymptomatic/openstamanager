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

use Models\Upload;
use Modules\Anagrafiche\Anagrafica;
use Models\Module;

include_once __DIR__.'/../../../core.php';

// Allegati dell'anagrafica
echo '
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">'.tr('Allegati dell\'anagrafica').'</h3>
    </div>
    <div class="box-body">';

if (empty($_GET['visualizza_allegati'])) {
    echo '
        <div class="row">
            <div class="col-md-12 text-center">
                <a class="btn btn-info btn-lg" href="'.base_path().'/editor.php?id_module='.$id_module.'&id_record='.$id_record.'&visualizza_allegati=1#tab_'.$id_plugin.'">
                    <i class="fa fa-eye"></i>
                    '.tr('Visualizza tutti gli allegati').'
                </a>
            </div>
        </div>';
} else {
    // Controllo i permessi dei modulo per la visualizzazione degli allegati
    $rs = $dbo->table('zz_permissions')->where('idgruppo', $user->idgruppo)->get();
    $permessi = [];
    $documenti[] = 0;

    foreach ($rs as $r) {
        $permessi[] = $r->idmodule;
    }

    if ($anagrafica->uploads()->first()) {
        $documenti[] = [
            'id_module' => $id_module,
            'id_record' => $id_record,
            'descrizione' => "Allegato dell'anagrafica",
        ];
    }

    // Interventi dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Interventi')->id_record, $permessi)) {
        $interventi = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Interventi')->id_record)." AS id_module, `id` AS id_record, CONCAT('Intervento num. ',codice,' del ',DATE_FORMAT(`data_richiesta`,'%d/%m/%Y')) AS descrizione FROM `in_interventi` WHERE `idanagrafica`=".prepare($id_record));
        $documenti = array_merge($documenti, $interventi);
    }

    // Preventivi dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Preventivi')->id_record, $permessi)) {
        $preventivi = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Preventivi')->id_record)." AS id_module, id AS id_record, CONCAT('Preventivo num. ',numero,' del ',DATE_FORMAT(data_bozza,'%d/%m/%Y')) AS descrizione FROM co_preventivi WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $preventivi);
    }

    // Contratti dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Contratti')->id_record, $permessi)) {
        $contratti = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Contratti')->id_record)." AS id_module, id AS id_record, CONCAT('Preventivo num. ',numero,' del ',DATE_FORMAT(data_bozza,'%d/%m/%Y')) AS descrizione FROM co_contratti WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $contratti);
    }

    // DDT dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Ddt di vendita')->id_record, $permessi)) {
        $ddt_vendita = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Ddt di vendita')->id_record)." AS id_module, id AS id_record, CONCAT('Ddt di vendita num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data,'%d/%m/%Y')) AS descrizione FROM dt_ddt WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $ddt_vendita);
    }

    if ($user->is_admin || in_array((new Module())->getByName('Ddt di acquisto')->id_record, $permessi)) {
        $ddt_acquisto = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Ddt di acquisto')->id_record)." AS id_module, id AS id_record, CONCAT('Ddt di acquisto num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data,'%d/%m/%Y')) AS descrizione FROM dt_ddt WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $ddt_acquisto);
    }

    // Fatture dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Fatture di vendita')->id_record, $permessi)) {
        $fatture_vendita = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Fatture di vendita')->id_record)." AS id_module, id AS id_record, CONCAT('Fattura di vendita num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data_registrazione,'%d/%m/%Y')) AS descrizione FROM co_documenti WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $fatture_vendita);
    }

    if ($user->is_admin || in_array((new Module())->getByName('Fatture di acquisto')->id_record, $permessi)) {
        $fatture_acquisto = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Fatture di acquisto')->id_record)." AS id_module, id AS id_record, CONCAT('Fattura di acquisto num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data_registrazione,'%d/%m/%Y')) AS descrizione FROM co_documenti WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $fatture_acquisto);
    }

    // Ordini dell'anagrafica
    if ($user->is_admin || in_array((new Module())->getByName('Ordini cliente')->id_record, $permessi)) {
        $ordini_vendita = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Ordini cliente')->id_record)." AS id_module, id AS id_record, CONCAT('Ordine cliente num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data,'%d/%m/%Y')) AS descrizione FROM or_ordini WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $ordini_vendita);
    }

    if ($user->is_admin || in_array((new Module())->getByName('Ordini fornitore')->id_record, $permessi)) {
        $ordini_acquisto = $dbo->fetcharray('SELECT '.prepare((new Module())->getByName('Ordini fornitore')->id_record)." AS id_module, id AS id_record, CONCAT('Ordine fornitore num. ',IFNULL(numero_esterno,numero),' del ',DATE_FORMAT(data,'%d/%m/%Y')) AS descrizione FROM or_ordini WHERE idanagrafica=".prepare($id_record));
        $documenti = array_merge($documenti, $ordini_acquisto);
    }

    if (!empty($documenti)) {
        echo '
            <table class="table table-striped table-hover">
                <tr>
                    <th class="text-center" width="7%">#</th>
                    <th>'.tr('Allegato').'</th>
                    <th width="40%">'.tr('Documento').'</th>
                    <th class="text-center" width="10%">'.tr('Data').'</th>
                </tr>';

        foreach ($documenti as $documento) {
            $allegati = $dbo->fetchArray('SELECT * FROM zz_files WHERE id_module='.prepare($documento['id_module']).' AND id_record='.prepare($documento['id_record']));

            foreach ($allegati as $allegato) {
                $file = Upload::find($allegato['id']);

                echo '
                    <tr>
                        <td class="text-center">
                            <a class="btn btn-xs btn-primary" href="'.base_path().'/actions.php?id_module='.$file->id_module.'&op=download-allegato&id='.$file->id.'&filename='.$file->filename.'" target="_blank">
                                <i class="fa fa-download"></i>
                            </a>';

                // Anteprime supportate dal browser
                if ($file->hasPreview()) {
                    echo '
                            <button class="btn btn-xs btn-info" type="button" data-title="'.prepareToField($file->name).' <small style=\'color:white\'><i>('.$file->filename.')</i></small>" data-href="'.base_path().'/view.php?file_id='.$file->id.'">
                                <i class="fa fa-eye"></i>
                            </button>';
                } else {
                    echo '
                            <button class="btn btn-xs btn-default disabled" title="'.tr('Anteprima file non disponibile').'" disabled>
                                <i class="fa fa-eye"></i>
                            </button>';
                }

                echo '
                        </td>
                        <td>
                            <a href="'.base_path().'/view.php?file_id='.$file->id.'" target="_blank">
                                <i class="fa fa-external-link"></i> '.$file->name.'
                            </a>
                        </td>
                        <td>'.Modules::link(Module::find($allegato['id_module']->name), $file->id_record, $documento['descrizione']).'</td>
                        <td class="text-center">'.Translator::dateToLocale($file->created_at).'</td>
                        </tr>';
            }
        }
        echo '
            </table>';
    } else {
        echo '
        <div class=\'alert alert-info\' ><i class=\'fa fa-info-circle\'></i> '.tr('Nessun allegato per questa anagrafica').'.</div>';
    }
}
echo '
    </div>
</div>';
