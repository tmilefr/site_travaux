-- =====================================================================
-- Migration de la base de PRODUCTION (schema CodeIgniter 3) vers la version CodeIgniter 4.
-- Cible : MariaDB >= 10.3 (ADD COLUMN IF NOT EXISTS / CREATE TABLE IF NOT EXISTS).
-- IDEMPOTENT : peut etre rejoue sans risque. Aucune donnee n'est supprimee,
-- sauf la table ci_sessions (sessions en cours : tout le monde devra se reconnecter).
--
-- A FAIRE AVANT : sauvegarde complete (mysqldump) de la base.
-- Usage       : mysql -u USER -p NOM_DE_LA_BASE < database/sql/mig_prod_vers_ci4.sql
--
-- Cette migration remplace l'application manuelle de : mig_family_import.sql, mig_cantine.sql,
-- mig_alert_sent_at.sql, mig_cantine_generation_cols.sql, mig_sendmail_statut_error_text.sql,
-- ci4_ci_sessions.sql, et ajoute les tables/colonnes qu'aucun script du depot ne creait.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. famille
--    * password : le hash bcrypt fait 60 caracteres (la colonne etait en VARCHAR(50) :
--      la reecriture du hash a la connexion echouait).
--    * colonnes de l'import CSV ABCM et des alertes e-mail.
-- ---------------------------------------------------------------------
ALTER TABLE `famille` MODIFY `password` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `famille`
  ADD COLUMN IF NOT EXISTS `code_famille_abcm` VARCHAR(20) NULL AFTER `idfamille`,
  ADD COLUMN IF NOT EXISTS `to_deactivate` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `alert_types` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `type_session` INT(10) NULL;
ALTER TABLE `famille` ADD INDEX IF NOT EXISTS `idx_famille_code_abcm` (`code_famille_abcm`);

-- ---------------------------------------------------------------------
-- 2. members : identifiants ABCM et classe
-- ---------------------------------------------------------------------
ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `code_membre_abcm` VARCHAR(50) NULL AFTER `id_fam`,
  ADD COLUMN IF NOT EXISTS `classe` VARCHAR(100) NULL AFTER `prenom`;
ALTER TABLE `members` ADD INDEX IF NOT EXISTS `idx_members_code_abcm` (`code_membre_abcm`);

-- ---------------------------------------------------------------------
-- 3. travaux / infos : annee scolaire des inscriptions, suivi des mails automatiques
-- ---------------------------------------------------------------------
ALTER TABLE `travaux`
  ADD COLUMN IF NOT EXISTS `ref_mail_sent_at` DATETIME NULL,
  ADD COLUMN IF NOT EXISTS `alert_sent_at` DATETIME NULL;

ALTER TABLE `infos` ADD COLUMN IF NOT EXISTS `civil_year` VARCHAR(255) NULL AFTER `nb_unites_valides_effectif`;
-- reprend l'annee de la session pour les inscriptions existantes
UPDATE `infos` i JOIN `travaux` t ON t.`id` = i.`id_travaux`
   SET i.`civil_year` = t.`civil_year`
 WHERE i.`civil_year` IS NULL AND t.`civil_year` IS NOT NULL;

-- ---------------------------------------------------------------------
-- 4. e-mails : le journal SMTP depasse 255 caracteres, et `error` n'a pas de valeur par defaut
-- ---------------------------------------------------------------------
ALTER TABLE `sendmail_statut` MODIFY `error` TEXT NULL;
ALTER TABLE `sendmail` MODIFY `error` VARCHAR(255) NULL DEFAULT NULL;

-- ---------------------------------------------------------------------
-- 5. Nouvelles tables
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `validation_tokens` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `id_travaux` INT(11) NOT NULL,
  `id_fam_ref` INT(11) NOT NULL,
  `token`      VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at`    DATETIME NULL,
  `created`    DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_travaux` (`id_travaux`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `famille_alerts` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_fam`  INT(11)      NULL,
  `id_type` VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fam` (`id_fam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `parameters` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_name`    VARCHAR(255) NULL,
  `slogan`      VARCHAR(255) NULL,
  `debug_app`   VARCHAR(255) NULL,
  `protocol`    VARCHAR(255) NULL,
  `smtp_host`   VARCHAR(255) NULL,
  `smtp_port`   VARCHAR(255) NULL,
  `smtp_user`   VARCHAR(255) NULL,
  `smtp_pass`   VARCHAR(255) NULL,
  `smtp_crypto` VARCHAR(255) NULL,
  `charset`     VARCHAR(255) NULL,
  `mailtype`    VARCHAR(255) NULL,
  `wordwrap`    VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `family_import` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`       VARCHAR(255) NOT NULL,
  `stored_path`    VARCHAR(255) NOT NULL,
  `civil_year`     VARCHAR(20)  NOT NULL,
  `nb_lines`       INT(11) NOT NULL DEFAULT 0,
  `nb_families`    INT(11) NOT NULL DEFAULT 0,
  `nb_created`     INT(11) NOT NULL DEFAULT 0,
  `nb_updated`     INT(11) NOT NULL DEFAULT 0,
  `nb_marked`      INT(11) NOT NULL DEFAULT 0,
  `nb_reactivated` INT(11) NOT NULL DEFAULT 0,
  `nb_errors`      INT(11) NOT NULL DEFAULT 0,
  `report`         TEXT NULL,
  `imported_by`    INT(11) NULL,
  `created`        DATETIME NOT NULL,
  `updated`        DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_family_import_civil_year` (`civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Cantine (garde du midi)
CREATE TABLE IF NOT EXISTS `cantine_config` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `id_day`      TINYINT(1) NOT NULL,
  `active`      TINYINT(1) NOT NULL DEFAULT 0,
  `nb_slots`    INT(2) NOT NULL DEFAULT 0,
  `ecole`       VARCHAR(3) NOT NULL DEFAULT 'B',
  `nb_units`    FLOAT(10) NOT NULL DEFAULT 1,
  `id_referent` VARCHAR(255) NULL,
  `heure_deb`   VARCHAR(10) NOT NULL DEFAULT '11:45',
  `heure_fin`   VARCHAR(10) NOT NULL DEFAULT '13:30',
  `civil_year`  VARCHAR(255) NULL,
  `created`     DATETIME NULL,
  `updated`     DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_day_ecole_year` (`id_day`, `ecole`, `civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantine_inscriptions` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `date_garde` DATE NOT NULL,
  `id_famille` INT(11) NOT NULL,
  `ecole`      VARCHAR(3) NOT NULL DEFAULT 'B',
  `id_info`    INT(11) NULL,
  `id_travaux` INT(11) NULL,
  `civil_year` VARCHAR(255) NULL,
  `created`    DATETIME NULL,
  `updated`    DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date_fam` (`date_garde`, `id_famille`),
  KEY `idx_date` (`date_garde`),
  KEY `idx_famille` (`id_famille`),
  KEY `idx_info` (`id_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantine_generation` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_deb`   DATE         NULL,
  `date_fin`   DATE         NULL,
  `ecole`      VARCHAR(3)   NULL,
  `civil_year` VARCHAR(255) NULL,
  `nb_created` INT(11)      NULL,
  `nb_skipped` INT(11)      NULL,
  `created`    DATETIME     NULL,
  `updated`    DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------
-- 6. Sessions : CodeIgniter 4 stocke l'activite dans une colonne TIMESTAMP (CI3 : entier).
--    Les sessions en cours sont perdues : tous les utilisateurs devront se reconnecter.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`, `ip_address`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
