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
use Modules\Preventivi\Stato;
use Models\Module;

$id_anagrafica = !empty(get('idanagrafica')) ? get('idanagrafica') : '';

$stati = get('pianificabile') ? 'SELECT `co_statipreventivi`.`id`, `co_statipreventivi_lang`.`name` as descrizione FROM `co_statipreventivi`  LEFT JOIN `co_statipreventivi_lang` ON (`co_statipreventivi`.`id` = `co_statipreventivi_lang`.`id_record` AND `co_statipreventivi_lang`.`id_lang` = '.prepare(\App::getLang()).') WHERE `is_pianificabile`=1' : 'SELECT `co_statipreventivi`.`id`, `co_statipreventivi_lang`.`name` as descrizione FROM `co_statipreventivi` LEFT JOIN `co_statipreventivi_lang` ON (`co_statipreventivi`.`id` = `co_statipreventivi_lang`.`id_record` AND `co_statipreventivi_lang`.`id_lang` = '.prepare(\App::getLang()).')';

$stato = (new Stato())->getByName('Bozza')->id_record;

?><form action="" method="post" id="add-form">
	<input type="hidden" name="op" value="add">
	<input type="hidden" name="backto" value="record-edit">

	<!-- Fix creazione da Anagrafica -->
	<input type="hidden" name="id_record" value="">

	<div class="row">

		<div class="col-md-6">
			 {[ "type": "text", "label": "<?php echo tr('Nome'); ?>", "name": "nome", "required": 1 ]}
		</div>

		<div class="col-md-6">
				{[ "type": "select", "label": "<?php echo tr('Cliente'); ?>", "name": "idanagrafica", "required": 1, "value": "<?php echo $id_anagrafica; ?>", "ajax-source": "clienti", "icon-after": "add|<?php echo (new Module())->GetByName('Anagrafiche')->id_record; ?>|tipoanagrafica=Cliente&readonly_tipo=1", "readonly": "<?php echo (empty(get('idanagrafica'))) ? 0 : 1; ?>" ]}
		</div>
	</div>
	<div class="row">

		<div class="col-md-4">
				{[ "type": "select", "label": "<?php echo tr('Sede'); ?>", "name": "idsede", "ajax-source": "sedi", "select-options": <?php echo json_encode(['idanagrafica' => $id_anagrafica]); ?>, "placeholder": "Sede legale" ]}
		</div>

		<div class="col-md-4">
			{[ "type": "date", "label": "<?php echo tr('Data bozza'); ?>", "name": "data_bozza", "value": "<?php echo '-now-'; ?>", "required": 1 ]}
        </div>

		<div class="col-md-4">
			{[ "type": "select", "label": "<?php echo tr('Sezionale'); ?>", "name": "id_segment", "required": 1, "ajax-source": "segmenti", "select-options": <?php echo json_encode(['id_module' => $id_module, 'is_sezionale' => 1]); ?>, "value": "<?php echo $_SESSION['module_'.$id_module]['id_segment']; ?>" ]}
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			{[ "type": "select", "label": "<?php echo tr('Tipo di Attività'); ?>", "name": "idtipointervento", "required": 1, "ajax-source": "tipiintervento" ]}
		</div>

		<div class="col-md-6">
            {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "idstato", "required": 1, "value": "<?php echo $stato; ?>", "values": "query=<?php echo $stati; ?>" ]}
        </div>
	</div>

	<!-- PULSANTI -->
	<div class="row">
		<div class="col-md-12 text-right">
			<button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo tr('Aggiungi'); ?></button>
		</div>
	</div>
</form>

<script>
	$('#modals > div #idanagrafica').change(function() {
        updateSelectOption("idanagrafica", $(this).val());
        session_set('superselect,idanagrafica', $(this).val(), 0);
	});
</script>
