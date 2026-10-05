-- =====================================================================
--  SITE_TRAVAUX — PURGE DU JEU DE TEST
-- ---------------------------------------------------------------------
--  Retire toutes les données créées par 10_jeu_de_test.sql, et rien
--  d'autre. Le filtre porte exclusivement sur la plage d'identifiants
--  réservée 9000–9999, qu'aucune donnée réelle n'utilise.
--
--  À exécuter en fin de campagne, ou avant de recharger un jeu neuf
--  (10_jeu_de_test.sql fait de toute façon sa propre purge au démarrage).
--
--  CONTRÔLE PRÉALABLE RECOMMANDÉ — vérifier qu'aucune donnée réelle ne
--  se trouve dans la plage réservée :
--
--     SELECT 'famille' t, COUNT(*) n FROM famille WHERE id BETWEEN 9000 AND 9999
--       AND (idfamille IS NULL OR idfamille NOT LIKE 'ZZTEST%')
--     UNION ALL
--     SELECT 'travaux', COUNT(*) FROM travaux WHERE id BETWEEN 9000 AND 9999
--       AND (titre IS NULL OR titre NOT LIKE '[RECETTE]%');
--
--  Les deux compteurs doivent valoir 0. Sinon, ne pas exécuter ce script
--  et purger ligne à ligne.
-- =====================================================================

SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- Ordre : des tables filles vers les tables mères.
DELETE FROM `cantine_inscriptions` WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `cantine_config`       WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `validation_tokens`    WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `infos`                WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `unites`               WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `travaux`              WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `candidatures`         WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `trombi`               WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `groupes_member`       WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `groupes`              WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `members`              WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `emails`               WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `famille_alerts`       WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `famille`              WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `sendmail_statut`      WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `sendmail`             WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `acl_users`            WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `acl_roles_controllers` WHERE `id_role` BETWEEN 9000 AND 9999;
DELETE FROM `acl_roles`            WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `options`              WHERE `id` BETWEEN 9000 AND 9999;

-- Familles importées par le test d'import CSV (codes ZZIMPORT*),
-- créées par l'application elle-même et donc hors plage 9000–9999.
DELETE FROM `members` WHERE `id_fam` IN (SELECT `id` FROM `famille` WHERE `idfamille` LIKE 'ZZIMPORT%');
DELETE FROM `emails`  WHERE `id_fam` IN (SELECT `id` FROM `famille` WHERE `idfamille` LIKE 'ZZIMPORT%');
DELETE FROM `famille` WHERE `idfamille` LIKE 'ZZIMPORT%';

SELECT 'Purge terminée — lignes restantes dans la plage 9000-9999 :' AS ``;
SELECT 'famille' AS `Table`, COUNT(*) AS `Restant` FROM `famille` WHERE `id` BETWEEN 9000 AND 9999
UNION ALL SELECT 'travaux',  COUNT(*) FROM `travaux`  WHERE `id` BETWEEN 9000 AND 9999
UNION ALL SELECT 'infos',    COUNT(*) FROM `infos`    WHERE `id` BETWEEN 9000 AND 9999
UNION ALL SELECT 'acl_users',COUNT(*) FROM `acl_users`WHERE `id` BETWEEN 9000 AND 9999;
