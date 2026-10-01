-- Migration CodeIgniter 3 -> 4 : table des sessions (DatabaseHandler CI4).
-- CI4 stocke la date de derniere activite dans une colonne TIMESTAMP
-- (CI3 utilisait un entier UNIX). Les sessions existantes sont purgees :
-- les utilisateurs devront se reconnecter apres la bascule.

DROP TABLE IF EXISTS `ci_sessions`;

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`, `ip_address`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
