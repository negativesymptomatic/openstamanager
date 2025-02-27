<?php

use Models\Module;
use Modules\Anagrafiche\Anagrafica;
use Modules\Contratti\Contratto;
use Modules\Fatture\Fattura;
use Modules\Preventivi\Preventivo;
use Modules\Fatture\Stato;

include_once __DIR__.'/../../core.php';

$id_anagrafica = get('id_anagrafica');
$op = get('op');
$numero_documenti = 5;

switch ($op) {
    case 'dettagli':
        // Informazioni sui contratti
        $modulo_contratti = Module::find((new Module())->getByName('Contratti')->id_record);
        if ($modulo_contratti->permission != '-') {
            // Contratti attivi per l'anagrafica
            $contratti = Contratto::where('idanagrafica', '=', $id_anagrafica)
                ->whereHas('stato', function ($query) {
                    $query->where('is_pianificabile', '=', 1);
                })
                ->latest()->take($numero_documenti)->get();

            echo '
        <div class="row">
            <div class="col-md-4">
                <b>'.tr('Contratti').':</b><ul>';
            if (!$contratti->isEmpty()) {
                foreach ($contratti as $contratto) {
                    echo '
                    <li>'.$contratto->getReference().' ['.$contratto->stato->name.']: '.dateFormat($contratto->data_accettazione).' - '.dateFormat($contratto->data_conclusione).'</li>';
                }
            } else {
                echo '
                    <li>'.tr('Nessun contratto attivo per questo cliente').'</li>';
            }
            echo '
                </ul>
            </div>';
        }

        // Informazioni sui preventivi
        $modulo_preventivi = Module::find((new Module())->getByName('Preventivi')->id_record);
        if ($modulo_preventivi->permission != '-') {
            // Preventivi attivi
            $preventivi = Preventivo::where('idanagrafica', '=', $id_anagrafica)
                ->whereHas('stato', function ($query) {
                    $query->where('is_pianificabile', '=', 1);
                })
                ->latest()->take($numero_documenti)->get();
            echo '
            <div class="col-md-4">
                <b>'.tr('Preventivi').':</b><ul>';
            if (!$preventivi->isEmpty()) {
                foreach ($preventivi as $preventivo) {
                    echo '
                    <li>'.$preventivo->getReference().' ['.$preventivo->stato->name.']</li>';
                }
            } else {
                echo '
                    <li>'.tr('Nessun preventivo attivo per questo cliente').'</li>';
            }
            echo '
                </ul>
            </div>';
        }

        // Informazioni sui preventivi
        $modulo_fatture_vendita =Module::find((new Module())->getByName('Fatture di vendita')->id_record);
        if ($modulo_fatture_vendita->permission != '-') {
            // Fatture attive
            $fatture = Fattura::where('idanagrafica', '=', $id_anagrafica)
                ->whereHas('stato', function ($query) {
                    $id_bozza = (new Stato())->getByName('Bozza')->id_record;
                    $id_parz_pagato = (new Stato())->getByName('Parziale pagato')->id_record;
                    $query->whereIn('id', [$id_bozza, $id_parz_pagato]);
                })
                ->latest()->take($numero_documenti)->get();
            echo '
            <div class="col-md-4">
                <b>'.tr('Fatture').':</b><ul>';
            if (!$fatture->isEmpty()) {
                foreach ($fatture as $fattura) {
                    $scadenze = $fattura->scadenze;
                    $da_pagare = $scadenze->sum('da_pagare') - $scadenze->sum('pagato');
                    echo '
                    <li>'.$fattura->getReference().': '.moneyFormat($da_pagare).'</li>';
                }
            } else {
                echo '
                    <li>'.tr('Nessuna fattura attiva per questo cliente').'</li>';
            }
            echo '
                </ul>
            </div>';
        }

        // Note dell'anagrafica
        $anagrafica = Anagrafica::find($id_anagrafica);
        $note_anagrafica = $anagrafica->note;
        echo '
            <div class="col-md-12">
                <p><b>'.tr('Note interne sul cliente').':</b></p>
                '.(!empty($note_anagrafica) ? $note_anagrafica : tr('Nessuna nota interna per questo cliente')).'
            </div>';

        echo '
        </div>';

        break;
}
