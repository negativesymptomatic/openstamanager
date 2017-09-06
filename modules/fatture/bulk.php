<?php

include_once __DIR__.'/../../core.php';

switch (post('op')) {
    case 'export-bulk':
        $dir = DOCROOT.'/files/export_fatture/';

        // Selezione delle fatture da stampare
        $records = $dbo->fetchArray('SELECT co_documenti.id, numero_esterno, data, ragione_sociale, co_tipidocumento.descrizione FROM co_documenti INNER JOIN an_anagrafiche ON co_documenti.idanagrafica=an_anagrafiche.idanagrafica INNER JOIN co_tipidocumento ON co_documenti.idtipodocumento=co_tipidocumento.id WHERE co_documenti.id IN('.implode(',', $id_records).')');

        foreach ($records as $r) {
            $numero = !empty($r['numero_esterno']) ? $r['numero_esterno'] : $r['numero'];
            $numero = str_replace(['/', '\\'], '-', $numero);

            // Gestione della stampa
            $rapportino_nome = sanitizeFilename($numero.' '.$r['data'].' '.$r['ragione_sociale'].'.pdf');
            $filename = slashes($dir.$rapportino_nome);

            $_GET['iddocumento'] = $r['id']; // Fix temporaneo per la stampa
            $iddocumento = $r['id']; // Fix temporaneo per la stampa
            $ptype = ($r['descrizione'] == 'Fattura accompagnatoria di vendita') ? 'fatture_accompagnatorie' : 'fatture';

            require DOCROOT.'/pdfgen.php';
        }

        $dir = slashes($dir);
        $file = slashes($dir.'fatture.zip');

        // Creazione zip
        if (extension_loaded('zip')) {
            create_zip($dir, $file);

            // Invio al browser dello zip
            force_download($file);

            // Rimozione dei contenuti
            deltree($dir);
        }

        break;
}

return [
    'export-bulk' => [
        'text' => tr('Esporta stampe'),
        'data' => [
            'msg' => tr('Vuoi davvero esportare tutte le stampe in un archivio?'),
            'button' => tr('Procedi'),
            'class' => 'btn btn-lg btn-warning',
        ],
    ],
];
