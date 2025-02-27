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

use Modules\Anagrafiche\Anagrafica;
use Modules\Anagrafiche\Sede;
use Models\Module;
use Models\Plugin;

include_once __DIR__.'/../../core.php';

$block_edit = $record['flag_completato'];
$id_modulo_anagrafiche = (new Module())->getByName('Anagrafiche')->id_record;

// Verifica aggiuntive sulla sequenzialità dei numeri
$numero_previsto = verifica_numero_intervento($intervento);

if (!empty($numero_previsto) && intval(setting('Verifica numero intervento'))) {
    echo '
<div class="alert alert-warning alert-dismissable">
    <i class="fa fa-warning"></i> '.tr("E' assente una attività con numero _NUM_ in data precedente o corrispondente al _DATE_: potrebbero esserci alcuni errori di continuità con la numerazione delle attività", [
            '_DATE_' => dateFormat($intervento->data_richiesta),
            '_NUM_' => '"'.$numero_previsto.'"',
        ]).'.</b>

    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
</div>';
}

echo '
<div class="row">
    <div class="col-md-2">';
if ($prev) {
    echo '
            <a class="btn btn-info btn-block" href="'.base_path().'/editor.php?id_module=3&id_record='.$prev.'">
                <i class="fa fa-arrow-circle-left"></i> '.tr('Precedente').'
            </a>';
} echo '
    </div>
    <div class="col-md-2 col-md-offset-8">';
if ($next) {
    echo '
            <a class="btn btn-info btn-block" href="'.base_path().'/editor.php?id_module=3&id_record='.$next.'">
                '.tr('Successivo').' <i class="fa fa-arrow-circle-right"></i>
            </a>';
}
echo '
    </div>
</div>
<br>
<form action="" method="post" id="edit-form">
	<input type="hidden" name="op" value="update">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="id_record" value="'.$id_record.'">

    <div class="row">
        <div class="col-md-8">
            <!-- DATI CLIENTE -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">'.tr('Dati cliente').'</h3>
                </div>

                <div class="panel-body">
                    <!-- RIGA 1 -->
                    <div class="row">
                        <div class="col-md-6">
                            '.Modules::link('Anagrafiche', $record['idanagrafica'], null, null, 'class="pull-right"').'
                            {[ "type": "select", "label": "'.tr('Cliente').'", "name": "idanagrafica", "required": 1, "value": "$idanagrafica$", "ajax-source": "clienti", "readonly": "'.($user['gruppo'] == 'Clienti' ? '1' : $record['flag_completato']).'" ]}
                        </div>

                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Sede destinazione').'", "name": "idsede_destinazione","value": "$idsede_destinazione$", "ajax-source": "sedi", "select-options": '.json_encode(['idanagrafica' => $record['idanagrafica']]).', "placeholder": "'.tr('Sede legale').'", "readonly": "'.$record['flag_completato'].'" ]}
                        </div>

                        <div class="col-md-6">';
if (!empty($record['idclientefinale'])) {
    echo '
                                                        '.Modules::link('Anagrafiche', $record['idclientefinale'], null, null, 'class="pull-right"');
}
echo '
                            {[ "type": "select", "label": "'.tr('Per conto di').'", "name": "idclientefinale", "value": "$idclientefinale$", "ajax-source": "clienti", "readonly": "'.$record['flag_completato'].'" ]}
                        </div>

                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Referente').'", "name": "idreferente", "value": "$idreferente$", "ajax-source": "referenti", "select-options": '.json_encode(['idanagrafica' => $record['idanagrafica'], 'idclientefinale' => $record['idclientefinale'], 'idsede_destinazione' => $record['idsede_destinazione']]).', "readonly": "'.intval($record['flag_completato']).'", "icon-after": "add|'.$id_modulo_anagrafiche.'|id_plugin='.(new Plugin())->getByName('Referenti')->id_record.'&id_parent='.$record['idanagrafica'].'" ]}
                        </div>
                    </div>

                    <!-- RIGA 2 -->
                    <div class="row">
                        <div class="col-md-6">';
if (!empty($record['idpreventivo'])) {
    echo '
                            '.Modules::link('Preventivi', $record['idpreventivo'], null, null, 'class="pull-right"');
}
echo '
                            {[ "type": "select", "label": "'.tr('Preventivo').'", "name": "idpreventivo", "value": "'.$record['id_preventivo'].'", "ajax-source": "preventivi", "select-options": '.json_encode(['idanagrafica' => $record['idanagrafica']]).', "readonly": "'.$record['flag_completato'].'", "icon-after": "add|'.(new Module())->getByName('Preventivi')->id_record.'|pianificabile=1&idanagrafica='.$record['idanagrafica'].'"  ]}
                        </div>

                        <div class="col-md-6">';

$idpreventivo_riga = $dbo->fetchOne('SELECT id FROM co_promemoria WHERE idintervento='.prepare($id_record))['id'];

if (!empty($record['idcontratto'])) {
    echo '
                                    '.Modules::link('Contratti', $record['idcontratto'], null, null, 'class="pull-right"');
}
echo '

                            {[ "type": "select", "label": "'.tr('Contratto').'", "name": "idcontratto", "value": "'.$record['id_contratto'].'", "ajax-source": "contratti", "select-options": '.json_encode(['idanagrafica' => $record['idanagrafica']]).', "readonly": "'.$record['flag_completato'].'", "icon-after": "add|'.(new Module())->getByName('Contratti')->id_record.'|pianificabile=1&idanagrafica='.$record['idanagrafica'].'" ]}

                            <input type="hidden" name="idcontratto_riga" value="'.$idcontratto_riga.'">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">';

$idcontratto_riga = $dbo->fetchOne('SELECT id FROM co_promemoria WHERE idintervento='.prepare($id_record))['id'];

if (!empty($record['idordine'])) {
    echo '
                            '.Modules::link('Ordini cliente', $record['idordine'], null, null, 'class="pull-right"');
}
echo '

                            {[ "type": "select", "label": "'.tr('Ordine').'", "name": "idordine", "value": "'.$record['id_ordine'].'", "ajax-source": "ordini-cliente", "select-options": '.json_encode(['idanagrafica' => $record['idanagrafica']]).', "readonly": "'.$record['flag_completato'].'" ]}
                        </div>

                        <div class="col-md-6">';
if ($record['idagente'] != 0) {
    echo Modules::link('Anagrafiche', $record['idagente'], null, null, 'class="pull-right"');
}
echo '
                            {[ "type": "select", "label": "'.tr('Agente').'", "name": "idagente", "ajax-source": "agenti", "select-options": {"idanagrafica": '.$record['idanagrafica'].'}, "value": "$idagente$" ]}
                        </div>
                    </div>
                </div>
            </div>
        </div>';

$anagrafica_cliente = $intervento->anagrafica;
$sede_cliente = $anagrafica_cliente->sedeLegale;
if (!empty($intervento->idsede_destinazione)) {
    $sede_cliente = Sede::find($intervento->idsede_destinazione);
}

$anagrafica_azienda = Anagrafica::find(setting('Azienda predefinita'));
$sede_azienda = $anagrafica_azienda->sedeLegale;

echo '
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-map"></i> '.tr('Geolocalizzazione').'</h3>
                </div>
                <div class="panel-body">';

if (!empty($sede_cliente->gaddress) || (!empty($sede_cliente->lat) && !empty($sede_cliente->lng))) {
    echo '
                    <div id="map-edit" style="width: 100%;"></div>

                    <div class="clearfix"></div>
                        <br>';

    // Navigazione diretta verso l'indirizzo
    echo '
                        <a class="btn btn-info btn-block" onclick="$(\'#map-edit\').height(235); caricaMappa(); $(this).hide();">
                            <i class="fa fa-compass"></i> '.tr('Carica mappa').'
                        </a>';

    // Navigazione diretta verso l'indirizzo
    echo '
                        <a class="btn btn-info btn-block" onclick="calcolaPercorso()">
                            <i class="fa fa-map-signs"></i> '.tr('Calcola percorso').'
                        </a>';
} else {
    // Navigazione diretta verso l'indirizzo
    echo '
                        <a class="btn btn-info btn-block" onclick="calcolaPercorso()">
                            <i class="fa fa-map-signs"></i> '.tr('Calcola percorso').'
                        </a>';

    // Ricerca diretta su Mappa
    echo '
                        <a class="btn btn-info btn-block" onclick="cercaOpenStreetMap()">
                            <i class="fa fa-map-marker"></i> '.tr('Cerca su Mappa').'
                        </a>';
}

echo '
                    </div>
                </div>
            </div>
        </div>

        <script>
            function modificaPosizione() {
                openModal("'.tr('Modifica posizione').'", "'.$module->fileurl('modals/posizione.php').'?id_module='.$id_module.'&id_record='.$id_record.'");
            }

            function cercaOpenStreetMap() {
                const indirizzo = getIndirizzoAnagrafica();

                const destinazione = (!isNaN(indirizzo[0]) && !isNaN(indirizzo[1])) ? indirizzo[0] + ","+ indirizzo[1] : indirizzo[2];
                if (isMobile.any) {
                    window.open("geo:" + destinazione + "?z=16&q=" + destinazione);
                } else {
                    if (!isNaN(indirizzo[0]) && !isNaN(indirizzo[1])) {
                        window.open("https://www.openstreetmap.org/?mlat=" + indirizzo[0] + "&mlon=" + indirizzo[1] + "#map=12/" + destinazione + "/" + indirizzo[1]);
                    } else {
                        window.open("https://www.openstreetmap.org/search?query=" + indirizzo[2] + "#map=12");
                    }
                }
            }

            function calcolaPercorso() {
                const indirizzo_partenza = getIndirizzoAzienda();
                const indirizzo_destinazione = getIndirizzoAnagrafica();

                const destinazione = (!isNaN(indirizzo_destinazione[0]) && !isNaN(indirizzo_destinazione[1])) ? indirizzo_destinazione[0] + ","+ indirizzo_destinazione[1] : indirizzo_destinazione[2];

                if (isMobile.any) {
                    window.open("geo:" + destinazione + "?z=16&q=" + destinazione);
                } else {
                    window.open("https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=" + indirizzo_partenza + ";" + destinazione);
                }
            }

            function getIndirizzoAzienda() {
                const indirizzo = "'.$sede_azienda->indirizzo.'";
                const citta = "'.$sede_azienda->citta.'";

                const lat = parseFloat("'.$sede_azienda->lat.'");
                const lng = parseFloat("'.$sede_azienda->lng.'");

                if (lat && lng){
                    return lat + ","+ lng;
                } else {
                    return "";
                }
            }

            function getIndirizzoAnagrafica() {
                const indirizzo = "'.$sede_cliente->indirizzo.'";
                const citta = "'.$sede_cliente->citta.'";

                const lat = parseFloat("'.$sede_cliente->lat.'");
                const lng = parseFloat("'.$sede_cliente->lng.'");

                const indirizzo_default = encodeURI(indirizzo) + "," + encodeURI(citta);

                return [lat, lng, indirizzo_default];
            }

            var map = null;
            function caricaMappa() {
                const lat = parseFloat("'.$sede_cliente->lat.'");
                const lng = parseFloat("'.$sede_cliente->lng.'");
            
                var container = L.DomUtil.get("map-edit"); 
                if(container._leaflet_id != null){ 
                    map.eachLayer(function (layer) {
                        if(layer instanceof L.Marker) {
                            map.removeLayer(layer);
                        }
                    });
                } else {
                    map = L.map("map-edit", {
                        gestureHandling: true
                    });
            
                    L.tileLayer("'.setting('Tile server OpenStreetMap').'", {
                        maxZoom: 17,
                        attribution: "© OpenStreetMap"
                    }).addTo(map); 
                }
            
                var icon = new L.Icon({
                    iconUrl: globals.rootdir + "/assets/dist/img/marker-icon.png",
                    shadowUrl:globals.rootdir + "/assets/dist/img/leaflet/marker-shadow.png",
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                });

                var marker = L.marker([lat, lng], {
                    icon: icon
                }).addTo(map);
            
                map.setView([lat, lng], 10);
            }
        </script>';

?>
    <!-- DATI INTERVENTO -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo tr('Dati intervento'); ?></h3>
        </div>

        <div class="panel-body">
            <!-- RIGA 3 -->
            <div class="row">
                <div class="col-md-3">
                    {[ "type": "text", "label": "<?php echo tr('Numero'); ?>", "name": "codice", "value": "$codice$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                </div>

                <div class="col-md-3">
                    {[ "type": "timestamp", "label": "<?php echo tr('Data/ora richiesta'); ?>", "name": "data_richiesta", "required": 1, "value": "$data_richiesta$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                </div>

                <div class="col-md-3">
                    {[ "type": "timestamp", "label": "<?php echo tr('Data/ora scadenza'); ?>", "name": "data_scadenza", "required": 0, "value": "$data_scadenza$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                </div>

                <div class="col-md-3">
                    {[ "type": "select", "label": "<?php echo tr('Zona'); ?>", "name": "idzona", "values": "query=SELECT id, CONCAT_WS( ' - ', nome, descrizione) AS descrizione FROM an_zone ORDER BY nome", "value": "$idzona$" , "placeholder": "<?php echo tr('Nessuna zona'); ?>", "extra": "readonly", "help":"<?php echo 'La zona viene definita automaticamente in base al cliente selezionato'; ?>." ]}
                </div>

            </div>

            <!-- RIGA 4 -->
            <div class="row">
                <div class="col-md-4">
                    {[ "type": "select", "label": "<?php echo tr('Tipo attività'); ?>", "name": "idtipointervento", "required": 1, "ajax-source": "tipiintervento", "value": "$idtipointervento$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                </div>

                <div class="col-md-4">
                    {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "idstatointervento", "required": 1, "values": "query=SELECT `in_statiintervento`.`id`, `name` as descrizione, `colore` AS _bgcolor_ FROM `in_statiintervento`  LEFT JOIN `in_statiintervento_lang` ON (`in_statiintervento`.`id` = `in_statiintervento_lang`.`id_record` AND `in_statiintervento_lang`.`id_lang` = <?php echo prepare(\App::getLang()); ?>) WHERE `deleted_at` IS NULL ORDER BY `name`", "value": "$id$", "class": "unblockable" ]}
                </div>
<?php

$tecnici_assegnati = $database->fetchArray('SELECT id_tecnico FROM in_interventi_tecnici_assegnati WHERE id_intervento = '.prepare($id_record));
$tecnici_assegnati = array_column($tecnici_assegnati, 'id_tecnico');
echo '
                <div class="col-md-4">
                    {[ "type": "select", "label": "'.tr('Tecnici assegnati').'", "multiple": "1", "name": "tecnici_assegnati[]", "ajax-source": "tecnici", "value": "'.implode(',', $tecnici_assegnati).'", "icon-after": "add|'.$id_modulo_anagrafiche.'|tipoanagrafica=Tecnico&readonly_tipo=1" ]}
                </div>
            </div>

            <!-- RIGA 5 -->
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">';
echo input([
    'type' => 'ckeditor',
    'label' => tr('Richiesta'),
    'name' => 'richiesta',
    'required' => 1,
    'value' => $record['richiesta'],
    'extra' => 'style=\'max-height:80px;\'',
]);
echo '
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">';
echo input([
    'type' => 'ckeditor',
    'label' => tr('Descrizione'),
    'name' => 'descrizione',
    'value' => $record['descrizione'],
    'extra' => 'style=\'max-height:80px;\'',
]);
echo '
                </div>
            </div>';
// Nascondo le note interne ai clienti
if ($user->gruppo != 'Clienti') {
    echo '
            <div class="row">
                <div class="col-md-12">
                    {[ "type": "textarea", "label": "'.tr('Note interne').'", "name": "informazioniaggiuntive", "class": "autosize", "value": "$informazioniaggiuntive$", "extra": "rows=\'5\'" ]}
                </div>
            </div>';
}

// Conteggio numero articoli intervento per eventuale blocco della sede di partenza
$articoli = $intervento->articoli;
echo '
            <div class="row">
                <div class="col-md-4">
                    {[ "type": "select", "label": "'.tr('Partenza merce').'", "name": "idsede_partenza", "ajax-source": "sedi_azienda", "value": "$idsede_partenza$", "readonly": "'.(($record['flag_completato'] || !$articoli->isEmpty()) ? 1 : 0).'" ]}
                </div>
            </div>
        </div>
    </div>';

// Visualizzo solo se l'anagrafica cliente è un ente pubblico
if (!empty($record['idcontratto'])) {
    $contratto = $dbo->fetchOne('SELECT num_item,codice_cig,codice_cup,id_documento_fe FROM co_contratti WHERE id = '.prepare($record['idcontratto']));
    $record['id_documento_fe'] = $contratto['id_documento_fe'];
    $record['codice_cup'] = $contratto['codice_cup'];
    $record['codice_cig'] = $contratto['codice_cig'];
    $record['num_item'] = $contratto['num_item'];
}

?>
    <!-- Fatturazione Elettronica PA-->
    <div class="panel panel-primary <?php echo ($record['tipo_anagrafica'] == 'Ente pubblico' || $record['tipo_anagrafica'] == 'Azienda') ? 'show' : 'hide'; ?>" >
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo tr('Dati appalto'); ?>
			<?php if (!empty($record['idcontratto'])) {
			    ?>
			<span class="tip" title="<?php echo tr('E\' possibile specificare i dati dell\'appalto solo se il cliente è di tipo \'Ente pubblico\' o \'Azienda\' e l\'attività non risulta già collegata ad un contratto.'); ?>" > <i class="fa fa-question-circle-o"></i></span>
			</h3>
			<?php
			} ?>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
					{[ "type": "<?php echo !empty($record['idcontratto']) ? 'span' : 'text'; ?>", "label": "<?php echo tr('Identificatore Documento'); ?>", "name": "id_documento_fe", "required": 0, "help": "<?php echo tr('<span>Obbligatorio per valorizzare CIG/CUP. &Egrave; possible inserire: </span><ul><li>N. determina</li><li>RDO</li><li>Ordine MEPA</li></ul>'); ?>", "value": "<?php echo $record['id_documento_fe']; ?>", "maxlength": 20, "readonly": "<?php echo $record['flag_completato']; ?>", "extra": "" ]}
				</div>

                <div class="col-md-6">
					{[ "type": "<?php echo !empty($record['idcontratto']) ? 'span' : 'text'; ?>", "label": "<?php echo tr('Numero Riga'); ?>", "name": "num_item", "required": 0, "value": "<?php echo $record['num_item']; ?>", "maxlength": 15, "readonly": "<?php echo $record['flag_completato']; ?>", "extra": "" ]}
				</div>
			</div>
			<div class="row">
                <div class="col-md-6">
					{[ "type": "<?php echo !empty($record['idcontratto']) ? 'span' : 'text'; ?>", "label": "<?php echo tr('Codice CIG'); ?>", "name": "codice_cig", "required": 0, "value": "<?php echo $record['codice_cig']; ?>", "maxlength": 15, "readonly": "<?php echo $record['flag_completato']; ?>", "extra": "" ]}
				</div>

				<div class="col-md-6">
					{[ "type": "<?php echo !empty($record['idcontratto']) ? 'span' : 'text'; ?>", "label": "<?php echo tr('Codice CUP'); ?>", "name": "codice_cup", "required": 0, "value": "<?php echo $record['codice_cup']; ?>", "maxlength": 15, "readonly": "<?php echo $record['flag_completato']; ?>", "extra": "" ]}
				</div>
            </div>
        </div>
    </div>
</form>

<!-- ORE LAVORO -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo tr('Sessioni di lavoro'); ?></h3>
    </div>

    <div class="panel-body">
    <?php
			if ($show_prezzi) {
			    echo "
        <div class=\"pull-right\">
            <a class='btn btn-default btn-details' onclick=\"$('.extra').removeClass('hide'); $(this).addClass('hide'); $('#dontshowall_dettagli').removeClass('hide');\" id='showall_dettagli'><i class='fa fa-square-o'></i> <?php echo tr('Visualizza dettaglio costi'); ?></a>
            <a class='btn btn-info btn-details hide' onclick=\"$('.extra').addClass('hide'); $(this).addClass('hide'); $('#showall_dettagli').removeClass('hide');\" id='dontshowall_dettagli'><i class='fa fa-check-square-o'></i> <?php echo tr('Visualizza dettaglio costi'); ?></a>
        </div>
        <div class=\"clearfix\"></div>
        <br>";
			}
?>

        <div class="row">
            <div class="col-md-12" id="tecnici"></div>
        </div>
    </div>
</div>

<!-- RIGHE -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo tr('Righe'); ?></h3>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">

<?php

if (!$block_edit) {
    // Lettura preventivi accettati, in attesa di conferma o in lavorazione
    $prev_query = 'SELECT 
            COUNT(*) AS tot 
        FROM 
            `co_preventivi`
            INNER JOIN `co_statipreventivi` ON `co_preventivi`.`idstato` = `co_statipreventivi`.`id`
            INNER JOIN `co_righe_preventivi` ON `co_preventivi`.`id` = `co_righe_preventivi`.`idpreventivo`
        WHERE 
            idanagrafica='.prepare($record['idanagrafica']).' AND `co_statipreventivi`.`is_fatturabile` = 1 AND `default_revision`=1 AND ((`co_righe_preventivi`.`qta` - `co_righe_preventivi`.`qta_evasa`) > 0)';
    $preventivi = $dbo->fetchArray($prev_query)[0]['tot'];

    // Lettura contratti accettati, in attesa di conferma o in lavorazione
    $contr_query = 'SELECT COUNT(*) AS tot FROM `co_contratti` WHERE `idanagrafica`='.prepare($record['idanagrafica']).' AND `idstato` IN (SELECT `id` FROM `co_staticontratti` WHERE `is_fatturabile` = 1) AND `co_contratti`.`id` IN (SELECT `idcontratto` FROM `co_righe_contratti` WHERE `co_righe_contratti`.`idcontratto` = `co_contratti`.`id` AND (`qta` - `qta_evasa`) > 0)';
    $contratti = $dbo->fetchArray($contr_query)[0]['tot'];

    // Lettura ddt (entrata o uscita)
    $ddt_query = 'SELECT 
            COUNT(*) AS tot 
        FROM 
            `dt_ddt`
            LEFT JOIN `dt_causalet` ON `dt_causalet`.`id` = `dt_ddt`.`idcausalet`
            INNER JOIN `dt_statiddt` ON `dt_statiddt`.`id` = `dt_ddt`.`idstatoddt`
            LEFT JOIN `dt_statiddt_lang` ON (`dt_statiddt_lang`.`id_record` = `dt_statiddt`.`id` AND `dt_statiddt_lang`.`id_lang` = '.prepare(\App::getLang()).')
            INNER JOIN `dt_tipiddt` ON `dt_tipiddt`.`id` = `dt_ddt`.`idtipoddt`
            INNER JOIN `dt_righe_ddt` ON `dt_righe_ddt`.`idddt` = `dt_ddt`.`id`
        WHERE 
            `idanagrafica`='.prepare($record['idanagrafica']).'
            AND `dt_statiddt_lang`.`name` IN ("Evaso", "Parzialmente evaso", "Parzialmente fatturato")
            AND `dt_tipiddt`.`dir` = '.prepare($intervento->direzione).'
            AND `dt_causalet`.`is_importabile` = 1
            AND (`dt_righe_ddt`.`qta` - `dt_righe_ddt`.`qta_evasa`) > 0';
    $ddt = $dbo->fetchArray($ddt_query)[0]['tot'];

    // Form di inserimento riga documento
    echo '
                <form id="link_form" action="" method="post">
                    <input type="hidden" name="op" value="add_articolo">
                    <input type="hidden" name="backto" value="record-edit">

                    <div class="row">
                        <div class="col-md-4">
                            {[ "type": "text", "label": "'.tr('Aggiungi un articolo tramite barcode').'", "name": "barcode", "extra": "autocomplete=\"off\"", "icon-before": "<i class=\"fa fa-barcode\"></i>", "required": 0 ]}
                        </div>

                        <div class="col-md-4">
                            {[ "type": "select", "label": "'.tr('Articolo').'", "name": "id_articolo", "value": "", "ajax-source": "articoli", "select-options": '.json_encode(['idsede_partenza' => $record['idsede_partenza']]).', "icon-after": "add|'.(new Module())->getByName('Articoli')->id_record.'" ]}
                        </div>

                        <div class="col-md-4" style="margin-top: 25px">
                            <button title="'.tr('Aggiungi articolo alla vendita').'" class="btn btn-primary tip" type="button" onclick="salvaArticolo()">
                                <i class="fa fa-plus"></i> '.tr('Aggiungi').'
                            </button>
                            
                            <a class="btn btn-primary" onclick="gestioneRiga(this)" data-title="'.tr('Aggiungi riga').'">
                                <i class="fa fa-plus"></i> '.tr('Riga').'
                            </a>

                            <div class="btn-group tip" data-toggle="tooltip">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="fa fa-list"></i> '.tr('Altro').'
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">';
    /*<li>
        <a style="cursor:pointer" onclick="gestioneDescrizione(this)" data-title="'.tr('Aggiungi descrizione').'">
            <i class="fa fa-plus"></i> '.tr('Descrizione').'
        </a>
    </li>*/
    echo '
                                    <li>
                                        <a style="cursor:pointer" onclick="gestioneSconto(this)" data-title="'.tr('Aggiungi sconto/maggiorazione').'">
                                            <i class="fa fa-plus"></i> '.tr('Sconto/maggiorazione').'
                                        </a>
                                    </li>

                                    <li title="'.tr("L'aggiunta del documento secondo questa procedura non associa l'attività al relativo consuntivo del documento: utilizzare i campi soprastanti a questo fine").'.">
                                        <a class="'.(!empty($preventivi) ? '' : ' disabled').'" style="cursor:pointer" data-href="'.$structure->fileurl('add_preventivo.php').'?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="'.tr('Aggiungi Preventivo').'" onclick="saveForm()">
                                            <i class="fa fa-plus"></i> '.tr('Preventivo').'
                                        </a>
                                    </li>

                                    <li title="'.tr("L'aggiunta del documento secondo questa procedura non associa l'attività al relativo consuntivo del documento: utilizzare i campi soprastanti a questo fine").'.">
                                        <a class="'.(!empty($contratti) ? '' : ' disabled').'" style="cursor:pointer" data-href="'.$structure->fileurl('add_contratto.php').'?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="'.tr('Aggiungi Contratto').'" onclick="saveForm()">
                                            <i class="fa fa-plus"></i> '.tr('Contratto').'
                                        </a>
                                    </li>
                                    
                                    <li title="'.tr('DDT in uscita per il Cliente che si trovano nello stato di Evaso o Parzialmente Evaso con una Causale importabile').'. '.tr("L'aggiunta del documento secondo questa procedura non associa l'attività al relativo consuntivo del documento: utilizzare i campi soprastanti a questo fine").'.">
                                        <a class="'.(!empty($ddt) ? '' : ' disabled').'" style="cursor:pointer" data-href="'.$structure->fileurl('add_ddt.php').'?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="'.tr('Aggiungi Ddt').'" onclick="saveForm()">
                                            <i class="fa fa-plus"></i> '.tr('Ddt').'
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>';
}

?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12" id="righe"></div>
        </div>
    </div>
</div>

<!-- COSTI TOTALI -->
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo tr('Costi totali'); ?></h3>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-12" id="costi"></div>
        </div>
    </div>
</div>

{( "name": "filelist_and_upload", "id_module": "$id_module$", "id_record": "$id_record$", <?php echo ($record['flag_completato']) ? '"readonly": 1' : '"readonly": 0'; ?> )}

<!-- EVENTUALE FIRMA GIA' EFFETTUATA -->
<div class="text-center row">
	<div class="col-md-12" >
	    <?php
        if ($record['firma_file'] == '') {
            echo '
	    <div class="alert alert-warning"><i class="fa fa-warning"></i> '.tr('Questo intervento non è ancora stato firmato dal cliente').'.</div>';
        } else {
            echo '
	    <img src="'.base_path().'/files/interventi/'.$record['firma_file'].'" class="img-thumbnail"><div>&nbsp;</div>
	   	<div class="col-md-6 col-md-offset-3 alert alert-success"><i class="fa fa-check"></i> '.tr('Firmato il _DATE_ alle _TIME_ da _PERSON_', [
            '_DATE_' => Translator::dateToLocale($record['firma_data']),
            '_TIME_' => Translator::timeToLocale($record['firma_data']),
            '_PERSON_' => (!empty($record['firma_nome']) ? $record['firma_nome'] : $intervento->anagrafica->ragione_sociale),
        ]).'</div>';
        }

echo '
	</div>
</div>

{( "name": "log_email", "id_module": "$id_module$", "id_record": "$id_record$" )}

<script>
async function saveForm() {
    // Salvataggio via AJAX
    await salvaForm("#edit-form");
}

function gestioneSconto(button) {
    gestioneRiga(button, "is_sconto");
}

function gestioneDescrizione(button) {
    gestioneRiga(button, "is_descrizione");
}

async function gestioneRiga(button, options) {
    // Salvataggio via AJAX
    await salvaForm("#edit-form", {}, button);

    // Lettura titolo e chiusura tooltip
    let title = $(button).attr("data-title");

    // Apertura modal
    options = options ? options : "is_riga";
    openModal(title, "'.$structure->fileurl('row-add.php').'?id_module='.$id_module.'&id_record='.$id_record.'&" + options);
}

/**
 * Funzione dedicata al caricamento dinamico via AJAX delle righe del documento.
 */
function caricaRighe(id_riga) {
    let container = $("#righe");

    localLoading(container, true);
    return $.get("'.$structure->fileurl('row-list.php').'?id_module='.$id_module.'&id_record='.$id_record.'", function(data) {
        container.html(data);
        localLoading(container, false);
        if (id_riga != null) {
            $("tr[data-id="+ id_riga +"]").effect("highlight",1000);
        }
    });
}

/**
 * Funzione dedicata al caricamento dinamico via AJAX delle sessioni dei tecnici per l\'Attività.
 */
function caricaTecnici() {
    let container = $("#tecnici");

    localLoading(container, true);
    return $.get("'.$structure->fileurl('ajax_tecnici.php').'?id_module='.$id_module.'&id_record='.$id_record.'", function(data) {
        caricaRighe(null);
        container.html(data);
        localLoading(container, false);
    });
}

/**
 * Funzione dedicata al caricamento dinamico via AJAX delle sessioni dei tecnici per l\'Attività.
 */
function caricaCosti() {
    let container = $("#costi");

    localLoading(container, true);
    return $.get("'.$structure->fileurl('ajax_costi.php').'?id_module='.$id_module.'&id_record='.$id_record.'", function(data) {
        container.html(data);
        localLoading(container, false);
    });
}

$(document).ready(function() {
    caricaRighe(null);
    caricaTecnici();
    caricaCosti();

    $("#id_articolo").on("change", function(e) {
        if ($(this).val()) {
            var data = $(this).selectData();

            if (data.barcode) {
                $("#barcode").val(data.barcode);
            } else {
                $("#barcode").val("");
            }
        }

        e.preventDefault();

        setTimeout(function(){
            $("#barcode").focus();
        }, 100);
    });

    $("#barcode").focus();
});

    var anagrafica = input("idanagrafica");
    var sede = input("idsede_destinazione");
    var contratto = input("idcontratto");
    var preventivo = input("idpreventivo");
    var ordine = input("idordine");
    var cliente_finale = input("idclientefinale");
    var referente = input("idreferente");

    // Gestione della modifica dell\'anagrafica
	anagrafica.change(function() {
        updateSelectOption("idanagrafica", $(this).val());
        session_set("superselect,idanagrafica", $(this).val(), 0);

        let value = !$(this).val();
        let placeholder = value ? "'.tr('Seleziona prima un cliente').'" : "'.tr("Seleziona un'opzione").'";

        referente.getElement()
            .selectReset(placeholder);

        sede.setDisabled(value)
            .getElement().selectReset(placeholder);

        preventivo.setDisabled(value)
            .getElement().selectReset(placeholder);

        contratto.setDisabled(value)
            .getElement().selectReset(placeholder);

        ordine.setDisabled(value)
            .getElement().selectReset(placeholder);

        input("idimpianti").setDisabled(value);

        let data = anagrafica.getData();
		if (data) {
		    input("idzona").set(data.idzona ? data.idzona : "");
			// session_set("superselect,idzona", $(this).selectData().idzona, 0);

            // Impostazione del tipo intervento da anagrafica
            if (data.idtipointervento) {
                input("idtipointervento").getElement()
                    .selectSet(data.idtipointervento);
            }
		}
    });

    //gestione del cliente finale
    cliente_finale.change(function() {
        updateSelectOption("idclientefinale", $(this).val());
        session_set("superselect,idclientefinale", $(this).val(), 0);

        referente.getElement()
            .selectReset("'.tr("Seleziona un'opzione").'");
    });

    // Gestione della modifica della sede selezionato
	sede.change(function() {
        updateSelectOption("idsede_destinazione", $(this).val());
		session_set("superselect,idsede_destinazione", $(this).val(), 0);
        input("idimpianti").getElement().selectReset();
        input("idreferente").getElement().selectReset();
        
        let data = sede.getData();
		if (data) {
		    input("idzona").set(data.idzona ? data.idzona : "");
			// session_set("superselect,idzona", $(this).selectData().idzona, 0);
		}
	});

    // Gestione della modifica dell\'ordine selezionato
	ordine.change(function() {
		if (ordine.get()) {
            contratto.getElement().selectReset();
            preventivo.getElement().selectReset();
        }
	});

    // Gestione della modifica del preventivo selezionato
	preventivo.change(function() {
		if (preventivo.get()){
            contratto.getElement().selectReset();
            ordine.getElement().selectReset();

             // Impostazione del tipo intervento da preventivo
            var data = $(this).selectData()
            if (data.idtipointervento) {
                input("idtipointervento").getElement()
                    .selectSet(data.idtipointervento);
            }
        }
	});

    // Gestione della modifica del contratto selezionato
	contratto.change(function() {
		if (contratto.get()){
            preventivo.getElement().selectReset();
            ordine.getElement().selectReset();

            $("input[name=idcontratto_riga]").val("");
        }
	});

    // Gestione delle modifiche agli impianti selezionati
	input("idimpianti").change(function() {
        updateSelectOption("matricola", $(this).val());
		session_set("superselect,matricola", $(this).val(), 0);

        input("componenti").setDisabled(!$(this).val())
            .getElement().selectReset();
	});

    $("#codice_cig, #codice_cup").bind("keyup change", function (e) {
        if ($("#codice_cig").val() == "" && $("#codice_cup").val() == "") {
            $("#id_documento_fe").prop("required", false);
        } else {
            $("#id_documento_fe").prop("required", true);
        }
    });

    async function salvaArticolo() {
        // Salvataggio via AJAX
        await salvaForm("#edit-form");
        
        $("#link_form").ajaxSubmit({
            url: globals.rootdir + "/actions.php",
            data: {
                id_module: globals.id_module,
                id_record: globals.id_record,
                ajax: true,
            },
            type: "post",
            beforeSubmit: function(arr, $form, options) {
                return $form.parsley().validate();
            },
            success: function(response){
                renderMessages();
                if(response.length > 0){
                    response = JSON.parse(response);
                    swal({
                        type: "error",
                        title: "'.tr('Errore').'",
                        text: response.error,
                    });
                }
    
                $("#barcode").val("");
                $("#id_articolo").selectReset();
                content_was_modified = false;
                caricaRighe(null);
                caricaCosti();
            }
        });
    }
    
    $("#link_form").bind("keypress", function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            salvaArticolo();
            return false;
        }
    });
</script>';

// Collegamenti diretti
// Fatture collegate a questo intervento
$elementi = $dbo->fetchArray('SELECT `co_documenti`.*, `co_tipidocumento_lang`.`name` AS tipo_documento, `co_statidocumento_lang`.`name` AS stato_documento, `co_tipidocumento`.`dir` FROM `co_documenti` INNER JOIN `co_tipidocumento` ON `co_tipidocumento`.`id` = `co_documenti`.`idtipodocumento` LEFT JOIN `co_tipidocumento_lang` ON (`co_tipidocumento_lang`.`idtipodocumento` = `co_documenti`.`idtipodocumento` AND `co_tipidocumento_lang`.`lang` = '.prepare(\App::getLang()).') INNER JOIN `co_statidocumento` ON `co_statidocumento`.`id` = `co_documenti`.`idstatodocumento` LEFT JOIN `co_statidocumento_lang` ON (`co_statidocumento_lang`.`idstatodocumento` = `co_documenti`.`idstatodocumento` AND `co_statidocumento_lang`.`lang` = '.prepare(\App::getLang()).') WHERE `co_documenti`.`id` IN (SELECT `iddocumento` FROM `co_righe_documenti` WHERE `idintervento` = '.prepare($id_record).') ORDER BY `data`');

if (!empty($elementi)) {
    echo '
<div class="box box-warning collapsable collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-warning"></i> '.tr('Documenti collegati: _NUM_', [
            '_NUM_' => count($elementi),
        ]).'</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <ul>';

    foreach ($elementi as $fattura) {
        $descrizione = tr('_DOC_ num. _NUM_ del _DATE_ [_STATE_]', [
            '_DOC_' => $fattura['tipo_documento'],
            '_NUM_' => !empty($fattura['numero_esterno']) ? $fattura['numero_esterno'] : $fattura['numero'],
            '_DATE_' => Translator::dateToLocale($fattura['data']),
            '_STATE_' => $fattura['stato_documento'],
        ]);

        $modulo = ($fattura['dir'] == 'entrata') ? 'Fatture di vendita' : 'Fatture di acquisto';
        $id = $fattura['id'];

        echo '
            <li>'.Modules::link($modulo, $id, $descrizione).'</li>';
    }

    echo '
        </ul>
    </div>
</div>';
}

if (!empty($elementi)) {
    echo '
<div class="alert alert-error">
    '.tr('Eliminando questo documento si potrebbero verificare problemi nelle altre sezioni del gestionale').'.
</div>';
}

?>

<a class="btn btn-danger ask" data-backto="record-list">
    <i id ="elimina" class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
</a>
