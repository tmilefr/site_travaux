-- Journal des generations cantine : le code et le schema JSON utilisent date_deb / date_fin / nb_skipped,
-- alors que certaines bases (jeu de test) avaient date_from / date_to (la generation echouait sur "Unknown column 'date_deb'").
-- Rejouable sans risque (MariaDB >= 10.0 : IF EXISTS / IF NOT EXISTS).
ALTER TABLE `cantine_generation`
  CHANGE COLUMN IF EXISTS `date_from` `date_deb` DATE NULL,
  CHANGE COLUMN IF EXISTS `date_to`   `date_fin` DATE NULL,
  ADD COLUMN IF NOT EXISTS `nb_skipped` INT(11) NULL AFTER `nb_created`;
