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

use Modules\Anagrafiche\Nazione;
use Modules\Ordini\Ordine;

$documento = Ordine::find($id_record);
$d_qta = (int) setting('Cifre decimali per quantità in stampa');
$d_importi = (int) setting('Cifre decimali per importi in stampa');
$d_totali = (int) setting('Cifre decimali per totali in stampa');

$id_cliente = $documento['idanagrafica'];

// Leggo i dati della destinazione (se 0=sede legale, se!=altra sede da leggere da tabella an_sedi)
$destinazione = '';
if (!empty($documento->idsede)) {
    $rsd = $dbo->fetchArray('SELECT (SELECT codice FROM an_anagrafiche WHERE idanagrafica=an_sedi.idanagrafica) AS codice, (SELECT ragione_sociale FROM an_anagrafiche WHERE idanagrafica=an_sedi.idanagrafica) AS ragione_sociale, nomesede, indirizzo, indirizzo2, cap, citta, provincia, piva, codice_fiscale, id_nazione FROM an_sedi WHERE idanagrafica='.prepare($id_cliente).' AND id='.prepare($documento->idsede));

    if (!empty($rsd[0]['nomesede'])) {
        $destinazione .= $rsd[0]['nomesede'].'<br/>';
    }
    if (!empty($rsd[0]['indirizzo'])) {
        $destinazione .= $rsd[0]['indirizzo'].'<br/>';
    }
    if (!empty($rsd[0]['indirizzo2'])) {
        $destinazione .= $rsd[0]['indirizzo2'].'<br/>';
    }
    if (!empty($rsd[0]['cap'])) {
        $destinazione .= $rsd[0]['cap'].' ';
    }
    if (!empty($rsd[0]['citta'])) {
        $destinazione .= $rsd[0]['citta'];
    }
    if (!empty($rsd[0]['provincia'])) {
        $destinazione .= ' ('.$rsd[0]['provincia'].')';
    }
    if (!empty($rsd[0]['id_nazione'])) {
        $nazione = Nazione::find($rsd[0]['id_nazione']);
        if ($nazione['iso2'] != 'IT') {
            $destinazione .= ' - '.$nazione->name;
        }
    }
}

$numero = !empty($documento['numero_esterno']) ? $documento['numero_esterno'] : $documento['numero'];
$pagamento = $dbo->fetchOne('SELECT * FROM `co_pagamenti` LEFT JOIN `co_pagamenti_lang` ON (`co_pagamenti`.`id` = `co_pagamenti_lang`.`id_record` AND `co_pagamenti_lang`.`id_lang` = '.prepare(\App::getLang()).') WHERE `co_pagamenti`.`id` = '.prepare($documento->idpagamento));

// Sostituzioni specifiche
$custom = [
    'tipo_doc' => Stringy\Stringy::create($documento->tipo->name)->toUpperCase(),
    'numero' => $numero,
    'data' => Translator::dateToLocale($documento['data']),
    'pagamento' => $pagamento['descrizione'],
];
