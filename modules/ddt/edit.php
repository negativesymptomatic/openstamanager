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

$module = Module::find($id_module);

if ($module->name == 'Ddt di acquisto') {
    $dir = 'uscita';
} else {
    $dir = 'entrata';
}

if ($dir == 'entrata') {
    $numero_previsto = verifica_numero_ddt($ddt);
    if (!empty($numero_previsto)) {
        echo '
        <div class="alert alert-warning">
            <i class="fa fa-warning"></i> '.tr("E' assente un _TYPE_ numero _NUM_ in data precedente o corrispondente a _DATE_: si potrebbero verificare dei problemi con la numerazione corrente dei DDT", [
                    '_TYPE_' => $module['name'],
                    '_DATE_' => dateFormat($ddt->data),
                    '_NUM_' => '"'.$numero_previsto.'"',
                ]).'.</b>
        </div>';
    }

    $rs2 = $dbo->fetchArray('SELECT piva, codice_fiscale, citta, indirizzo, cap, provincia FROM an_anagrafiche WHERE idanagrafica='.prepare($record['idanagrafica']));
    $campi_mancanti = [];
    if ($rs2[0]['piva'] == '') {
        if ($rs2[0]['codice_fiscale'] == '') {
            array_push($campi_mancanti, 'codice fiscale');
        }
    }
    if ($rs2[0]['citta'] == '') {
        array_push($campi_mancanti, 'citta');
    }
    if ($rs2[0]['indirizzo'] == '') {
        array_push($campi_mancanti, 'indirizzo');
    }
    if ($rs2[0]['cap'] == '') {
        array_push($campi_mancanti, 'C.A.P.');
    }

    if (sizeof($campi_mancanti) > 0) {
        echo "<div class='alert alert-warning'><i class='fa fa-warning'></i> Prima di procedere alla stampa completa i seguenti campi dell'anagrafica:<br/><b>".implode(', ', $campi_mancanti).'</b><br/>
        '.Modules::link('Anagrafiche', $record['idanagrafica'], tr('Vai alla scheda anagrafica'), null).'</div>';
    }
}


$righe = $ddt->getRighe();
$righe_vuote = false;
foreach ($righe as $riga) {
    if ($riga->qta == 0) {
        $righe_vuote = true;
    }
}
if ($righe_vuote) {
        echo '
    <div class="alert alert-warning" id="righe-vuote">
        <i class="fa fa-warning"></i> '.tr("Nel ddt sono presenti delle righe con quantità a 0.").'</b>
    </div>';
}

?>
<form action="" method="post" id="edit-form">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="update">
	<input type="hidden" name="id_record" value="<?php echo $id_record; ?>">

    <div class="row">
        <div class="col-md-8">
            <!-- INTESTAZIONE -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo tr('Intestazione'); ?></h3>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <?php
                            if ($dir == 'uscita') {
                                echo '
                        <div class="col-md-6">
                            {[ "type": "span", "label": "'.tr('Numero ddt').'", "class": "text-center", "value": "$numero$" ]}
                        </div>';
                            }
?>

                        <div class="col-md-6">
                            {[ "type": "text", "label": "<?php echo tr('Numero secondario'); ?>", "name": "numero_esterno", "class": "text-center", "value": "$numero_esterno$" ]}
                        </div>

                        <div class="col-md-6">
                            {[ "type": "date", "label": "<?php echo tr('Data'); ?>", "name": "data", "required": 1, "value": "$data$" ]}
                        </div>

                        <div class="col-md-6">
                            <?php
    if (setting('Cambia automaticamente stato ddt fatturati')) {
        if ($record['stato'] == 'Fatturato' || $record['stato'] == 'Parzialmente fatturato') {
            ?>
                                    {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "idstatoddt", "required": 1, "values": "query=SELECT *, `dt_statiddt_lang`.`name` as descrizione, `colore` AS _bgcolor_ FROM `dt_statiddt` LEFT JOIN `dt_statiddt_lang` ON (`dt_statiddt`.`id` = `dt_statiddt_lang`.`id_record` AND `dt_statiddt_lang`.`id_lang`= <?php echo prepare(\App::getLang()); ?>) ORDER BY `name`", "value": "$idstatoddt$", "extra": "readonly", "class": "unblockable" ]}
                            <?php
        } else {
            ?>
                                    {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "idstatoddt", "required": 1, "values": "query=SELECT *, `dt_statiddt_lang`.`name` as descrizione, `colore` AS _bgcolor_ FROM `dt_statiddt` LEFT JOIN `dt_statiddt_lang` ON (`dt_statiddt`.`id` = `dt_statiddt_lang`.`id_record` AND `dt_statiddt_lang`.`id_lang`= <?php echo prepare(\App::getLang()); ?>) WHERE `name` IN('Bozza', 'Evaso', 'Parzialmente evaso') ORDER BY `name`", "value": "$idstatoddt$", "class": "unblockable" ]}
                            <?php
        }
    } else {
        ?>
                            {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "idstatoddt", "required": 1, "values": "query=SELECT *, `colore` AS _bgcolor_, `dt_statiddt_lang`.`name` as descrizione FROM `dt_statiddt` LEFT JOIN `dt_statiddt_lang` ON (`dt_statiddt`.`id` = `dt_statiddt_lang`.`id_record` AND `dt_statiddt_lang`.`id_lang`= <?php echo prepare(\App::getLang()); ?>) ORDER BY `name`", "value": "$idstatoddt$", "class": "unblockable" ]}
                            <?php
    }
?>
                        </div>
<?php
                    if ($dir == 'entrata') {
                        echo '
                        <div class="col-md-6">';
                        if ($record['idagente'] != 0) {
                            echo Modules::link('Anagrafiche', $record['idagente'], null, null, 'class="pull-right"');
                        }
                        echo '
                            {[ "type": "select", "label": "'.tr('Agente').'", "name": "idagente", "ajax-source": "agenti", "select-options": {"idanagrafica": '.$record['idanagrafica'].'}, "value": "$idagente$" ]}
                        </div>';
                    }
?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php echo Modules::link('Anagrafiche', $record['idanagrafica'], null, null, 'class="pull-right"'); ?>
                            {[ "type": "select", "label": "<?php echo ($dir == 'uscita') ? tr('Mittente') : tr('Destinatario'); ?>", "name": "idanagrafica", "required": 1, "value": "$idanagrafica$", "ajax-source": "clienti_fornitori" ]}
                        </div>
<?php
    echo '
                        <div class="col-md-6">';
if (!empty($record['idreferente'])) {
    echo Plugins::link('Referenti', $record['idanagrafica'], null, null, 'class="pull-right"');
}
echo '
                            {[ "type": "select", "label": "'.tr('Referente').'", "name": "idreferente", "value": "$idreferente$", "ajax-source": "referenti", "select-options": {"idanagrafica": '.$record['idanagrafica'].', "idsede_destinazione": '.$record['idsede_destinazione'].'} ]}
                        </div>';

// Conteggio numero articoli ddt in uscita
$articolo = $dbo->fetchArray('SELECT `mg_articoli`.`id` FROM ((`mg_articoli` INNER JOIN `dt_righe_ddt` ON `mg_articoli`.`id`=`dt_righe_ddt`.`idarticolo`) INNER JOIN `dt_ddt` ON `dt_ddt`.`id`=`dt_righe_ddt`.`idddt`) WHERE `dt_ddt`.`id`='.prepare($id_record));
$id_modulo_anagrafiche = (new Module())->getByName('Anagrafiche')->id_record;
$id_plugin_sedi = (new Plugin())->getByName('Sedi')->id_record;
if ($dir == 'entrata') {
    echo '
                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Partenza merce').'", "name": "idsede_partenza", "ajax-source": "sedi_azienda", "value": "$idsede_partenza$", "help": "'.tr("Sedi di partenza dell'azienda").'" ]}
                        </div>

                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Destinazione merce').'", "name": "idsede_destinazione", "ajax-source": "sedi", "select-options": {"idanagrafica": '.$record['idanagrafica'].'}, "value": "$idsede_destinazione$", "help": "'.tr('Sedi del destinatario').'", "icon-after": "add|'.$id_modulo_anagrafiche.'|id_plugin='.$id_plugin_sedi.'&id_parent='.$record['idanagrafica'].'||'.(intval($block_edit) ? 'disabled' : '').'" ]}
                        </div>';
} else {
    echo '
                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Partenza merce').'", "name": "idsede_partenza", "ajax-source": "sedi", "select-options": {"idanagrafica": '.$record['idanagrafica'].'}, "value": "$idsede_partenza$", "help": "'.tr('Sedi del mittente').'", "icon-after": "add|'.$id_modulo_anagrafiche.'|id_plugin='.$id_plugin_sedi.'&id_parent='.$record['idanagrafica'].'||'.(intval($block_edit) ? 'disabled' : '').'" ]}
                        </div>

                        <div class="col-md-6">
                            {[ "type": "select", "label": "'.tr('Destinazione merce').'", "name": "idsede_destinazione", "ajax-source": "sedi_azienda", "value": "$idsede_destinazione$", "help": "'.tr("Sedi di arrivo dell'azienda").'" ]}
                        </div>';
}
?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <?php
            $sede_anagrafica = $ddt->anagrafica->sedeLegale;
$id_sede_anagrafica = $dir == 'entrata' ? $ddt->idsede_destinazione : $ddt->idsede_partenza;
if (!empty($id_sede_anagrafica)) {
    $sede_anagrafica = Sede::find($id_sede_anagrafica);
}

$anagrafica_azienda = Anagrafica::find(setting('Azienda predefinita'));
$sede_azienda = $anagrafica_azienda->sedeLegale;
$id_sede_azienda = $dir == 'entrata' ? $ddt->idsede_partenza : $ddt->idsede_destinazione;
if (!empty($id_sede_azienda)) {
    $sede_azienda = Sede::find($id_sede_azienda);
}
?>

            <!-- GEOLOCALIZZAZIONE -->
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-map"></i> <?php echo tr('Geolocalizzazione'); ?></h3>
                </div>
                <div class="panel-body">
                <?php
    if (!empty($sede_anagrafica->gaddress) || (!empty($sede_anagrafica->lat) && !empty($sede_anagrafica->lng))) {
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
                        '.((!empty($sede_anagrafica->lat)) ? tr(' (GPS)') : '').'
                    </a>';
    }

echo '
                </div>
            </div>

            <script>
                function modificaPosizione() {
                    openModal("'.tr('Modifica posizione').'", "'.$module->fileurl('modals/posizione.php').'?id_module='.$id_module.'&id_record='.$id_record.'");
                }

                function cercaOpenStreetMap() {
                    const indirizzo = getIndirizzoAnagrafica();
                    if (indirizzo[0] && indirizzo[1]) {
                        window.open("https://www.openstreetmap.org/?mlat=" + indirizzo[0] + "&mlon=" + indirizzo[1] + "#map=12/" + indirizzo[0] + "/" + indirizzo[1]);
                    } else {
                        window.open("https://www.openstreetmap.org/search?query=" + indirizzo[2]);
                    }
                }

                function calcolaPercorso() {
                    const indirizzo_partenza = getIndirizzoAzienda();
                    const indirizzo_destinazione = getIndirizzoAnagrafica();
                    window.open("https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=" + indirizzo_partenza + ";" + indirizzo_destinazione[0] + "," + indirizzo_destinazione[1]);
                }

                function getIndirizzoAzienda() {
                    const indirizzo = "'.$sede_azienda->indirizzo.'";
                    const citta = "'.$sede_azienda->citta.'";

                    const lat = parseFloat("'.$sede_azienda->lat.'");
                    const lng = parseFloat("'.$sede_azienda->lng.'");

                    return lat + "," + lng;
                }

                function getIndirizzoAnagrafica() {
                    const indirizzo = "'.$sede_anagrafica->indirizzo.'";
                    const citta = "'.$sede_anagrafica->citta.'";

                    const lat = parseFloat("'.$sede_anagrafica->lat.'");
                    const lng = parseFloat("'.$sede_anagrafica->lng.'");

                    const indirizzo_default = encodeURI(indirizzo) + "," + encodeURI(citta);

                    return [lat, lng, indirizzo_default];
                }

                var map = null;
                function caricaMappa() {
                    const lat = parseFloat("'.$sede_anagrafica->lat.'");
                    const lng = parseFloat("'.$sede_anagrafica->lng.'");
                
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
        </div>
    </div>

     <!-- DATI DDT -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo tr('Dati ddt'); ?></h3>
        </div>

        <div class="panel-body">
			<div class="row">
				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Aspetto beni'); ?>", "name": "idaspettobeni", "value": "$idaspettobeni$", "ajax-source": "aspetto-beni", "icon-after": "add|<?php echo (new Module())->getByName('Aspetto beni')->id_record;; ?>|||<?php echo $block_edit ? 'disabled' : ''; ?>" ]}
				</div>

				<div class="col-md-3">
                    <?php
            if (!empty($record['idcausalet'])) {
                echo Modules::link('Causali', $record['idcausalet'], null, null, 'class="pull-right"');
            }
?>
					{[ "type": "select", "label": "<?php echo tr('Causale trasporto'); ?>", "name": "idcausalet", "required": 1, "value": "$idcausalet$", "ajax-source": "causali", "icon-after": "add|<?php echo(new Module())->getByName('Causali')->id_record;; ?>|||<?php echo $block_edit ? 'disabled' : ''; ?>", "help": "<?php echo tr('Definisce la causale del trasporto'); ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Tipo di spedizione'); ?>", "name": "idspedizione", "placeholder": "-", "values": "query=SELECT `dt_spedizione`.`id`, `dt_spedizione_lang`.`name` as `descrizione`, `esterno` FROM `dt_spedizione` LEFT JOIN `dt_spedizione_lang` ON (`dt_spedizione_lang`.`id_record` = `dt_spedizione`.`id` AND `dt_spedizione_lang`.`id_lang` = <?php echo prepare(\App::getLang()); ?>) ORDER BY `name` ASC", "value": "$idspedizione$" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "text", "label": "<?php echo tr('Num. colli'); ?>", "name": "n_colli", "value": "$n_colli$" ]}
				</div>
			</div>

			<div class="row">
				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Pagamento'); ?>", "name": "idpagamento", "ajax-source": "pagamenti", "value": "$idpagamento$" ]}
				</div>

                <div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Porto'); ?>", "name": "idporto", "placeholder": "-", "help": "<?php echo tr('<ul><li>Franco: pagamento del trasporto a carico del mittente</li> <li>Assegnato: pagamento del trasporto a carico del destinatario</li> </ul>'); ?>", "values": "query=SELECT `dt_porto`.`id`, `dt_porto_lang`.`name` as descrizione FROM `dt_porto` LEFT JOIN `dt_porto_lang` ON (`dt_porto`.`id` = `dt_porto_lang`.`id_record` AND `dt_porto_lang`.`id_lang` = <?php echo prepare(\App::getLang()); ?>) ORDER BY `name` ASC", "value": "$idporto$" ]}
				</div>

				<div class="col-md-3">
                    <?php
    if (!empty($record['idvettore'])) {
        echo Modules::link('Anagrafiche', $record['idvettore'], null, null, 'class="pull-right"');
    }
$esterno = $dbo->selectOne('dt_spedizione', 'esterno', [
    'id' => $record['idspedizione'],
])['esterno'];
?>
					{[ "type": "select", "label": "<?php echo tr('Vettore'); ?>", "name": "idvettore", "ajax-source": "vettori", "value": "$idvettore$", "disabled": <?php echo empty($esterno) || (!empty($esterno) && !empty($record['idvettore'])) ? 1 : 0; ?>, "required": <?php echo !empty($esterno) ?: 0; ?>, "icon-after": "add|<?php echo (new Module())->getByName('Anagrafiche')->id_record;; ?>|tipoanagrafica=Vettore&readonly_tipo=1|btn_idvettore|<?php echo ($esterno and (intval(!$record['flag_completato']) || empty($record['idvettore']))) ? '' : 'disabled'; ?>", "class": "<?php echo empty($record['idvettore']) ? 'unblockable' : ''; ?>" ]}
				</div>

                <div class="col-md-3">
					{[ "type": "timestamp", "label": "<?php echo tr('Data ora trasporto'); ?>", "name": "data_ora_trasporto", "value": "$data_ora_trasporto$", "help": "<?php echo tr('Data e ora inizio del trasporto'); ?>" ]}
				</div>

                 <script>
                    $("#idspedizione").change(function() {
                        if($(this).val()){
                            if (!$(this).selectData().esterno) {
                                $("#idvettore").attr("required", false);
                                input("idvettore").disable();
                                $("label[for=idvettore]").text("<?php echo tr('Vettore'); ?>");
                                $("#idvettore").selectReset("<?php echo tr("Seleziona un\'opzione"); ?>");
                                $(".btn_idvettore").prop("disabled", true);
                                $(".btn_idvettore").addClass("disabled");
                            }else{
                                $("#idvettore").attr("required", true);
                                input("idvettore").enable();
                                $("label[for=idvettore]").text("<?php echo tr('Vettore'); ?>*");
                                $(".btn_idvettore").prop("disabled", false);
                                $(".btn_idvettore").removeClass("disabled");

                            }
                        } else{
                            $("#idvettore").attr("required", false);
                            input("idvettore").disable();
                            $("label[for=idvettore]").text("<?php echo tr('Vettore'); ?>");
                            $("#idvettore").selectReset("<?php echo tr("Seleziona un\'opzione"); ?>");
                            $(".btn_idvettore").prop("disabled", true);
                            $(".btn_idvettore").addClass("disabled");
                        }
                    });

                    $("#idcausalet").change(function() {
                        if ($(this).val() == 3) {
                            $("#tipo_resa").attr("disabled", false);
                        }else{
                            $("#tipo_resa").attr("disabled", true);
                        }
                    });
                </script>
			</div>
<?php

if ($dir == 'entrata') {
    echo '
        <div class="row">
            <div class="col-md-3">
                {[ "type": "number", "label": "'.tr('Peso').'", "name": "peso", "value": "$peso$", "readonly": "'.intval(empty($record['peso_manuale'])).'", "help": "'.tr('Il valore del campo Peso viene calcolato in automatico sulla base degli articoli inseriti nel documento, a meno dell\'impostazione di un valore manuale in questo punto').'" ]}
                <input type="hidden" id="peso_calcolato" name="peso_calcolato" value="'.$ddt->peso_calcolato.'">
            </div>

            <div class="col-md-3">
                {[ "type": "checkbox", "label": "'.tr('Modifica peso').'", "name": "peso_manuale", "value":"$peso_manuale$", "help": "'.tr('Seleziona per modificare manualmente il campo Peso').'", "placeholder": "'.tr('Modifica peso').'" ]}
            </div>

            <div class="col-md-3">
                {[ "type": "number", "label": "'.tr('Volume').'", "name": "volume", "value": "$volume$", "readonly": "'.intval(empty($record['volume_manuale'])).'", "help": "'.tr('Il valore del campo volume viene calcolato in automatico sulla base degli articoli inseriti nel documento, a meno dell\'impostazione di un valore manuale in questo punto').'" ]}
                <input type="hidden" id="volume_calcolato" name="volume_calcolato" value="'.$ddt->volume_calcolato.'">
            </div>

            <div class="col-md-3">
                {[ "type": "checkbox", "label": "'.tr('Modifica volume').'", "name": "volume_manuale", "value":"$volume_manuale$", "help": "'.tr('Seleziona per modificare manualmente il campo volume').'", "placeholder": "'.tr('Modifica volume').'" ]}
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                {[ "type": "number", "label": "Sconto in fattura", "name": "sconto_finale", "value": "'.($ddt->sconto_finale_percentuale ?: $ddt->sconto_finale).'", "icon-after": "choice|untprc|'.(empty($ddt->sconto_finale) ? 'PRC' : 'UNT').'", "help": "'.tr('Sconto in fattura, utilizzabile per applicare sconti sul netto a pagare del documento').'." ]}
            </div>
        </div>';
}

?>
			<div class="row">
				<div class="col-md-6">
					{[ "type": "textarea", "label": "<?php echo tr('Note'); ?>", "name": "note", "value": "$note$" ]}
				</div>
                <div class="col-md-6">
                    {[ "type": "textarea", "label": "<?php echo tr('Note aggiuntive'); ?>", "name": "note_aggiuntive", "help": "<?php echo tr('Note interne.'); ?>", "value": "$note_aggiuntive$" ]}
                </div>
            </div>
		</div>
	</div>

    <?php
    if (!empty($record['id_documento_fe']) || !empty($record['num_item']) || !empty($record['codice_cig']) || !empty($record['codice_cup'])) {
        $collapsed = '';
    } else {
        $collapsed = ' collapsed-box';
    }
?>

    <!-- Fatturazione Elettronica PA-->

    <div class="box box-primary collapsable  <?php echo ($record['tipo_anagrafica'] == 'Ente pubblico' || $record['tipo_anagrafica'] == 'Azienda') ? 'show' : 'hide'; ?> <?php echo $collapsed; ?>">
        <div class=" box-header">
            <h4 class=" box-title">
                
                <?php echo tr('Dati appalto'); ?></h4>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-plus"></i>
                    </button>
                </div>
            
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    {[ "type": "text", "label": "<?php echo tr('Identificatore Documento'); ?>", "name": "id_documento_fe", "required": 0, "help": "<?php echo tr('<span>Obbligatorio per valorizzare CIG/CUP. &Egrave; possible inserire: </span><ul><li>N. determina</li><li>RDO</li><li>Ordine MEPA</li></ul>'); ?>", "value": "$id_documento_fe$", "maxlength": 20 ]}
                </div>

                <div class="col-md-6">
                    {[ "type": "text", "label": "<?php echo tr('Numero Riga'); ?>", "name": "num_item", "required": 0, "value": "$num_item$", "maxlength": 15 ]}
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    {[ "type": "text", "label": "<?php echo tr('Codice CIG'); ?>", "name": "codice_cig", "required": 0, "value": "$codice_cig$", "maxlength": 15 ]}
                </div>

                <div class="col-md-6">
                    {[ "type": "text", "label": "<?php echo tr('Codice CUP'); ?>", "name": "codice_cup", "required": 0, "value": "$codice_cup$", "maxlength": 15 ]}
                </div>
            </div>
        </div>
    </div>
</form>

<!-- RIGHE -->
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo tr('Righe'); ?></h3>
	</div>

	<div class="panel-body">
<?php

if (!$block_edit) {
    // Lettura ordini (cliente o fornitore)
    $ordini_query = 'SELECT 
            COUNT(*) AS tot 
        FROM 
            `or_ordini` 
            INNER JOIN `or_righe_ordini` ON `or_ordini`.`id` = `or_righe_ordini`.`idordine` 
            INNER JOIN `or_statiordine` ON `or_ordini`.`idstatoordine`=`or_statiordine`.`id` 
            INNER JOIN `or_tipiordine` ON `or_ordini`.`idtipiordine`=`or_tipiordine`.`id` 
            LEFT JOIN `or_statiordine_lang` ON (`or_statiordine`.`id` = `or_statiordine_lang`.`id_record` AND `or_statiordine_lang`.`id_lang` = '.prepare(\App::getLang()).')
        WHERE 
            `idanagrafica`='.prepare($record['idanagrafica']).' 
            AND `or_statiordine_lang`.`name` IN(\'Accettato\', \'Evaso\', \'Parzialmente evaso\', \'Parzialmente fatturato\')) 
            AND `or_tipiordine`.`dir`='.prepare($dir).') 
            AND (`or_righe_ordini`.`qta` - `or_righe_ordini`.`qta_evasa`) > 0)
        GROUP BY `or_ordini`.`id`';
    $tot_ordini = $dbo->fetchArray($ordini_query)[0]['tot'];

    $ddt_query = 'SELECT 
            COUNT(*) AS tot 
        FROM 
            `dt_ddt` 
            INNER JOIN `dt_statiddt` ON `dt_ddt`.`idstatoddt` = `dt_statiddt`.`id`
            LEFT JOIN `dt_statiddt_lang` ON (`dt_statiddt_lang`.`id_record` = `dt_statiddt`.`id` AND `dt_statiddt_lang`.`id_lang` = '.prepare(\App::getLang()).')
            INNER JOIN `dt_tipiddt` ON `dt_ddt`.`idtipoddt` = `dt_tipiddt`.`id`
            INNER JOIN `dt_righe_ddt` ON `dt_righe_ddt`.`idddt` = `dt_ddt`.`id`
        WHERE 
            `name` IN("Evaso", "Parzialmente evaso", "Parzialmente fatturato") AND 
            `dt_tipiddt`.`dir`="'.($dir == 'entrata' ? 'uscita' : 'entrata').'") AND 
            (`dt_righe_ddt`.`qta` - `dt_righe_ddt`.`qta_evasa`) > 0
        GROUP BY `dt_ddt`.`id`';
    $tot_ddt = $dbo->fetchArray($ddt_query)[0]['tot'];

    // Form di inserimento riga documento
    echo '
        <form id="link_form" action="" method="post">
            <input type="hidden" name="op" value="add_articolo">
            <input type="hidden" name="backto" value="record-edit">

            <div class="row">
                <div class="col-md-3">
                    {[ "type": "text", "label": "'.tr('Aggiungi un articolo tramite barcode').'", "name": "barcode", "extra": "autocomplete=\"off\"", "icon-before": "<i class=\"fa fa-barcode\"></i>", "required": 0 ]}
                </div>

                <div class="col-md-4">
                    {[ "type": "select", "label": "'.tr('Articolo').'", "name": "id_articolo", "value": "", "ajax-source": "articoli",  "select-options": {"permetti_movimento_a_zero": '.($dir == 'entrata' ? 0 : 1).', "idsede_partenza": '.intval($ddt->idsede_partenza).', "idsede_destinazione": '.intval($ddt->idsede_destinazione).', "idanagrafica": '.$ddt->idanagrafica.', "dir": "'.$dir.'", "idagente": '.$ddt->idagente.'}, "icon-after": "add|'.(new Module())->getByName('Articoli')->id_record.'" ]}
                </div>

                <div class="col-md-3" style="margin-top: 25px">
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
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a style="cursor:pointer" onclick="gestioneDescrizione(this)" data-title="'.tr('Aggiungi descrizione').'">
                                    <i class="fa fa-plus"></i> '.tr('Descrizione').'
                                </a>
                            </li>

                            <li>
                                <a style="cursor:pointer" onclick="gestioneSconto(this)" data-title="'.tr('Aggiungi sconto/maggiorazione').'">
                                    <i class="fa fa-plus"></i> '.tr('Sconto/maggiorazione').'
                                </a>
                            </li>

                            <li>
                                <a class="'.(!empty($tot_ddt) ? '' : ' disabled').'" style="cursor:pointer" data-href="'.$structure->fileurl('add_ddt.php').'?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="'.tr('Aggiungi Ddt').'" onclick="saveForm()">
                                    <i class="fa fa-plus"></i> '.tr('Ddt').'
                                </a>
                            </li>

                            <li>
                                <a class="'.(!empty($tot_ordini) ? '' : ' disabled').'" style="cursor:pointer" data-href="'.$structure->fileurl('add_ordine.php').'?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="'.tr('Aggiungi Ordine').'" onclick="saveForm()">
                                    <i class="fa fa-plus"></i> '.tr('Ordine').'
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-2">
                    {[ "type": "select", "label": "'.tr('Ordinamento').'", "name": "ordinamento", "class": "no-search", "value": "'.($_SESSION['module_'.$id_module]['order_row_desc'] ? 'desc' : 'manuale').'", "values": "list=\"desc\": \"'.tr('Ultima riga inserita').'\", \"manuale\": \"'.tr('Manuale').'\"" ]}
                </div>
            </div>
        </form>';
}

echo '
		<div class="clearfix"></div>
		<br>

		<div class="row">
			<div class="col-md-12" id="righe"></div>
		</div>
	</div>
</div>

{( "name": "filelist_and_upload", "id_module": "$id_module$", "id_record": "$id_record$" )}

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

$(document).ready(function() {
    caricaRighe(null);

    if(!$("#peso_manuale").is(":checked")){
        input("peso").set($("#peso_calcolato").val());
    }
    $("#peso_manuale").click(function() {
        $("#peso").prop("readonly", !$("#peso_manuale").is(":checked"));
        if(!$("#peso_manuale").is(":checked")){
            input("peso").set($("#peso_calcolato").val());
        }
    });

    if(!$("#volume_manuale").is(":checked")){
        input("volume").set($("#volume_calcolato").val());
    }
    $("#volume_manuale").click(function() {
        $("#volume").prop("readonly", !$("#volume_manuale").is(":checked"));
        if(!$("#volume_manuale").is(":checked")){
            input("volume").set($("#volume_calcolato").val());
        }
    });

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

$("#idanagrafica").change(function() {
    updateSelectOption("idanagrafica", $(this).val());
    session_set("superselect,idanagrafica", $(this).val(), 0);

    $("#idsede_'.($dir == 'uscita' ? 'partenza' : 'destinazione').'").selectReset();
    $("#idpagamento").selectReset();

    let data = $(this).selectData();
	if (data) {
        // Impostazione del tipo di pagamento da anagrafica
        if (data.id_pagamento) {
            input("idpagamento").getElement()
                .selectSetNew(data.id_pagamento, data.desc_pagamento);
        }
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
// Fatture collegate a questo ddt
$elementi = $dbo->fetchArray('SELECT `co_documenti`.`id`, `co_documenti`.`data`, `co_documenti`.`numero`, `co_documenti`.`numero_esterno`, `co_tipidocumento_lang`.`name` AS tipo_documento, IF(`co_tipidocumento`.`dir` = \'entrata\', \'Fatture di vendita\', \'Fatture di acquisto\') AS modulo FROM `co_documenti` INNER JOIN `co_righe_documenti` ON `co_righe_documenti`.`iddocumento` = `co_documenti`.`id` INNER JOIN `co_tipidocumento` ON `co_tipidocumento`.`id` = `co_documenti`.`idtipodocumento` LEFT JOIN `co_tipidocumento_lang` ON (`co_tipidocumento_lang`.`id_record` = `co_tipidocumento`.`id` AND `co_tipidocumento_lang`.`id_lang` = '.prepare(\App::getLang()).') WHERE `co_righe_documenti`.`idddt` = '.prepare($id_record).')

UNION
SELECT `in_interventi`.`id`, `in_interventi`.`data_richiesta`, `in_interventi`.`codice`, NULL, \'Attività\' AS tipo_documento, \'Interventi\' as modulo FROM `in_interventi` JOIN `in_righe_interventi` ON `in_righe_interventi`.`idintervento` = `in_interventi`.`id` WHERE (`in_righe_interventi`.`original_document_id` = '.prepare($id_record).' AND `in_righe_interventi`.`original_document_type` = \'Modules\\\\DDT\\\\DDT\')

ORDER BY `data`');

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

    foreach ($elementi as $elemento) {
        $descrizione = tr('_DOC_ num. _NUM_ del _DATE_', [
            '_DOC_' => $elemento['tipo_documento'],
            '_NUM_' => !empty($elemento['numero_esterno']) ? $elemento['numero_esterno'] : $elemento['numero'],
            '_DATE_' => Translator::dateToLocale($elemento['data']),
        ]);

        echo '
            <li>'.Modules::link($elemento['modulo'], $elemento['id'], $descrizione).'</li>';
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

<?php
// Eliminazione ddt solo se ho accesso alla sede aziendale
$field_name = ($dir == 'entrata') ? 'idsede_partenza' : 'idsede_destinazione';
if (in_array($record[$field_name], $user->sedi)) {
    ?>
    <a class="btn btn-danger ask" data-backto="record-list">
        <i id ="elimina" class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
    </a>
<?php
}

echo '
<script>
$("#idsede_destinazione").change(function(){
    updateSelectOption("idsede_destinazione", $(this).val());
    $("#idreferente").selectReset();
});

input("ordinamento").on("change", function(){
    if (input(this).get() == "desc") {
        session_set("module_'.$id_module.',order_row_desc", 1, "").then(function () {
            caricaRighe(null);
        });
    } else {
        session_set("module_'.$id_module.',order_row_desc").then(function () {
            caricaRighe(null);
        });
    }
});
</script>';
