ALTER TABLE `famille`
  DROP `Peinture`,
  DROP `Maconnerie`,
  DROP `Electricite`,
  DROP `Chauffage`,
  DROP `Sanitaire`,
  DROP `Autre`,
  DROP `cheque_date`,
  DROP `cheque_resp`,
  DROP `type_session`;

  ALTER TABLE `infos`
  DROP `prepa_repas`,
  DROP `prend_repas`,
  DROP `nb_enfants_a_garder`,
  DROP `Monsieur`,
  DROP `Madame`,
  DROP `lesdeux`;

  ALTER TABLE `trombi`
  DROP `num_tel`,
  DROP `title`,
  DROP `description`,
  DROP `color`;