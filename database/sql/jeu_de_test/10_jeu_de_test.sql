-- =====================================================================
--  SITE_TRAVAUX — JEU DE TEST DE RECETTE
-- ---------------------------------------------------------------------
--  Charge le jeu de données de référence décrit dans le plan de recette
--  (Plan_de_recette_site_travaux.docx, § 5.3) et utilisé par les cas de
--  test du classeur plan_tests_site_travaux.xlsx.
--
--  ATTENTION — À N'EXÉCUTER QUE SUR UN ENVIRONNEMENT DE RECETTE.
--  Ne jamais charger ce fichier en production.
--
--  Toutes les données créées portent des identifiants dans la plage 9000+
--  et un marqueur textuel ([RECETTE] / ZZTEST), ce qui permet de les
--  retirer proprement avec 90_purge_jeu_de_test.sql sans toucher aux
--  données réelles.
--
--  Le script est REJOUABLE : il purge d'abord ses propres lignes.
--
--  Les mots de passe ne sont pas stockés dans ce fichier : ils sont
--  générés au chargement (voir plus bas et charger_jeu_de_test.sh).
-- =====================================================================

SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
--  PARAMÈTRES — à ajuster avant exécution
-- ---------------------------------------------------------------------
--  @CY doit être STRICTEMENT identique à $config['civil_year']
--  (application/config/app.php), sinon aucune session ne s'affichera.
SET @CY      = '2025-2026';   -- année civile de la campagne en cours
SET @CY_PREV = '2024-2025';   -- année précédente (test de cloisonnement)

--  Domaine des adresses de test. Choisir un domaine qui n'existe pas,
--  pour qu'aucun message ne puisse partir vers une vraie boîte.
SET @MAIL_DOM = 'recette.local';

--  MOTS DE PASSE DES COMPTES DE TEST
-- ---------------------------------------------------------------------
--  ⚠️  REMPLACEZ LA VALEUR CI-DESSOUS AVANT D'IMPORTER CE FICHIER.
--
--  Le jeu de données se charge quoi qu'il arrive : si vous laissez la
--  valeur par défaut, les sessions, commissions et inscriptions seront
--  bien créées, mais les comptes de test ne pourront pas se connecter.
--  Le récapitulatif en fin de script vous le dira.
SET @MDP_CLAIR = 'CHANGEZ-MOI';

--  Aucun mot de passe n'est stocké dans ce fichier : MySQL calcule
--  lui-même le hash MD5 à partir de la valeur ci-dessus. L'application
--  accepte ce format pour les comptes famille et le remplace
--  automatiquement par un hash bcrypt à la première connexion — c'est
--  précisément le comportement que vérifie le cas de test T1-10.
--
--  Les comptes d'ADMINISTRATION (table acl_users) exigent en revanche un
--  hash bcrypt, que MySQL ne sait pas produire. Deux possibilités :
--    • utiliser votre compte administrateur habituel, qui suffit pour
--      toute la recette ;
--    • ou coller un hash bcrypt ci-dessous, généré avec :
--          php -r "echo password_hash('VotreMotDePasse', PASSWORD_BCRYPT);"
SET @HASH_BCRYPT = '';

--  Les lignes suivantes se débrouillent seules : ne pas les modifier.
--  Elles respectent les valeurs déjà définies par charger_jeu_de_test.sh.
SET @PWD_INVALIDE = '*mot-de-passe-non-defini*';
SET @PWD_MD5    = COALESCE(NULLIF(@PWD_MD5, ''),
                    IF(@MDP_CLAIR = 'CHANGEZ-MOI' OR @MDP_CLAIR = '' OR @MDP_CLAIR IS NULL,
                       @PWD_INVALIDE, MD5(@MDP_CLAIR)));
SET @PWD_BCRYPT = COALESCE(NULLIF(@PWD_BCRYPT, ''), NULLIF(@HASH_BCRYPT, ''), @PWD_INVALIDE);
--  Mot de passe des comptes famille : bcrypt s'il a été fourni, sinon MD5.
SET @PWD_FAM    = IF(@PWD_BCRYPT LIKE '$2y$%' OR @PWD_BCRYPT LIKE '$2a$%',
                     @PWD_BCRYPT, @PWD_MD5);

SET @NOW = NOW();
--  Lundi de la semaine en cours, pour les créneaux de cantine.
SET @LUNDI = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

-- ---------------------------------------------------------------------
--  PURGE PRÉALABLE (rejouabilité)
-- ---------------------------------------------------------------------
DELETE FROM `infos`             WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `unites`            WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `validation_tokens` WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `travaux`           WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `candidatures`      WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `trombi`            WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `groupes_member`    WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `groupes`           WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `members`           WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `emails`            WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `famille_alerts`    WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `famille`           WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `acl_users`         WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `acl_roles_controllers` WHERE `id_role` BETWEEN 9000 AND 9999;
DELETE FROM `acl_roles`         WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `sendmail`          WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `cantine_inscriptions` WHERE `id` BETWEEN 9000 AND 9999;
DELETE FROM `cantine_config`    WHERE `id` BETWEEN 9000 AND 9999;

-- =====================================================================
--  1. COMPTES D'ADMINISTRATION (table acl_users, profil « sys »)
-- =====================================================================
--  Rôle ACL restreint, pour vérifier le cloisonnement entre deux profils
--  administrateurs (cas T10-xx). Ses droits sont recopiés depuis le rôle
--  Famille (id 2), ce qui en fait un rôle valide mais volontairement
--  limité.
INSERT INTO `acl_roles` (`id`,`role_name`,`role_description`,`created`,`updated`) VALUES
 (9001,'[RECETTE] Profil restreint','Rôle de recette : droits volontairement limités',@NOW,@NOW);

INSERT INTO `acl_roles_controllers` (`id_role`,`id_ctrl`,`id_act`,`allow`)
  SELECT 9001, `id_ctrl`, `id_act`, `allow`
    FROM `acl_roles_controllers` WHERE `id_role` = 2;

INSERT INTO `acl_users` (`id`,`name`,`login`,`password`,`role_id`,`created`,`updated`,`recaptchaResponse`) VALUES
 (9001,'[RECETTE] Administrateur', CONCAT('admin.recette@',@MAIL_DOM), @PWD_BCRYPT, 1, @NOW, @NOW, ''),
 (9002,'[RECETTE] Profil restreint', CONCAT('restreint.recette@',@MAIL_DOM), @PWD_BCRYPT, 9001, @NOW, @NOW, '');

-- =====================================================================
--  2. FAMILLES (table famille, profil « fam »)
-- =====================================================================
INSERT INTO `famille`
 (`id`,`idfamille`,`code_famille_abcm`,`role_id`,`nom`,`prenom`,`login`,`password`,`cp`,`ville`,`adresse`,
  `e_mail`,`e_mail_comp`,`nb_enfants`,`ecole`,`capacity`,`alert_types`,`civil_year`,`to_deactivate`,`created`,`updated`)
VALUES
 -- U-FAM1 : famille de référence du parcours nominal, école Mulhouse, 0 unité
 (9001,'ZZTEST001','ZZTEST001',2,'Martin','Claire', CONCAT('famille1@',@MAIL_DOM), @PWD_FAM,
  '68100','Mulhouse','12 rue des Tests', CONCAT('famille1@',@MAIL_DOM), NULL,
  '2','M','pein,elec','WORK_REGISTER',@CY,0,@NOW,@NOW),

 -- U-FAM2 : école Lutterbach, possède déjà des unités validées
 (9002,'ZZTEST002','ZZTEST002',2,'Dubois','Marc', CONCAT('famille2@',@MAIL_DOM), @PWD_FAM,
  '68460','Lutterbach','8 avenue de la Recette', CONCAT('famille2@',@MAIL_DOM), NULL,
  '1','L','mac',NULL,@CY,0,@NOW,@NOW),

 -- U-REF : famille référente (chaîne trombi → groupes_member → famille)
 (9003,'ZZTEST003','ZZTEST003',2,'Leroy','Sophie', CONCAT('referent@',@MAIL_DOM), @PWD_FAM,
  '68100','Mulhouse','3 place du Référent', CONCAT('referent@',@MAIL_DOM), NULL,
  '2','M','inf','WORK_REGISTER,WORK_UNREGISTER',@CY,0,@NOW,@NOW),

 -- U-LEGACY : mot de passe encore en MD5, doit migrer en bcrypt au login
 (9004,'ZZTEST004','ZZTEST004',2,'Ancien','Paul', CONCAT('legacy@',@MAIL_DOM), @PWD_MD5,
  '68100','Mulhouse','1 impasse Héritage', CONCAT('legacy@',@MAIL_DOM), NULL,
  '1','M',NULL,NULL,@CY,0,@NOW,@NOW),

 -- U-FAM3 : école « les deux », utilisée pour la cantine et la concurrence
 (9005,'ZZTEST005','ZZTEST005',2,'Petit','Julie', CONCAT('famille3@',@MAIL_DOM), @PWD_FAM,
  '68200','Mulhouse','45 rue Partagée', CONCAT('famille3@',@MAIL_DOM), NULL,
  '3','B','chau,san',NULL,@CY,0,@NOW,@NOW),

 -- U-FAM4 : cible de l'import CSV (sera modifiée par import_ok.csv)
 (9006,'ZZTEST006','ZZTEST006',2,'Import','Test', CONCAT('famille.import@',@MAIL_DOM), @PWD_FAM,
  '68100','Mulhouse','Adresse a mettre a jour', CONCAT('famille.import@',@MAIL_DOM), NULL,
  '1','M',NULL,NULL,@CY,0,@NOW,@NOW);

-- E-mails complémentaires de U-FAM1 (table liée, cas T12-03 / FAM-04)
INSERT INTO `emails` (`id`,`id_fam`,`email`,`created`,`updated`) VALUES
 (9001,9001, CONCAT('conjoint.famille1@',@MAIL_DOM),@NOW,@NOW),
 (9002,9001, CONCAT('secours.famille1@',@MAIL_DOM),@NOW,@NOW);

-- Préférences d'alerte : U-FAM1 abonnée, U-FAM2 volontairement non abonnée
-- (cas T9-07 : une famille désabonnée ne doit recevoir aucun message).
INSERT INTO `famille_alerts` (`id`,`id_fam`,`id_type`,`created`,`updated`) VALUES
 (9001,9001,'WORK_REGISTER',@NOW,@NOW),
 (9002,9003,'WORK_REGISTER',@NOW,@NOW);

-- Enfants rattachés (table members), pour l'import et l'affichage famille
INSERT INTO `members` (`id`,`id_fam`,`code_membre_abcm`,`type`,`nom`,`prenom`,`classe`,`created`,`updated`) VALUES
 (9001,9001,'ZZM001','enfant','Martin','Lea','2025 2026 MUL CL03',@NOW,@NOW),
 (9002,9001,'ZZM002','enfant','Martin','Tom','2025 2026 MUL CL05',@NOW,@NOW),
 (9003,9002,'ZZM003','enfant','Dubois','Emma','2025 2026 LUT CL02',@NOW,@NOW),
 (9004,9006,'ZZM004','enfant','Import','Enfant1','2025 2026 MUL CL01',@NOW,@NOW);

-- =====================================================================
--  3. ORGANIGRAMME, COMMISSIONS ET RÉFÉRENTS DE SESSION
-- ---------------------------------------------------------------------
--  Un référent ne peut être proposé sur une session que s'il est déclaré
--  comme membre d'une commission. L'application construit la liste
--  déroulante « Référent » du formulaire de session avec cette requête
--  (cf. Travaux.json, champ referent_travaux) :
--
--      SELECT tr.id, CONCAT_WS(' ', gr.short, gm.name, gm.surname) AS title
--        FROM trombi tr
--        LEFT JOIN groupes_member gm ON tr.ref = gm.id
--        LEFT JOIN groupes gr        ON tr.id_grp = gr.id
--       WHERE tr.classif IN ('reftra','RT');
--
--  Trois conditions sont donc nécessaires pour qu'un référent apparaisse :
--    1. une ligne `groupes_member` rattachée à la famille (id_fam) ;
--    2. une ligne `trombi` rattachant ce membre à une commission (id_grp)
--       avec classif = 'reftra' (Référent de session) ou 'RT'
--       (Responsable de commission) ;
--    3. la commission doit porter un libellé court (`groupes`.`short`),
--       qui sert de préfixe dans la liste déroulante.
--
--  Chaîne complète utilisée ensuite pour retrouver la famille référente
--  d'une session :
--      travaux.referent_travaux = trombi.id
--      trombi.ref               = groupes_member.id
--      groupes_member.id_fam    = famille.id
-- =====================================================================
INSERT INTO `groupes` (`id`,`title`,`short`,`color`,`type`,`created`,`updated`) VALUES
 (9001,'[RECETTE] Commission Travaux','TRAVAUX','nicdark_bg_blue','com',@NOW,@NOW),
 (9002,'[RECETTE] Bureau','BUREAU','nicdark_bg_green','org',@NOW,@NOW);

INSERT INTO `groupes_member` (`id`,`id_fam`,`name`,`surname`,`email`,`phone`,`created`,`updated`) VALUES
 (9001,'9003','Leroy','Sophie', CONCAT('referent@',@MAIL_DOM),'0300000001',@NOW,@NOW),
 (9002,'9001','Martin','Claire', CONCAT('famille1@',@MAIL_DOM),'0300000002',@NOW,@NOW),
 -- Membre volontairement incohérent : id_fam non numérique. Il apparaît
 -- dans la liste des référents, mais la jointure vers famille ne remonte
 -- aucune ligne — sans erreur SQL (cas T14-09, T9-08).
 (9003,'REF-INCONNU','Chaine','Rompue', CONCAT('chaine.rompue@',@MAIL_DOM),'0300000003',@NOW,@NOW),
 -- Membre sans photo, pour vérifier l'image de remplacement (cas T11-10)
 (9004,'9005','Petit','Julie', CONCAT('famille3@',@MAIL_DOM),'0300000004',@NOW,@NOW);

--  classif : 'reftra' = Référent de session, 'RT' = Responsable de
--  commission, 'ME' = Membre simple, 'TR' = Trésorier.
--  Seuls 'reftra' et 'RT' alimentent la liste déroulante des sessions.
--
--  Colonnes : strictement celles du modèle Trombi.json. Les anciens dumps
--  du dépôt comportent des colonnes supplémentaires (photo, nom, num_tel,
--  email, title, description, color, ref_travaux) qui n'existent plus en
--  production ; les lister ici ferait échouer l'import.
INSERT INTO `trombi` (`id`,`id_grp`,`ref`,`classif`,`order`,`created`,`updated`) VALUES
 -- Référente principale : c'est elle qui est désignée sur les sessions
 (9001,9001,'9001','reftra',1,@NOW,@NOW),

 -- Référente dont la chaîne est rompue : proposée dans la liste, mais
 -- aucune famille ne peut être retrouvée derrière.
 (9002,9001,'9003','reftra',2,@NOW,@NOW),

 -- Second référent, pour que la liste déroulante offre un vrai choix
 (9003,9001,'9002','reftra',3,@NOW,@NOW),

 -- Responsable de commission : apparaît aussi dans la liste (classif 'RT')
 (9004,9002,'9004','RT',1,@NOW,@NOW),

 -- Membre simple : ne doit PAS apparaître dans la liste des référents
 (9005,9001,'9004','ME',4,@NOW,@NOW);

-- Candidature en attente de traitement (cas T14-07 / T14-08)
INSERT INTO `candidatures` (`id`,`id_fam`,`id_grp`,`name`,`surname`,`email`,`phone`,`memo`,`created`,`updated`) VALUES
 (9001,'9002',9001,'Dubois','Marc', CONCAT('famille2@',@MAIL_DOM),'0300000005',
  'Candidature de recette : à accepter puis refuser pour tester le circuit.',@NOW,@NOW);

-- =====================================================================
--  4. SESSIONS DE TRAVAUX
-- ---------------------------------------------------------------------
--  Les dates sont RELATIVES à la date d'exécution : le jeu reste valide
--  quel que soit le jour où il est chargé.
--    statut       0 = brouillon, 1 = publié
--    type_session 1 = Horaire,   2 = Action
--    accespar     M = Mulhouse,  L = Lutterbach, B = les deux
-- =====================================================================
INSERT INTO `travaux`
 (`id`,`date_travaux`,`heure_deb_trav`,`heure_fin_trav`,`type`,`nb_units`,`titre`,`description`,
  `nb_inscrits_max`,`referent_travaux`,`ecole`,`accespar`,`archived`,`civil_year`,
  `type_session`,`statut`,`created`,`updated`)
VALUES
 -- T-BROUILLON : ne doit JAMAIS apparaître côté famille (cas T2-15)
 (9001, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 20 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Session en brouillon','Session non publiée : invisible côté famille.',
  '6','9001','M','M',0,@CY,1,'0',@NOW,@NOW),

 -- T-FUTUR : session de référence pour l'inscription (cas T3-01 et suivants)
 (9002, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 15 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Travaux - session ouverte','Session publiée avec 4 places. Support des tests d''inscription.',
  '4','9001','M','M',0,@CY,1,'1',@NOW,@NOW),

 -- T-PLEIN : 2 places, déjà 2 inscrits (cas T3-06 : session complète)
 (9003, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 10 DAY),'%Y-%m-%d'),'14:00','17:00','MEN',1,
  '[RECETTE] Menage - session complete','Toutes les places sont prises : l''inscription doit être refusée.',
  '2','9001','B','B',0,@CY,1,'1',@NOW,@NOW),

 -- T-PASSE : session passée avec inscrits non validés (validation référent)
 (9004, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Travaux - a valider','Session passée, présences à valider par le référent.',
  '6','9001','M','M',0,@CY,1,'1',@NOW,@NOW),

 -- T-VIEUX : plus de 30 jours, doit être archivée automatiquement (T8-01)
 (9005, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 45 DAY),'%Y-%m-%d'),'09:00','11:00','GOU',1,
  '[RECETTE] Gouter - a archiver','Session de plus de 30 jours : l''archivage automatique doit la masquer.',
  '5','9001','M','M',0,@CY,2,'1',@NOW,@NOW),

 -- T-URG : sans date, ne doit JAMAIS être archivée (cas T8-03 / T2-21)
 (9006, '','','','URG',1,
  '[RECETTE] Urgence - sans date','Session d''urgence : pas de date, jamais archivée.',
  '10','9001','B','B',0,@CY,2,'1',@NOW,@NOW),

 -- T-LUT : réservée à Lutterbach, invisible pour une famille de Mulhouse
 (9007, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 12 DAY),'%Y-%m-%d'),'09:00','12:00','LAV',2,
  '[RECETTE] Lavage - Lutterbach uniquement','Cloisonnement par école : invisible pour une famille Mulhouse.',
  '6','9001','L','L',0,@CY,1,'1',@NOW,@NOW),

 -- T-AN-1 : année civile précédente (cas T2-18 : cloisonnement par année)
 (9008, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 300 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Session annee precedente','Rattachée à l''année civile précédente : ne doit pas apparaître.',
  '6','9001','M','M',0,@CY_PREV,1,'1',@NOW,@NOW),

 -- T-ACTION : session de type Action, passée (bug d'agrégation de l'écran valid)
 (9009, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 DAY),'%Y-%m-%d'),'','','DEC',3,
  '[RECETTE] Dechetterie - type Action','Session de type Action : doit s''afficher séparément dans la file de validation.',
  '4','9001','M','M',0,@CY,2,'1',@NOW,@NOW),

 -- T-REF-KO : référent dont la chaîne est rompue (cas T9-08 / T14-09)
 (9010, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 6 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Session sans referent joignable','La chaîne référent est rompue : aucun mail ne doit partir, sans erreur.',
  '6','9002','M','M',0,@CY,1,'1',@NOW,@NOW),

 -- T-DERNIERE-PLACE : 3 places, 2 prises (cas T3-11 : concurrence).
 -- Rattachée au SECOND référent (trombi 9003 → Martin Claire), pour que le
 -- jeu exerce deux référents distincts : « mes sessions » doit donner des
 -- listes différentes selon la famille connectée.
 (9011, DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 8 DAY),'%Y-%m-%d'),'09:00','12:00','TRA',2,
  '[RECETTE] Une seule place restante','Support du test de deux inscriptions simultanées sur la dernière place.',
  '3','9003','B','B',0,@CY,1,'1',@NOW,@NOW);

-- Créneaux de cantine de la semaine en cours (type 'can', lundi à vendredi).
-- Le mercredi est volontairement absent, conformément à la configuration.
INSERT INTO `travaux`
 (`id`,`date_travaux`,`heure_deb_trav`,`heure_fin_trav`,`type`,`nb_units`,`titre`,`description`,
  `nb_inscrits_max`,`referent_travaux`,`ecole`,`accespar`,`archived`,`civil_year`,
  `type_session`,`statut`,`created`,`updated`)
VALUES
 (9020, DATE_FORMAT(@LUNDI,'%Y-%m-%d'),                      '11:45','13:30','can',1,
  '[RECETTE] Cantine lundi','Créneau de garde du midi.','4','9001','M','M',0,@CY,1,'1',@NOW,@NOW),
 (9021, DATE_FORMAT(DATE_ADD(@LUNDI, INTERVAL 1 DAY),'%Y-%m-%d'),'11:45','13:30','can',1,
  '[RECETTE] Cantine mardi','Créneau de garde du midi.','4','9001','M','M',0,@CY,1,'1',@NOW,@NOW),
 (9023, DATE_FORMAT(DATE_ADD(@LUNDI, INTERVAL 3 DAY),'%Y-%m-%d'),'11:45','13:30','can',1,
  '[RECETTE] Cantine jeudi','Créneau de garde du midi.','4','9001','M','M',0,@CY,1,'1',@NOW,@NOW),
 (9024, DATE_FORMAT(DATE_ADD(@LUNDI, INTERVAL 4 DAY),'%Y-%m-%d'),'11:45','13:30','can',1,
  '[RECETTE] Cantine vendredi','Créneau saturé : 1 place, 1 inscrit.','1','9001','M','M',0,@CY,1,'1',@NOW,@NOW);

-- =====================================================================
--  5. INSCRIPTIONS (table infos)
-- ---------------------------------------------------------------------
--  nb_unites_valides          = unités prévues  (nb_units x nb_participants)
--  nb_unites_valides_effectif = unités validées (0 tant que non validé)
-- =====================================================================
INSERT INTO `infos`
 (`id`,`id_famille`,`id_travaux`,`heure_debut_prevue`,`heure_fin_prevue`,
  `nb_unites_valides`,`nb_participants`,`type_participant`,`nb_unites_valides_effectif`,
  `type_session`,`civil_year`,`created`,`updated`)
VALUES
 -- T-PLEIN (9003) : les 2 places sont prises par U-FAM2 et U-FAM3,
 -- pour que U-FAM1 puisse tester le refus « session complète ».
 (9001,'9002',9003,'14:00','17:00',1,1,'Mr',0,1,@CY,@NOW,@NOW),
 (9002,'9005',9003,'14:00','17:00',1,1,'Mme',0,1,@CY,@NOW,@NOW),

 -- T-PASSE (9004) : 3 inscrits, aucun validé → file de validation référent
 (9003,'9001',9004,'09:00','12:00',2,1,'Mr',0,1,@CY,@NOW,@NOW),
 (9004,'9002',9004,'09:00','12:00',4,2,'Both',0,1,@CY,@NOW,@NOW),
 (9005,'9005',9004,'09:00','12:00',2,1,'Mme',0,1,@CY,@NOW,@NOW),

 -- T-ACTION (9009) : 2 inscrits non validés, session de type Action
 (9006,'9001',9009,'','',3,1,'Mr',0,2,@CY,@NOW,@NOW),
 (9007,'9002',9009,'','',6,2,'Both',0,2,@CY,@NOW,@NOW),

 -- T-DERNIERE-PLACE (9011) : 2 des 3 places occupées
 (9008,'9002',9011,'09:00','12:00',2,1,'Mr',0,1,@CY,@NOW,@NOW),
 (9009,'9005',9011,'09:00','12:00',2,1,'Mme',0,1,@CY,@NOW,@NOW),

 -- T-VIEUX (9005) : session déjà validée → U-FAM2 possède des unités
 (9010,'9002',9005,'09:00','11:00',1,1,'Mr',1,1,@CY,@NOW,@NOW),

 -- Inscription de U-FAM1 sur la session future, pour tester la désinscription
 (9011,'9001',9002,'09:00','12:00',2,1,'Mr',0,1,@CY,@NOW,@NOW),

 -- Cantine vendredi (9024) : créneau saturé (1 place, 1 inscrit)
 (9012,'9005',9024,'11:45','13:30',1,1,'Mme',0,1,@CY,@NOW,@NOW);

INSERT INTO `cantine_inscriptions`
 (`id`,`date_garde`,`id_famille`,`ecole`,`id_info`,`id_travaux`,`civil_year`,`created`,`updated`) VALUES
 (9001, DATE_ADD(@LUNDI, INTERVAL 4 DAY), 9005,'M',9012,9024,@CY,@NOW,@NOW);

-- =====================================================================
--  6. UNITÉS COMPLÉMENTAIRES (hors session)
-- =====================================================================
INSERT INTO `unites`
 (`id`,`unites_id_famille`,`unites_date`,`unites_valides`,`unites_desc`,`unites_comm`,
  `type_session`,`civil_year`,`archived`,`created`,`updated`)
VALUES
 (9001,'9002', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 20 DAY),'%Y-%m-%d'),'3',
  '[RECETTE] Unités complémentaires','Don de matériel : 3 unités accordées hors session.',1,@CY,0,@NOW,@NOW),
 (9002,'9003', DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 15 DAY),'%Y-%m-%d'),'2',
  '[RECETTE] Unités référent','Animation de session : 2 unités accordées.',1,@CY,0,@NOW,@NOW);

-- =====================================================================
--  7. JETONS DE VALIDATION RÉFÉRENT
-- ---------------------------------------------------------------------
--  Trois jetons couvrant les trois comportements attendus (cas T5-03 à
--  T5-11). Ces valeurs sont FIXES et donc publiques : elles ne doivent
--  exister que sur un environnement de recette.
--
--  URL de test : <base_url>/Admwork_controller/validate_by_token/<token>
-- =====================================================================
INSERT INTO `validation_tokens` (`id`,`id_travaux`,`id_fam_ref`,`token`,`expires_at`,`used_at`,`created`) VALUES
 -- Jeton VALIDE sur la session passée 9004 → la validation doit s'ouvrir
 (9001,9004,9003,'a1b2c3d4e5f60718293a4b5c6d7e8f90a1b2c3d4e5f60718293a4b5c6d7e8f90',
  DATE_ADD(@NOW, INTERVAL 25 DAY), NULL, @NOW),
 -- Jeton EXPIRÉ sur la session 9009 → refus attendu
 (9002,9009,9003,'b1b2c3d4e5f60718293a4b5c6d7e8f90a1b2c3d4e5f60718293a4b5c6d7e8f91',
  DATE_SUB(@NOW, INTERVAL 1 DAY), NULL, DATE_SUB(@NOW, INTERVAL 31 DAY)),
 -- Jeton DÉJÀ UTILISÉ sur la session 9005 → refus attendu, pas de double comptage
 (9003,9005,9003,'c1b2c3d4e5f60718293a4b5c6d7e8f90a1b2c3d4e5f60718293a4b5c6d7e8f92',
  DATE_ADD(@NOW, INTERVAL 10 DAY), DATE_SUB(@NOW, INTERVAL 2 DAY), DATE_SUB(@NOW, INTERVAL 5 DAY));

-- =====================================================================
--  8. CONFIGURATION CANTINE
-- ---------------------------------------------------------------------
--  INSERT IGNORE : la migration mig_cantine.sql a peut-être déjà créé
--  ces lignes (clé unique jour + école + année). Dans ce cas on conserve
--  la configuration existante.
-- =====================================================================
INSERT IGNORE INTO `cantine_config`
 (`id`,`id_day`,`active`,`nb_slots`,`ecole`,`nb_units`,`id_referent`,`heure_deb`,`heure_fin`,`civil_year`,`created`,`updated`) VALUES
 (9001,1,1,4,'M',1,'9001','11:45','13:30',@CY,@NOW,@NOW),
 (9002,2,1,4,'M',1,'9001','11:45','13:30',@CY,@NOW,@NOW),
 (9003,3,0,0,'M',1,'9001','11:45','13:30',@CY,@NOW,@NOW),
 (9004,4,1,4,'M',1,'9001','11:45','13:30',@CY,@NOW,@NOW),
 (9005,5,1,1,'M',1,'9001','11:45','13:30',@CY,@NOW,@NOW);

-- =====================================================================
--  9. FILE D'ENVOI D'E-MAILS
-- ---------------------------------------------------------------------
--  Trois messages en attente (statut 0) pour tester le cron d'envoi
--  (cas T9-02, T9-13). Les adresses pointent sur un domaine inexistant :
--  vérifier malgré tout que le SMTP de recette est bien une boîte de
--  capture avant de lancer le cron.
-- =====================================================================
INSERT INTO `sendmail` (`id`,`reference`,`email`,`object`,`message`,`statut`,`created`,`updated`) VALUES
 (9001,'RECETTE-01', CONCAT('famille1@',@MAIL_DOM),'[RECETTE] Message de test 1',
  '<p>Message de test n°1 de la campagne de recette.</p>',0,@NOW,@NOW),
 (9002,'RECETTE-02', CONCAT('famille2@',@MAIL_DOM),'[RECETTE] Message de test 2',
  '<p>Message de test n°2 de la campagne de recette.</p>',0,@NOW,@NOW),
 (9003,'RECETTE-03', CONCAT('referent@',@MAIL_DOM),'[RECETTE] Message de test 3',
  '<p>Message de test n°3 de la campagne de recette.</p>',0,@NOW,@NOW);

-- =====================================================================
--  RÉCAPITULATIF
-- =====================================================================
SELECT '--- Jeu de test chargé ---' AS ``;

SELECT 'Comptes admin'    AS `Objet`, CAST((SELECT COUNT(*) FROM acl_users WHERE id BETWEEN 9000 AND 9999) AS CHAR) AS `Nombre`
UNION ALL SELECT 'Familles',          CAST((SELECT COUNT(*) FROM famille   WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Commissions',       CAST((SELECT COUNT(*) FROM groupes   WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Référents declares',CAST((SELECT COUNT(*) FROM trombi    WHERE id BETWEEN 9000 AND 9999 AND classif IN ('reftra','RT')) AS CHAR)
UNION ALL SELECT 'Sessions de travail',CAST((SELECT COUNT(*) FROM travaux  WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Inscriptions',      CAST((SELECT COUNT(*) FROM infos     WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Unités complém.',   CAST((SELECT COUNT(*) FROM unites    WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Jetons référent',   CAST((SELECT COUNT(*) FROM validation_tokens WHERE id BETWEEN 9000 AND 9999) AS CHAR)
UNION ALL SELECT 'Mails en file',     CAST((SELECT COUNT(*) FROM sendmail  WHERE id BETWEEN 9000 AND 9999) AS CHAR);

--  Contrôles à lire attentivement : ils expliquent la plupart des cas
--  « je ne vois rien dans l'application ».
SELECT 'Annee civile du jeu' AS `Controle`,
       @CY AS `Valeur`,
       CONCAT('Doit etre identique a $config[civil_year]. Sinon aucune session ne s affichera.') AS `Remarque`
UNION ALL
SELECT 'Mot de passe des familles',
       IF(@PWD_FAM = @PWD_INVALIDE, 'NON DEFINI', 'defini'),
       IF(@PWD_FAM = @PWD_INVALIDE,
          'ATTENTION : remplacez @MDP_CLAIR en tete de ce fichier puis reimportez-le. Les donnees sont la, mais les comptes famille ne peuvent pas se connecter.',
          'Les comptes famille peuvent se connecter avec le mot de passe saisi.')
UNION ALL
SELECT 'Mot de passe des comptes admin',
       IF(@PWD_BCRYPT = @PWD_INVALIDE, 'NON DEFINI', 'defini'),
       IF(@PWD_BCRYPT = @PWD_INVALIDE,
          'Normal si vous utilisez votre propre compte administrateur. Sinon renseignez @PWD_BCRYPT.',
          'Les comptes admin de recette peuvent se connecter.')
UNION ALL
SELECT 'Sessions visibles cote famille (ecole M)',
       CAST((SELECT COUNT(*) FROM travaux
              WHERE id BETWEEN 9000 AND 9999 AND statut = 1 AND COALESCE(archived,0) <> 1
                AND accespar IN ('B','M') AND type <> 'can'
                AND (type = 'URG' OR date_travaux >= CURDATE())) AS CHAR),
       'Si 0, verifier @CY ci-dessus et le filtre ecole de la famille connectee.'
UNION ALL
SELECT 'Referents proposables sur une session',
       CAST((SELECT COUNT(*) FROM trombi tr
              LEFT JOIN groupes_member gm ON tr.ref = gm.id
             WHERE tr.classif IN ('reftra','RT') AND gm.id IS NOT NULL) AS CHAR),
       'Compte TOUS les referents de la base, pas seulement ceux du jeu de test.';
