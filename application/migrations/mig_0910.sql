//MIGRATION SQL
ALTER TABLE `unites` ADD `civil_year` VARCHAR(255) NULL AFTER `type_session`;
UPDATE `unites` SET `civil_year` = '2022-2023';
ALTER TABLE `unites` ADD `archived` INT(11) NULL AFTER `civil_year`;

ALTER TABLE `infos` ADD `civil_year` VARCHAR(255) NULL AFTER `nb_unites_valides_effectif`;
UPDATE `infos` SET `civil_year` = '2022-2023';

ALTER TABLE `travaux` ADD `archived` INT(11) NULL AFTER `accespar`;
update `travaux` set `archived` = 1 WHERE `date_travaux` <= '2023-05-31';
ALTER TABLE `unites` CHANGE `archived` `archived` INT(11) NOT NULL DEFAULT '0';

update `travaux` set `civil_year` = '2022-2023' WHERE `date_travaux` <= '2023-05-31';
update `travaux` set `civil_year` = '2023-2024' WHERE `date_travaux` > '2023-05-31';