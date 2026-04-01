
/* Nettoyage des tables 
DROP TABLE `jos_banner`, `jos_bannerclient`, `jos_bannertrack`, `jos_categories`, `jos_components`, `jos_contact_details`, `jos_content`, `jos_content_frontpage`, `jos_content_rating`, `jos_core_acl_aro`, `jos_core_acl_aro_groups`, `jos_core_acl_aro_map`, `jos_core_acl_aro_sections`, `jos_core_acl_groups_aro_map`, `jos_core_log_items`, `jos_core_log_searches`, `jos_groups`, `jos_menu`, `jos_menu_types`, `jos_messages`, `jos_messages_cfg`, `jos_migration_backlinks`, `jos_modules`, `jos_modules_menu`, `jos_newsfeeds`, `jos_plugins`, `jos_polls`, `jos_poll_data`, `jos_poll_date`, `jos_poll_menu`, `jos_sections`, `jos_session`, `jos_stats_agents`, `jos_templates_menu`, `jos_users`, `jos_weblinks`;
*/
/* Modification infos */
ALTER TABLE `infos` CHANGE `id_infos` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `infos` ADD `type_session` INT(10) NULL AFTER `nb_unites_valides_effectif`;
ALTER TABLE `infos` ADD `type_participant` VARCHAR(255) NOT NULL AFTER `nb_participants`;
ALTER TABLE `infos` ADD `created` DATETIME NULL AFTER `type_session`, ADD `updated` DATETIME NULL AFTER `created`;
ALTER TABLE `infos` CHANGE `nb_unites_valides_effectif` `nb_unites_valides_effectif` FLOAT NULL DEFAULT '0';

/* Modification famille */
ALTER TABLE `famille` ADD `nom` VARCHAR(255) NULL AFTER `login`;
ALTER TABLE `famille` ADD `capacity` VARCHAR(255) NULL AFTER `ecole`;
ALTER TABLE `famille` ADD `type_session` INT(10) NULL AFTER `capacity`;
ALTER TABLE `famille` ADD `created` DATETIME NULL AFTER `type_session`, ADD `updated` DATETIME NULL AFTER `created`;

/* Modification travaux */
ALTER TABLE `travaux` CHANGE `repas` `repas` TINYINT(4) NULL;
ALTER TABLE `travaux` CHANGE `id_travaux` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `travaux` ADD `titre` VARCHAR(255) NOT NULL AFTER `heure_fin_trav`;
ALTER TABLE `travaux` ADD `type` VARCHAR(255) NOT NULL AFTER `heure_fin_trav`;
ALTER TABLE `travaux` ADD `nb_units` FLOAT(10) NOT NULL AFTER `type`;
ALTER TABLE `travaux` ADD `created` DATETIME NULL AFTER `accespar`, ADD `updated` DATETIME NULL AFTER `created`;

/* Trombi Voir trombi.sql */
DROP TABLE `trombi`;

/* Unités */
ALTER TABLE `unites` ADD `type_session` INT(10) NULL AFTER `unites_comm`;
ALTER TABLE `unites` CHANGE `unites_id` `id` INT(3) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `unites` ADD `created` DATETIME NULL AFTER `type_session`, ADD `updated` DATETIME NULL AFTER `created`;

/* div fix */
UPDATE `famille` SET nom = login WHERE nom IS NULL
UPDATE `infos` SET `type_participant` = 'Mr' WHERE 	`Monsieur` = 1;
UPDATE `infos` SET `type_participant` = 'Mme' WHERE 	`Madame` = 1;
UPDATE `infos` SET `type_participant` = 'Both' WHERE 	`lesdeux` = 1;

/*ADD 28/07 & PR */
ALTER TABLE `famille` ADD `e_mail_comp` VARCHAR(255) NULL AFTER `e_mail`;