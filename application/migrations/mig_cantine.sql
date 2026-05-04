-- =====================================================================
-- MIGRATION : Module Cantine (Garde du midi)
-- =====================================================================

-- Configuration des jours de garde (1 ligne par jour de la semaine)
--   id_day : 1 = Lundi, 2 = Mardi, 3 = Mercredi, 4 = Jeudi, 5 = Vendredi
--   active : 1 si une garde est nécessaire ce jour, 0 sinon
--   nb_slots : nombre de parents requis ce jour
--   ecole : 'M' / 'L' / 'B' (comme travaux)
--   nb_units : nombre d'unités associatives accordées par participation
--   id_referent : id du référent (table trombi) qui validera les unités
CREATE TABLE `cantine_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_day` TINYINT(1) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 0,
  `nb_slots` INT(2) NOT NULL DEFAULT 0,
  `ecole` VARCHAR(3) NOT NULL DEFAULT 'B',
  `nb_units` FLOAT(10) NOT NULL DEFAULT 1,
  `id_referent` VARCHAR(255) NULL,
  `heure_deb` VARCHAR(10) NOT NULL DEFAULT '11:45',
  `heure_fin` VARCHAR(10) NOT NULL DEFAULT '13:30',
  `civil_year` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_day_ecole_year` (`id_day`, `ecole`, `civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Valeurs par défaut : lundi, mardi, jeudi, vendredi actifs (2 parents chacun, 1 unité)
INSERT INTO `cantine_config` (`id_day`,`active`,`nb_slots`,`ecole`,`nb_units`,`civil_year`,`created`,`updated`) VALUES
(1,1,2,'M',1,'2025-2026', NOW(), NOW()),
(2,1,2,'M',1,'2025-2026', NOW(), NOW()),
(3,0,0,'M',1,'2025-2026', NOW(), NOW()),
(4,1,2,'M',1,'2025-2026', NOW(), NOW()),
(5,1,2,'M',1,'2025-2026', NOW(), NOW());

-- Inscriptions (1 ligne = 1 famille inscrite pour 1 date)
--   id_info : lien vers la ligne infos créée (pour la validation référent)
--   id_travaux : lien vers le pseudo-travail créé à cette date
CREATE TABLE `cantine_inscriptions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date_garde` DATE NOT NULL,
  `id_famille` INT(11) NOT NULL,
  `ecole` VARCHAR(3) NOT NULL DEFAULT 'B',
  `id_info` INT(11) NULL,
  `id_travaux` INT(11) NULL,
  `civil_year` VARCHAR(255) NULL,
  `created` DATETIME NULL,
  `updated` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date_fam` (`date_garde`,`id_famille`),
  KEY `idx_date` (`date_garde`),
  KEY `idx_famille` (`id_famille`),
  KEY `idx_info` (`id_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

