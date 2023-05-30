
-- Fix query Scadenzario
UPDATE `zz_views` INNER JOIN `zz_modules` ON `zz_views`.`id_module` = `zz_modules`.`id` SET `zz_views`.`query` = 'IF(emails IS NOT NULL, \'fa fa-envelope text-success\', \'\')' WHERE `zz_modules`.`name` = 'Scadenzario' AND `zz_views`.`name` = 'icon_Inviato';
UPDATE `zz_views` INNER JOIN `zz_modules` ON `zz_views`.`id_module` = `zz_modules`.`id` SET `zz_views`.`query` = 'IF(emails IS NOT NULL, \'Inviata via email\', \'\')' WHERE `zz_modules`.`name` = 'Scadenzario' AND `zz_views`.`name` = 'icon_title_Inviato';
UPDATE `zz_modules` SET `options` = "
SELECT
    |select| 
FROM 
    `co_scadenziario`
    LEFT JOIN `co_documenti` ON `co_scadenziario`.`iddocumento` = `co_documenti`.`id`
    LEFT JOIN `co_banche` ON `co_banche`.`id` = `co_documenti`.`id_banca_azienda`
    LEFT JOIN `an_anagrafiche` ON `co_scadenziario`.`idanagrafica` = `an_anagrafiche`.`idanagrafica`
    LEFT JOIN `co_pagamenti` ON `co_documenti`.`idpagamento` = `co_pagamenti`.`id`
    LEFT JOIN `co_tipidocumento` ON `co_documenti`.`idtipodocumento` = `co_tipidocumento`.`id`
    LEFT JOIN `co_statidocumento` ON `co_documenti`.`idstatodocumento` = `co_statidocumento`.`id`
    LEFT JOIN (SELECT COUNT(id) as emails, em_emails.id_record FROM em_emails INNER JOIN zz_operations ON zz_operations.id_email = em_emails.id WHERE id_module IN(SELECT id FROM zz_modules WHERE name = 'Scadenzario') AND `zz_operations`.`op` = 'send-email' GROUP BY em_emails.id_record) AS `email` ON `email`.`id_record` = `co_scadenziario`.`id`
WHERE 
    1=1 AND (`co_statidocumento`.`descrizione` IS NULL OR `co_statidocumento`.`descrizione` IN('Emessa','Parzialmente pagato','Pagato')) 
HAVING
    2=2
ORDER BY 
    `scadenza` ASC" WHERE `name` = 'Scadenzario';

-- Rimozione stampa spesometro
DELETE FROM `zz_prints` WHERE `name` = 'Spesometro';

-- Aggiunta stampa ddt in entrata
INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`) VALUES (NULL, (SELECT `id` FROM `zz_modules` WHERE `name` = 'Ddt di acquisto'), '1', 'Ddt di acquisto', 'Ddt in entrata', 'DDT num. {numero} del {data}', 'ddt', 'idddt', '{\"pricing\":true}', 'fa fa-print', '', '', '0', '1', '1', '1');

-- Aggiunte note checklist
ALTER TABLE `zz_checks` ADD `note` TEXT NOT NULL AFTER `content`;

-- Aggiunto widget per la stampa settimanale del calendario
INSERT INTO `zz_widgets` (`id`, `name`, `type`, `id_module`, `location`, `class`, `query`, `bgcolor`, `icon`, `print_link`, `more_link`, `more_link_type`, `php_include`, `text`, `enabled`, `order`, `help`) VALUES
(NULL, 'Stampa calendario settimanale', 'print', (SELECT id FROM zz_modules WHERE name = 'Dashboard'), 'controller_top', NULL, NULL, '#4ccc4c', 'fa fa-print', '', './modules/dashboard/widgets/stampa_calendario_settimanale.dashboard.php', 'popup', '', 'Stampa calendario settimanale', 1, 7, NULL);

INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`, `available_options`) VALUES
(NULL, (SELECT `id` FROM `zz_modules` WHERE name = 'Dashboard'), 1, 'Stampa calendario settimanale', 'Stampa calendario settimanale', 'Calendario settimanale', 'dashboard_settimanale', '', '', 'fa fa-print', '', '', 0, 1, 1, 1, NULL);

UPDATE `zz_settings` SET `nome` = 'Permetti fatturazione delle attività collegate a contratti' WHERE `zz_settings`.`nome` = 'Permetti fatturazione delle attività collegate a contratti, ordini e preventivi';

INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`, `order`, `help`) VALUES (NULL, 'Permetti fatturazione delle attività collegate a ordini', (SELECT `valore` FROM `zz_settings` AS `a` WHERE `nome` = 'Permetti fatturazione delle attività collegate a contratti'), 'boolean', '1', 'Fatturazione', NULL, NULL);

INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`, `order`, `help`) VALUES (NULL, 'Permetti fatturazione delle attività collegate a preventivi', (SELECT `valore` FROM `zz_settings` AS `a` WHERE `nome` = 'Permetti fatturazione delle attività collegate a contratti'), 'boolean', '1', 'Fatturazione', NULL, NULL);

INSERT INTO `zz_settings` (`id`, `nome`, `valore`, `tipo`, `editable`, `sezione`, `order`, `help`) VALUES (NULL, 'Serial number abilitato di default', '0', 'boolean', '1', 'Magazzino', NULL, "Abilita automaticamente il serial number al momento della creazione di un articolo dal Magazzino o dall'importazione di una fattura di acquisto.");

-- Fix visualizzazione stampa ordine senza codici
UPDATE `zz_prints` SET `options` = '{\"pricing\": true, \"last-page-footer\": true, \"hide-item-number\": true, \"images\": true}' WHERE `zz_prints`.`name` = "Ordine cliente (senza codici)";

-- Aggiunta stampa preventivo senza codici
INSERT INTO `zz_prints` (`id`, `id_module`, `is_record`, `name`, `title`, `filename`, `directory`, `previous`, `options`, `icon`, `version`, `compatibility`, `order`, `predefined`, `default`, `enabled`, `available_options`) VALUES (NULL, (SELECT `id` FROM `zz_modules` WHERE name = 'Preventivi'), '1', 'Preventivo (senza codici)', 'Preventivo (senza codici)', 'Preventivo num. {numero} del {data} rev {revisione}', 'preventivi', 'idpreventivo', '{\"pricing\": true, \"last-page-footer\": true, \"images\": true, \"hide-item-number\": true}', 'fa fa-print', '', '', '0', '0', '1', '1', '{\"pricing\":\"Visualizzare i prezzi\", \"hide-total\": \"Nascondere i totali delle righe\", \"show-only-total\": \"Visualizzare solo i totali del documento\", \"hide-header\": \"Nascondere intestazione\", \"hide-footer\": \"Nascondere footer\", \"last-page-footer\": \"Visualizzare footer solo su ultima pagina\", \"hide-item-number\": \"Nascondere i codici degli articoli\"}');

-- Aggiunte api app per checklists
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES (NULL, 'app-v1', 'retrieve', 'checklist', 'API\\App\\v1\\Checklists', '1');
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES (NULL, 'app-v1', 'retrieve', 'checklists', 'API\\App\\v1\\Checklists', '1');
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES (NULL, 'app-v1', 'update', 'checklist', 'API\\App\\v1\\Checklists', '1');
INSERT INTO `zz_api_resources` (`id`, `version`, `type`, `resource`, `class`, `enabled`) VALUES (NULL, 'app-v1', 'retrieve', 'checklists-cleanup', 'API\\App\\v1\\Checklists', '1');