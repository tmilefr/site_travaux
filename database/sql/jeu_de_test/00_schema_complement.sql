-- =====================================================================
--  SITE_TRAVAUX — SOCLE DE SCHÉMA COMPLÉMENTAIRE
-- ---------------------------------------------------------------------
--  À exécuter UNIQUEMENT sur une base vide (environnement de recette
--  monté de zéro). Sur une base restaurée depuis la production, les
--  tables existent déjà : ce script ne fera rien (CREATE TABLE IF NOT
--  EXISTS) mais il ne corrigera pas non plus d'éventuels écarts.
--
--  Pourquoi ce fichier : les tables historiques `famille`, `travaux`,
--  `infos` et `unites` proviennent de l'ancienne plateforme Joomla et
--  n'ont jamais eu de script de création dans le dépôt ; seules des
--  instructions ALTER existent (Migration.sql). Les définitions ci-dessous
--  sont reconstituées à partir des schémas JSON de l'application
--  (application/models/json/*.json), qui font foi pour le code.
--
--  Ordre d'exécution complet sur une base vide :
--     1. Acl.sql          2. Options.sql       3. capacitys.sql
--     4. groupes.sql      5. Trombi.sql        6. members.sql
--     7. emails.sql       8. ci_sessions.sql   9. mig_cantine.sql
--    10. mig_family_import.sql
--    11. 00_schema_complement.sql   (ce fichier)
--    12. 10_jeu_de_test.sql         (le jeu de test)
-- =====================================================================

SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- --------------------------------------------------------------- famille
CREATE TABLE IF NOT EXISTS `famille` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idfamille`   VARCHAR(255) NULL,
  `role_id`     INT(5)       NULL DEFAULT 2,
  `nom`         VARCHAR(255) NULL,
  `prenom`      VARCHAR(255) NULL,
  `login`       VARCHAR(255) NULL,
  `password`    VARCHAR(255) NULL,
  `cp`          VARCHAR(255) NULL,
  `ville`       VARCHAR(255) NULL,
  `adresse`     VARCHAR(255) NULL,
  `e_mail`      VARCHAR(255) NULL,
  `e_mail_comp` VARCHAR(255) NULL,
  `members`     VARCHAR(255) NULL,
  `nb_enfants`  VARCHAR(255) NULL,
  `ecole`       VARCHAR(255) NULL,
  `capacity`    VARCHAR(255) NULL,
  `alert_types` VARCHAR(255) NULL,
  `type_session` INT(10)     NULL,
  `civil_year`  VARCHAR(255) NULL,
  `created`     DATETIME     NULL,
  `updated`     DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_login` (`login`),
  KEY `idx_civil_year` (`civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------------- travaux
CREATE TABLE IF NOT EXISTS `travaux` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_personnel`     VARCHAR(255) NULL,
  `date_travaux`     VARCHAR(255) NULL,
  `heure_deb_trav`   VARCHAR(255) NULL,
  `heure_fin_trav`   VARCHAR(255) NULL,
  `type`             VARCHAR(255) NULL,
  `nb_units`         FLOAT(10)    NULL DEFAULT 0,
  `titre`            VARCHAR(255) NULL,
  `description`      TEXT         NULL,
  `txtmodel`         TEXT         NULL,
  `nb_inscrits_max`  VARCHAR(255) NULL,
  `referent_travaux` VARCHAR(255) NULL,
  `repas`            TINYINT(4)   NULL,
  `ecole`            VARCHAR(255) NULL,
  `accespar`         VARCHAR(255) NULL,
  `archived`         INT(11)      NULL DEFAULT 0,
  `civil_year`       VARCHAR(255) NULL,
  `type_session`     INT(10)      NULL DEFAULT 1,
  `statut`           VARCHAR(255) NULL DEFAULT '0',
  `created`          DATETIME     NULL,
  `updated`          DATETIME     NULL,
  `ref_mail_sent_at` DATETIME     NULL,
  `alert_sent_at` DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_date` (`date_travaux`),
  KEY `idx_civil_year` (`civil_year`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------- infos
CREATE TABLE IF NOT EXISTS `infos` (
  `id`                         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_famille`                 VARCHAR(255) NULL,
  `id_travaux`                 INT(11)      NULL,
  `heure_debut_prevue`         VARCHAR(255) NULL,
  `heure_fin_prevue`           VARCHAR(255) NULL,
  `heure_debut_effective`      VARCHAR(255) NULL,
  `heure_fin_effective`        VARCHAR(255) NULL,
  `remarque`                   TEXT         NULL,
  `nb_unites_valides`          FLOAT(11)    NULL DEFAULT 0,
  `nb_participants`            INT(11)      NULL DEFAULT 1,
  `type_participant`           VARCHAR(255) NULL,
  `nb_unites_valides_effectif` FLOAT        NULL DEFAULT 0,
  `type_session`               INT(10)      NULL,
  `civil_year`                 VARCHAR(255) NULL,
  `created`                    DATETIME     NULL,
  `updated`                    DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_travaux` (`id_travaux`),
  KEY `idx_famille` (`id_famille`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------- unites
CREATE TABLE IF NOT EXISTS `unites` (
  `id`                 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unites_id_famille`  VARCHAR(255) NULL,
  `unites_date`        VARCHAR(255) NULL,
  `unites_heure_debut` VARCHAR(255) NULL,
  `unites_heure_fin`   VARCHAR(255) NULL,
  `unites_valides`     VARCHAR(255) NULL,
  `unites_desc`        TEXT         NULL,
  `unites_comm`        TEXT         NULL,
  `type_session`       INT(10)      NULL,
  `civil_year`         VARCHAR(255) NULL,
  `archived`           INT(11)      NOT NULL DEFAULT 0,
  `created`            DATETIME     NULL,
  `updated`            DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_famille` (`unites_id_famille`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ------------------------------------------------------- validation_tokens
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

-- -------------------------------------------------------- famille_alerts
CREATE TABLE IF NOT EXISTS `famille_alerts` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_fam`  INT(11)      NULL,
  `id_type` VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fam` (`id_fam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -------------------------------------------------------- groupes_member
CREATE TABLE IF NOT EXISTS `groupes_member` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_fam`    VARCHAR(255) NULL,
  `name`      VARCHAR(255) NULL,
  `surname`   VARCHAR(255) NULL,
  `email`     VARCHAR(255) NULL,
  `phone`     VARCHAR(255) NULL,
  `thumbnail` VARCHAR(255) NULL,
  `picture`   VARCHAR(255) NULL,
  `created`   DATETIME     NULL,
  `updated`   DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------- candidatures
CREATE TABLE IF NOT EXISTS `candidatures` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_fam`  VARCHAR(255) NULL,
  `id_grp`  INT(5)       NULL,
  `name`    VARCHAR(255) NULL,
  `surname` VARCHAR(255) NULL,
  `email`   VARCHAR(255) NULL,
  `phone`   VARCHAR(255) NULL,
  `memo`    TEXT         NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------- grp_related
CREATE TABLE IF NOT EXISTS `grp_related` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_grp`  VARCHAR(255) NULL,
  `ref`     VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -------------------------------------------------------------- sendmail
CREATE TABLE IF NOT EXISTS `sendmail` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference`     VARCHAR(255) NULL,
  `email`         VARCHAR(255) NULL,
  `object`        VARCHAR(255) NULL,
  `message`       LONGTEXT     NULL,
  `statut`        INT(10)      NULL DEFAULT 0,
  `detail_statut` INT(11)      NULL,
  `created`       DATETIME     NULL,
  `updated`       DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sendmail_statut` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_sen`     VARCHAR(255) NULL,
  `sendstatut` VARCHAR(255) NULL,
  `date`       DATETIME     NULL,
  `error`      TEXT         NULL,
  `created`    DATETIME     NULL,
  `updated`    DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ------------------------------------------------------------- templates
CREATE TABLE IF NOT EXISTS `templates` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_type` VARCHAR(255) NULL,
  `name`    VARCHAR(255) NULL,
  `text`    TEXT         NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------- events
CREATE TABLE IF NOT EXISTS `events` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`   VARCHAR(255) NULL,
  `memo`    TEXT         NULL,
  `date`    VARCHAR(255) NULL,
  `time`    VARCHAR(255) NULL,
  `color`   VARCHAR(255) NULL,
  `statut`  VARCHAR(255) NULL,
  `type`    VARCHAR(255) NULL,
  `hit`     VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------------------------------------------- files
CREATE TABLE IF NOT EXISTS `files` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`    VARCHAR(255) NULL,
  `memo`    TEXT         NULL,
  `path`    VARCHAR(255) NULL,
  `statut`  VARCHAR(255) NULL,
  `type`    VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ------------------------------------------------------------ parameters
-- Table à ligne unique (paramétrage applicatif surchargeant app.php).
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

-- ---------------------------------------------------- family_import
-- Journal des imports CSV ABCM (repris de mig_family_import.sql, qui ne
-- peut pas s'exécuter sur une base vide puisqu'il commence par ALTER
-- TABLE `famille`).
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

-- ------------------------------------------------------ cantine_generation
CREATE TABLE IF NOT EXISTS `cantine_generation` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_from`  DATE         NULL,
  `date_to`    DATE         NULL,
  `ecole`      VARCHAR(3)   NULL,
  `civil_year` VARCHAR(255) NULL,
  `nb_created` INT(11)      NULL,
  `created`    DATETIME     NULL,
  `updated`    DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- =====================================================================
--  MISE À NIVEAU DES TABLES EXISTANTES
-- ---------------------------------------------------------------------
--  Les dumps du dépôt (Trombi.sql, groupes.sql, emails.sql…) datent d'une
--  version antérieure du schéma : il leur manque des colonnes que le code
--  actuel utilise. Exemple le plus bloquant : `trombi`.`ref`, sans laquelle
--  la chaîne référent (travaux → trombi → groupes_member → famille) ne
--  peut pas fonctionner, et donc ni les mails de validation ni l'écran
--  « mes sessions ».
--
--  Ce bloc ajoute uniquement ce qui manque. Il est idempotent : on peut le
--  rejouer autant de fois que nécessaire, sur une base vide comme sur une
--  base restaurée depuis la production.
-- =====================================================================

DROP PROCEDURE IF EXISTS `_add_col_if_missing`;
DELIMITER $$
CREATE PROCEDURE `_add_col_if_missing`(
  IN p_table VARCHAR(64), IN p_col VARCHAR(64), IN p_def VARCHAR(255))
BEGIN
  DECLARE v_table_exists INT DEFAULT 0;
  DECLARE v_col_exists   INT DEFAULT 0;

  SELECT COUNT(*) INTO v_table_exists FROM information_schema.TABLES
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table;

  IF v_table_exists > 0 THEN
    SELECT COUNT(*) INTO v_col_exists FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND COLUMN_NAME = p_col;

    IF v_col_exists = 0 THEN
      SET @ddl = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_col, '` ', p_def);
      PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
    END IF;
  END IF;
END$$
DELIMITER ;

-- trombi : chaîne référent + ordre d'affichage
CALL _add_col_if_missing('trombi', 'ref',   'VARCHAR(255) NULL');
CALL _add_col_if_missing('trombi', 'order', 'INT(11) NULL DEFAULT 0');

-- groupes : champs éditoriaux des commissions
CALL _add_col_if_missing('groupes', 'short',     'VARCHAR(255) NULL');
CALL _add_col_if_missing('groupes', 'hit',       'INT(11) NULL DEFAULT 0');
CALL _add_col_if_missing('groupes', 'intro',     'TEXT NULL');
CALL _add_col_if_missing('groupes', 'mission',   'TEXT NULL');
CALL _add_col_if_missing('groupes', 'actions',   'TEXT NULL');
CALL _add_col_if_missing('groupes', 'role',      'LONGTEXT NULL');
CALL _add_col_if_missing('groupes', 'needs',     'LONGTEXT NULL');
CALL _add_col_if_missing('groupes', 'search',    'LONGTEXT NULL');
CALL _add_col_if_missing('groupes', 'listorder', 'INT(11) NULL DEFAULT 0');

-- famille : évolutions successives (bcrypt, e-mails liés, alertes, année)
CALL _add_col_if_missing('famille', 'nom',         'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'prenom',      'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'e_mail_comp', 'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'capacity',    'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'alert_types', 'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'type_session','INT(10) NULL');
CALL _add_col_if_missing('famille', 'civil_year',  'VARCHAR(255) NULL');
CALL _add_col_if_missing('famille', 'role_id',     'INT(5) NULL DEFAULT 2');
CALL _add_col_if_missing('famille', 'created',     'DATETIME NULL');
CALL _add_col_if_missing('famille', 'updated',     'DATETIME NULL');

-- travaux : type, unités, archivage, année, mail référent
CALL _add_col_if_missing('travaux', 'type',            'VARCHAR(255) NULL');
CALL _add_col_if_missing('travaux', 'titre',           'VARCHAR(255) NULL');
CALL _add_col_if_missing('travaux', 'nb_units',        'FLOAT(10) NULL DEFAULT 0');
CALL _add_col_if_missing('travaux', 'txtmodel',        'TEXT NULL');
CALL _add_col_if_missing('travaux', 'type_session',    'INT(10) NULL DEFAULT 1');
CALL _add_col_if_missing('travaux', 'statut',          "VARCHAR(255) NULL DEFAULT '0'");
CALL _add_col_if_missing('travaux', 'archived',        'INT(11) NULL DEFAULT 0');
CALL _add_col_if_missing('travaux', 'civil_year',      'VARCHAR(255) NULL');
CALL _add_col_if_missing('travaux', 'ref_mail_sent_at','DATETIME NULL');
CALL _add_col_if_missing('travaux', 'alert_sent_at','DATETIME NULL');
CALL _add_col_if_missing('travaux', 'created',         'DATETIME NULL');
CALL _add_col_if_missing('travaux', 'updated',         'DATETIME NULL');

-- infos : participants, année, horodatage
CALL _add_col_if_missing('infos', 'type_participant', 'VARCHAR(255) NULL');
CALL _add_col_if_missing('infos', 'type_session',     'INT(10) NULL');
CALL _add_col_if_missing('infos', 'civil_year',       'VARCHAR(255) NULL');
CALL _add_col_if_missing('infos', 'created',          'DATETIME NULL');
CALL _add_col_if_missing('infos', 'updated',          'DATETIME NULL');

-- unites : année, archivage, horodatage
CALL _add_col_if_missing('unites', 'type_session', 'INT(10) NULL');
CALL _add_col_if_missing('unites', 'civil_year',   'VARCHAR(255) NULL');
CALL _add_col_if_missing('unites', 'archived',     'INT(11) NOT NULL DEFAULT 0');
CALL _add_col_if_missing('unites', 'created',      'DATETIME NULL');
CALL _add_col_if_missing('unites', 'updated',      'DATETIME NULL');

-- import CSV ABCM (mig_family_import.sql) : matching et désactivation
CALL _add_col_if_missing('famille', 'code_famille_abcm', 'VARCHAR(20) NULL');
CALL _add_col_if_missing('famille', 'to_deactivate',     'TINYINT(1) NOT NULL DEFAULT 0');
CALL _add_col_if_missing('members', 'code_membre_abcm',  'VARCHAR(50) NULL');
CALL _add_col_if_missing('members', 'classe',            'VARCHAR(100) NULL');

-- emails complémentaires : libellé
CALL _add_col_if_missing('emails', 'name', 'VARCHAR(255) NULL');

DROP PROCEDURE IF EXISTS `_add_col_if_missing`;

-- Élargissement des colonnes de mot de passe pour bcrypt (60 caractères).
ALTER TABLE `acl_users` MODIFY COLUMN `password` VARCHAR(255) NOT NULL;
ALTER TABLE `famille`   MODIFY COLUMN `password` VARCHAR(255) NULL;
