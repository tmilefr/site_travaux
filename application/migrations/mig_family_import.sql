-- =====================================================================
-- Migration : Import CSV ABCM (familles + membres)
-- ---------------------------------------------------------------------
-- Ajoute :
--   * famille.code_famille_abcm  : identifiant ABCM unique de la famille
--                                   (clé primaire de matching à partir
--                                    du 2e import)
--   * famille.to_deactivate      : drapeau "absente du dernier CSV ABCM"
--                                   (0 = active, 1 = à désactiver — l'admin
--                                    décide manuellement de l'action)
--   * members.code_membre_abcm   : identifiant ABCM unique du membre
--   * members.classe             : libellé de classe (ex: "2025 2026 MUL CL07")
--   * family_import              : log des imports réalisés
-- ---------------------------------------------------------------------
-- À jouer une seule fois en production. Idempotent par construction
-- (IF NOT EXISTS sur la table de log, ALTER TABLE ... ADD COLUMN
-- échouera silencieusement si déjà appliqué — vérifier les warnings).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. famille : nouvelles colonnes
-- ---------------------------------------------------------------------
ALTER TABLE `famille`
    ADD `code_famille_abcm` VARCHAR(20) NULL AFTER `idfamille`;

ALTER TABLE `famille`
    ADD `to_deactivate` TINYINT(1) NOT NULL DEFAULT 0 AFTER `civil_year`;

-- Index sur le code ABCM pour accélérer le matching à l'import (clé
-- théoriquement unique côté ABCM, mais on laisse NULL accepté tant que
-- les anciennes familles n'ont pas encore été matchées).
CREATE INDEX `idx_famille_code_abcm` ON `famille` (`code_famille_abcm`);


-- ---------------------------------------------------------------------
-- 2. members : nouvelles colonnes
-- ---------------------------------------------------------------------
ALTER TABLE `members`
    ADD `code_membre_abcm` VARCHAR(50) NULL AFTER `id_fam`;

ALTER TABLE `members`
    ADD `classe` VARCHAR(100) NULL AFTER `prenom`;

CREATE INDEX `idx_members_code_abcm` ON `members` (`code_membre_abcm`);


-- ---------------------------------------------------------------------
-- 3. family_import : log des imports CSV ABCM
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `family_import` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename`      VARCHAR(255) NOT NULL COMMENT 'nom du fichier original',
    `stored_path`   VARCHAR(255) NOT NULL COMMENT 'chemin relatif sous public/files/imports/',
    `civil_year`    VARCHAR(20)  NOT NULL,
    `nb_lines`      INT(11) NOT NULL DEFAULT 0 COMMENT 'lignes data du CSV',
    `nb_families`   INT(11) NOT NULL DEFAULT 0 COMMENT 'familles distinctes',
    `nb_created`    INT(11) NOT NULL DEFAULT 0,
    `nb_updated`    INT(11) NOT NULL DEFAULT 0,
    `nb_marked`     INT(11) NOT NULL DEFAULT 0 COMMENT 'familles passées à to_deactivate=1',
    `nb_reactivated`INT(11) NOT NULL DEFAULT 0,
    `nb_errors`     INT(11) NOT NULL DEFAULT 0,
    `report`        TEXT NULL COMMENT 'JSON détaillé des actions',
    `imported_by`   INT(11) NULL COMMENT 'acl_users.id',
    `created`       DATETIME NOT NULL,
    `updated`       DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_family_import_civil_year` (`civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
