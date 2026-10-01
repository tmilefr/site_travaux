# Plan de test — `site_travaux`

> Application de gestion des travaux associatifs et de la participation des familles
> (ABCM Mulhouse‑Lutterbach) — CodeIgniter 4 / PHP / MySQL.

| | |
|---|---|
| **Version du plan** | 1.0 |
| **Périmètre** | Application web complète + API REST + tâches cron |
| **Environnement de recette** | `https://regio.dev-asso.fr` (branche `develop`) |
| **Environnement de production** | `https://mulhouse-travaux.abcmzwei.eu/` (branche `main`) |
| **Type de tests** | Recette fonctionnelle manuelle + tests de sécurité + non‑régression |

---

## Table des matières

1. [Objectifs et périmètre](#1-objectifs-et-périmètre)
2. [Stratégie de test](#2-stratégie-de-test)
3. [Environnement et prérequis](#3-environnement-et-prérequis)
4. [Jeux de données et comptes de test](#4-jeux-de-données-et-comptes-de-test)
5. [Campagne de smoke test (30 min)](#5-campagne-de-smoke-test-30-min)
6. [Cas de test détaillés](#6-cas-de-test-détaillés)
   - [AUT — Authentification](#61-aut--authentification)
   - [ACL — Droits et rôles](#62-acl--droits-et-rôles)
   - [TRV — Travaux](#63-trv--travaux)
   - [REF — Validation par le référent](#64-ref--validation-par-le-référent)
   - [CAN — Cantine](#65-can--cantine)
   - [UNI — Unités](#66-uni--unités)
   - [FAM — Familles](#67-fam--familles)
   - [IMP — Import CSV des familles](#68-imp--import-csv-des-familles)
   - [ORG — Organigramme et commissions](#69-org--organigramme-et-commissions)
   - [MAI — E‑mails et notifications](#610-mai--e-mails-et-notifications)
   - [CRO — Tâches planifiées](#611-cro--tâches-planifiées)
   - [API — API REST](#612-api--api-rest)
   - [BOF — Back‑office générique (CRUD)](#613-bof--back-office-générique-crud)
   - [TRA — Traductions](#614-tra--traductions)
   - [SEC — Sécurité](#615-sec--sécurité)
   - [IHM — Ergonomie, responsive, compatibilité](#616-ihm--ergonomie-responsive-compatibilité)
   - [PER — Performance et robustesse](#617-per--performance-et-robustesse)
7. [Checklist de non‑régression avant mise en production](#7-checklist-de-non-régression-avant-mise-en-production)
8. [Gestion des anomalies](#8-gestion-des-anomalies)
9. [Critères d'entrée et de sortie](#9-critères-dentrée-et-de-sortie)
10. [Pistes d'automatisation](#10-pistes-dautomatisation)
11. [Annexes](#11-annexes)

---

## 1. Objectifs et périmètre

### 1.1 Objectifs

- Vérifier que le **cycle de vie complet d'une session de travaux** fonctionne : création → publication → inscription famille → validation référent → contrôle admin → archivage.
- Garantir le **cloisonnement des droits** (`sys` / `fam` / invité) : aucune donnée ni action accessible hors de son rôle.
- Garantir la **justesse du décompte des unités** de participation, qui conditionne la restitution du chèque de caution des familles.
- Vérifier la **chaîne d'envoi des e‑mails** (file `sendmail` + cron) et les notifications aux référents.
- Contrôler la **non‑régression** des écrans refondus récemment (`Units_controller/valid`, agenda cantine, scan ACL, import familles).

### 1.2 Dans le périmètre

| Module | Contrôleur |
|---|---|
| Authentification, compte, page d'accueil | `Home` |
| Travaux / sessions | `Admwork_controller` |
| Cantine (garde du midi) | `Cantine_controller` |
| Validation des unités | `Units_controller` |
| Familles, import, statistiques | `Familys_controller` |
| Organigramme, commissions, candidatures | `Orgchart_controller`, `GroupesMembers_controller`, `Candidatures_controller` |
| ACL (rôles, contrôleurs, actions, utilisateurs) | `Acl_*_controller` |
| E‑mails, modèles, options, paramètres | `Sendmail_controller`, `Templates_controller`, `Options_controller`, `Parameters` |
| Fichiers publics, évènements | `Files_controller`, `Event_controller`, `Publics` |
| Traductions | `Translations_controller` |
| API REST | `Api` |
| Tâches planifiées | `Cron` |

### 1.3 Hors périmètre

- Le cœur du framework CodeIgniter 4 (`system/`) et les bibliothèques tierces (`assets/vendor/`).
- Le SSO **Delta Enfance** côté fournisseur : seule l'intégration côté application est testée (avec un compte Delta de test, ou en simulant les réponses de `RestClient`).
- L'infrastructure serveur (Apache, MySQL, SMTP) hors vérification de configuration.
- L'audit de sécurité approfondi (pentest) : seuls les contrôles listés en [§ 6.15](#615-sec--sécurité) sont couverts.

---

## 2. Stratégie de test

### 2.1 Niveaux de test

| Niveau | Contenu | Quand |
|---|---|---|
| **N0 — Smoke** | 12 cas critiques, 30 min ([§ 5](#5-campagne-de-smoke-test-30-min)) | Après chaque déploiement sur recette et en prod |
| **N1 — Fonctionnel par module** | Tous les cas P1 et P2 du [§ 6](#6-cas-de-test-détaillés) | Avant chaque merge `develop` → `main` |
| **N2 — Non‑régression complète** | Tous les cas, y compris P3 | Avant chaque release majeure / début de campagne annuelle |
| **N3 — Sécurité** | [§ 6.15](#615-sec--sécurité) | À chaque modification touchant l'ACL, l'auth ou un upload |
| **N4 — Performance** | [§ 6.17](#617-per--performance-et-robustesse) | Avant une campagne annuelle, ou si volumétrie > 300 familles |

### 2.2 Niveaux de priorité

| Priorité | Signification |
|---|---|
| **P1** | Bloquant — le module est inutilisable ou une donnée métier est fausse (unités, droits, envoi de mail). Test obligatoire à chaque livraison. |
| **P2** | Majeur — fonction dégradée, contournement possible. |
| **P3** | Mineur — confort, affichage, cas limite rare. |

### 2.3 Rôles de test

| Rôle | Type | Utilisé pour |
|---|---|---|
| **Administrateur** | `sys` | Configuration, CRUD, validation des unités |
| **Famille standard** | `fam` | Inscriptions, consultation de son historique |
| **Famille référente** | `fam` + `trombi` | Validation des présences sur ses sessions |
| **Invité** | `none` | Pages publiques et validation par lien e‑mail |

### 2.4 Convention d'identification des cas

`<MODULE>-<NN>` — par exemple `TRV-07`. Chaque anomalie doit référencer l'identifiant du cas qui l'a révélée.

---

## 3. Environnement et prérequis

### 3.1 Prérequis techniques

- [ ] Base de recette **restaurée à partir d'un dump anonymisé de production** (ou du jeu de données du [§ 4](#4-jeux-de-données-et-comptes-de-test)).
- [ ] Toutes les migrations SQL en attente exécutées (`database/sql/`, dont `validation_tokens`, `travaux.ref_mail_sent_at`, `mig_cantine.sql`).
- [ ] `.env` présent et renseigné (`travaux.apiKey`, `travaux.passwordSalt`, SMTP `email.*`).
- [ ] `.env` (database.default.*) pointant sur la base de **recette** et jamais sur la production.
- [ ] Droits d'écriture sur `writable/`, `public/files/`, `app/Language/`.
- [ ] `$config['civil_year']` cohérent avec les données de test (par défaut `2025-2026`).
- [ ] `$config['maintenance'] = false`, `$config['debug_app'] = 'none'` (sauf test dédié).
- [ ] SMTP de recette pointant sur une **boîte de capture** (MailHog, Mailtrap ou alias interne) — jamais sur les adresses réelles des familles.

> ⚠️ **Règle absolue** : ne jamais lancer `php public/index.php cron sendmail` sur une base contenant les adresses réelles des familles avec un SMTP de production. Vider la table `sendmail` ou remplacer les adresses avant toute campagne de test.

### 3.2 Navigateurs cibles

| Navigateur | Versions | Priorité |
|---|---|---|
| Chrome / Edge | 2 dernières | P1 |
| Firefox | 2 dernières | P1 |
| Safari macOS / iOS | 2 dernières | P2 |
| Chrome Android | dernière | P1 (les familles s'inscrivent majoritairement sur mobile) |

Résolutions : **360×740** (mobile), **768×1024** (tablette), **1440×900** (bureau).

---

## 4. Jeux de données et comptes de test

### 4.1 Comptes

| Réf. | Login | Type | Rôle | Usage |
|---|---|---|---|---|
| `U-ADM` | `admin` | `sys` | admin | Toutes les actions d'administration |
| `U-SYS2` | `gestion@test.local` | `sys` | rôle restreint | Vérifier le cloisonnement entre rôles `sys` |
| `U-FAM1` | `famille1@test.local` | `fam` | `role_famille` (2) | Famille école **M**, 2 enfants, 0 unité |
| `U-FAM2` | `famille2@test.local` | `fam` | `role_famille` | Famille école **L**, unités déjà validées |
| `U-REF` | `referent@test.local` | `fam` | `role_famille` | Référente de `T-FUTUR` et `T-PASSE` |
| `U-LEGACY` | `legacy@test.local` | `fam` | `role_famille` | Mot de passe encore en hash MD5/`crypt()` (test de migration bcrypt) |
| `U-DELTA` | compte Delta de test | `fam` | — | Connexion SSO |

### 4.2 Données métier

| Réf. | Objet | Caractéristiques |
|---|---|---|
| `T-BROUILLON` | `travaux` | `statut=0`, date future — ne doit **pas** apparaître côté famille |
| `T-FUTUR` | `travaux` | `type=TRA`, `statut=1`, date J+15, `nb_inscrits_max=4`, `nb_units=2`, `accespar=M`, référent = `U-REF` |
| `T-PLEIN` | `travaux` | `nb_inscrits_max=2` et déjà 2 inscrits — test de saturation |
| `T-PASSE` | `travaux` | date J‑5, non archivé — validation référent |
| `T-VIEUX` | `travaux` | date J‑45, `archived=0` — test d'archivage automatique |
| `T-URG` | `travaux` | `type=URG`, sans date — ne doit **jamais** être archivé |
| `T-LUT` | `travaux` | `accespar=L` — invisible pour `U-FAM1` (école M) |
| `T-AN-1` | `travaux` | `civil_year=2024-2025` — cloisonnement par année |
| `T-CAN` | `travaux` | `type=can`, généré par le module cantine |
| `G-COM` | `groupes` | Commission `type=com` avec 2 membres et 1 candidature en attente |
| `CSV-OK` | fichier | Export ABCM valide, 10 familles dont 2 nouvelles et 1 modifiée |
| `CSV-KO` | fichier | Même fichier avec colonnes manquantes / encodage erroné |

### 4.3 Requêtes SQL utiles

```sql
-- Comptes encore en hash legacy (doivent migrer au prochain login)
SELECT id, login FROM famille
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';

-- File d'envoi en attente
SELECT id, reference, email, statut FROM sendmail WHERE statut = 0;

-- Tokens de validation référent actifs
SELECT id_travaux, id_fam_ref, expires_at, used_at FROM validation_tokens
 ORDER BY created DESC;

-- Contrôle du décompte d'unités d'une famille
SELECT SUM(nb_unites_valides_effectif) FROM infos
 WHERE id_famille = :id AND civil_year = '2025-2026';
```

---

## 5. Campagne de smoke test (30 min)

À exécuter **après chaque déploiement**, recette comme production. Un seul échec bloque la mise en service.

| # | Cas | Attendu |
|---|---|---|
| 1 | `AUT-01` — Connexion admin | Redirection vers le tableau de bord, menu `sys` complet |
| 2 | `AUT-04` — Connexion famille | Menu `fam`, pas d'entrée d'administration |
| 3 | `TRV-01` — `Admwork_controller/register` | Liste des sessions à venir, sans erreur PHP |
| 4 | `TRV-05` — Inscription à une session | Ligne créée dans `infos`, compteur d'inscrits incrémenté |
| 5 | `TRV-08` — Désinscription | Ligne supprimée, place libérée |
| 6 | `CAN-03` — Agenda cantine semaine courante | Grille lundi‑vendredi affichée |
| 7 | `UNI-01` — `Units_controller/valid` | Cartes par session, filtres et compteurs opérationnels |
| 8 | `FAM-01` — `Familys_controller/histo` | Total d'unités correct pour `U-FAM1` |
| 9 | `ORG-01` — `Orgchart_controller/orga` | Organigramme affiché |
| 10 | `ACL-01` — URL admin en session famille | Redirection vers `Home/no_right` |
| 11 | `MAI-01` — Déclenchement d'une notification | Ligne `statut=0` créée dans `sendmail` |
| 12 | `AUT-08` — Déconnexion | Session détruite, retour au formulaire de connexion |

---

## 6. Cas de test détaillés

> Format : **ID · Priorité · Rôle** — Objectif / Étapes / Résultat attendu.

### 6.1 AUT — Authentification

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| AUT-01 | P1 | sys | Connexion admin locale | `Home/login`, saisir `U-ADM` + mot de passe valide | Session ouverte, `type=sys`, redirection `/Home`, menu admin visible |
| AUT-02 | P1 | — | Mot de passe erroné | Login valide + mot de passe faux | Message d'erreur générique, **pas** de distinction « login inconnu » vs « mot de passe faux », aucune session |
| AUT-03 | P1 | — | Login inexistant | Saisir un login inconnu | Même message et **temps de réponse comparable** à AUT-02 (protection contre l'énumération de comptes) |
| AUT-04 | P1 | fam | Connexion famille | `U-FAM1` | `type=fam`, menu famille sans entrées d'administration |
| AUT-05 | P1 | fam | Migration de mot de passe legacy | Se connecter avec `U-LEGACY` (hash MD5/`crypt`) | Connexion acceptée **et** `famille.password` réécrit en `$2y$...` en base |
| AUT-06 | P2 | fam | Connexion SSO Delta | Choisir `type_cnx = DELTA`, identifiants Delta de test | Compte famille local créé ou synchronisé, mot de passe stocké en bcrypt |
| AUT-07 | P2 | — | Forçage `NORM` pour `admin` | Se connecter avec le login `admin` en sélectionnant `DELTA` | L'application force `type_cnx=NORM` et authentifie en local |
| AUT-08 | P1 | tous | Déconnexion | `Home/logout` | Session détruite, cache ACL invalidé, accès à une page protégée redirigé vers le login |
| AUT-09 | P2 | — | Captcha activé | `$config['captcha'] = true`, soumettre sans résoudre le captcha | Connexion refusée, message d'erreur captcha affiché |
| AUT-10 | P2 | — | Champs obligatoires | Soumettre le formulaire vide | Messages de validation en français, pas d'erreur PHP |
| AUT-11 | P2 | tous | Mode maintenance | `$config['maintenance'] = true`, visiter le site | Page `Home/maintenance` pour tous ; l'admin conserve un accès (à confirmer avec le métier) |
| AUT-12 | P2 | fam | Changement de mot de passe | `Home/myaccount` → nouveau mot de passe | Reconnexion possible avec le nouveau seulement ; l'ancien est refusé |
| AUT-13 | P3 | fam | Expiration de session | Rester inactif au‑delà de la durée de session, puis cliquer | Redirection vers le login sans erreur PHP |
| AUT-14 | P2 | — | Page publique `Home/about` | Accès sans être connecté | Page affichée (elle figure dans `guestPages`) |

### 6.2 ACL — Droits et rôles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| ACL-01 | P1 | fam | Accès direct à une URL admin | En session `fam`, appeler `Familys_controller/list` | Redirection `Home/no_right`, aucune donnée exposée |
| ACL-02 | P1 | — | Accès sans session | Appeler `Units_controller/valid` déconnecté | Redirection vers le login, pas de fuite de contenu |
| ACL-03 | P1 | — | Pages invitées (`guestPages`) | Appeler `home/login`, `home/no_right`, `home/about`, `admwork_controller/validate_by_token/<token>` | Accessibles sans session ; **toute autre** route exige une session |
| ACL-04 | P1 | sys | Matrice des droits | `Acl_roles_controller/set_rules/<id>`, retirer un droit, sauvegarder | Le droit disparaît immédiatement pour le rôle concerné (après reconnexion si le cache session persiste — comportement à documenter) |
| ACL-05 | P1 | sys | Invalidation du cache | Modifier les droits d'un rôle, se déconnecter/reconnecter | Les nouveaux droits s'appliquent sans intervention en base |
| ACL-06 | P2 | sys | Scan des contrôleurs | `Acl_controllers_controller/scan` | Les contrôleurs/actions présents dans le code mais absents de l'ACL sont listés ; les alertes (actions orphelines, contrôleurs supprimés) sont cohérentes |
| ACL-07 | P2 | sys | Application du scan | Sélectionner quelques éléments détectés puis appliquer | Insertion en `acl_controllers` / `acl_actions` conforme à la sélection, rien d'autre modifié |
| ACL-08 | P2 | sys | Ajout d'action en lot | `Acl_controllers_controller/bulk_add_action` | Action ajoutée à tous les contrôleurs choisis, sans doublon |
| ACL-09 | P1 | sys | Secure by default | Ajouter un contrôleur non déclaré en ACL et l'appeler | Accès refusé par défaut (`DontCheck = FALSE`) |
| ACL-10 | P2 | sys | Comptes admin | `Acl_users_controller` : créer, modifier, supprimer un compte `sys` | CRUD fonctionnel, mot de passe stocké en bcrypt, impossible de supprimer le dernier admin (ou anomalie à ouvrir si possible) |
| ACL-11 | P1 | fam | Cloisonnement inter‑familles (IDOR) | En session `U-FAM1`, appeler `Familys_controller/histo` puis forcer un `id` d'une autre famille dans l'URL | Aucune donnée d'une autre famille accessible |

### 6.3 TRV — Travaux

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| TRV-01 | P1 | fam | Liste des sessions | `Admwork_controller/register` | Seules les sessions `statut=1`, non archivées, de la `civil_year` courante et compatibles avec l'école de la famille apparaissent |
| TRV-02 | P1 | fam | Brouillon masqué | Chercher `T-BROUILLON` dans la liste | Absent de la vue famille ; visible uniquement en `Admwork_controller/list` (admin) |
| TRV-03 | P1 | fam | Filtrage par école | En `U-FAM1` (école M), chercher `T-LUT` (`accespar=L`) | Session non proposée ; les sessions `accespar=B` restent visibles |
| TRV-04 | P2 | fam | Cloisonnement par année | Chercher `T-AN-1` | Absent de la liste courante |
| TRV-05 | P1 | fam | Inscription | `register_one/<T-FUTUR>`, `type_participant = Mr` | `infos` créé avec `nb_participants=1`, unités prévues = `nb_units`, redirection avec confirmation |
| TRV-06 | P1 | fam | Inscription couple | Même session, `type_participant = Both` | `nb_participants = 2` (règle métier forcée côté serveur, indépendante du POST) |
| TRV-07 | P1 | fam | Session complète | S'inscrire sur `T-PLEIN` | Message `TOO_MANY_PEOPLE`, **aucune** ligne créée |
| TRV-08 | P1 | fam | Désinscription | Se désinscrire de `T-FUTUR` | Ligne `infos` supprimée, place recomptée, session à nouveau proposée |
| TRV-09 | P1 | fam | Double inscription | S'inscrire deux fois à la même session | Refus (`ALREADY_REGISTERED`), pas de doublon en base |
| TRV-10 | P2 | fam | Session passée | Tenter de s'inscrire à `T-PASSE` | Refus (`PAST_DATE`) |
| TRV-11 | P2 | fam | Concurrence sur la dernière place | Deux navigateurs, deux familles, dernière place de `T-FUTUR`, valider quasi simultanément | Une seule inscription acceptée ; l'autre reçoit `TOO_MANY_PEOPLE` (à défaut, ouvrir une anomalie sur le contrôle de capacité) |
| TRV-12 | P1 | sys | CRUD session | `Admwork_controller/add`, `edit`, `delete` | Création, modification et suppression conformes ; validations de champs actives |
| TRV-13 | P2 | sys | Inscription pour le compte d'une famille | `managed_one/<id>` | Inscription créée au nom de la famille choisie, **sans** notification « inscription famille » au référent |
| TRV-14 | P2 | sys | Génération PDF | `MakePdf/<id_work>` | PDF de la feuille de session généré, lisible, avec les inscrits et les horaires |
| TRV-15 | P2 | sys | Archivage automatique | Positionner `T-VIEUX` à J‑45, supprimer `writable/cache/last_archive_run.txt`, visiter `register` | `T-VIEUX` passe à `archived=1` ; `T-URG` reste actif ; le fichier de throttle est recréé |
| TRV-16 | P3 | sys | Throttle d'archivage | Rappeler `register` dans la foulée | Pas de nouveau balayage d'archivage le même jour |
| TRV-17 | P2 | fam | Filtres de la liste | Utiliser recherche, type, date sur `register` | Résultats cohérents, compteurs mis à jour, pas d'erreur si aucun résultat |
| TRV-18 | P2 | sys | Statistiques participants | `Admwork_controller/worker` | Totaux cohérents avec `infos` (contrôle SQL croisé) |
| TRV-19 | P3 | fam | Session sans date (`URG`) | Ouvrir `T-URG` | Affichage sans date, inscription possible, pas d'erreur de formatage |

### 6.4 REF — Validation par le référent

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| REF-01 | P1 | fam réf. | Mes sessions | `Admwork_controller/my_sessions` en `U-REF` | Seules les sessions dont l'utilisateur est référent sont listées |
| REF-02 | P1 | fam réf. | Validation connectée | `validate_one/<T-PASSE>`, cocher les présents, valider | `infos.nb_unites_valides_effectif` = `nb_units × nb_participants` pour les présents, `0` pour les absents |
| REF-03 | P1 | fam | Non‑référent | En `U-FAM1`, appeler `validate_one/<T-PASSE>` | Accès refusé, aucune donnée de validation exposée |
| REF-04 | P1 | invité | Validation par lien e‑mail | Ouvrir `validate_by_token/<token valide>` sans session | Écran de validation accessible, limité à la session concernée |
| REF-05 | P1 | invité | Token invalide | Token inexistant ou altéré d'un caractère | Page d'erreur `Admwork_controller_token_error`, aucune donnée affichée |
| REF-06 | P1 | invité | Token expiré | Forcer `expires_at` dans le passé | Refus explicite |
| REF-07 | P1 | invité | Token déjà utilisé | Rejouer un token dont `used_at` est renseigné | Refus (ou lecture seule) ; **aucune** double validation d'unités |
| REF-08 | P2 | fam réf. | Ajout d'un participant | Depuis l'écran référent, ajouter une famille présente non inscrite | `infos` créé, capacité maximale respectée |
| REF-09 | P2 | fam réf. | Retrait d'un participant | Retirer une famille inscrite mais absente | Ligne supprimée ou unités mises à 0, cohérent avec la règle métier |
| REF-10 | P1 | invité | Cloisonnement du token | Depuis un token de la session A, tenter d'agir sur la session B (id forcé dans le POST) | Action refusée |
| REF-11 | P2 | fam réf. | Idempotence | Valider deux fois de suite la même session | Les unités ne sont pas doublées |

### 6.5 CAN — Cantine

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| CAN-01 | P1 | sys | Paramétrage | `Cantine_controller/config` : jours actifs, `nb_slots`, école, `nb_units`, puis `save_config` | Configuration enregistrée et relue à l'identique |
| CAN-02 | P1 | sys | Génération des créneaux | `generate` sur une période donnée | Une ligne `travaux` `type=can` par jour actif ; pas de doublon si on relance sur la même période |
| CAN-03 | P1 | fam | Agenda hebdomadaire | `register/0`, puis `register/1` et `register/-1` | Grille lundi‑vendredi, navigation semaine suivante / précédente cohérente, dates en français |
| CAN-04 | P1 | fam | Inscription à un créneau | `register_one/<T-CAN>` | Inscription enregistrée, retour sur la semaine du créneau (bon `week_offset`) |
| CAN-05 | P1 | fam | Créneau complet | S'inscrire sur un créneau saturé | Message `SESSION_FULL` en flash, aucune inscription |
| CAN-06 | P1 | fam | Créneau passé | S'inscrire sur un jour antérieur à aujourd'hui | Refus `PAST_DATE` |
| CAN-07 | P1 | fam | Désinscription | `unregister_one/<T-CAN>` avant validation | Inscription supprimée |
| CAN-08 | P1 | fam | Désinscription après validation | Positionner `nb_unites_valides_effectif > 0` puis se désinscrire | Refus `ALREADY_VALIDATED`, message affiché, ligne conservée |
| CAN-09 | P2 | sys | Accès admin à l'agenda | `register` en `sys` | Vue accessible en lecture ; les actions d'inscription réservées aux familles sont neutralisées |
| CAN-10 | P2 | fam | Filtre par école | Famille école L sur une config école M | Créneaux non proposés |
| CAN-11 | P3 | fam | Fin d'année scolaire | Naviguer au‑delà de la fin de l'année civile configurée | Pas de créneau au‑delà, pas d'erreur de date |

### 6.6 UNI — Unités

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| UNI-01 | P1 | sys | Écran de validation | `Units_controller/valid` | Une **carte par session** (y compris pour les sessions de type « Action » — régression corrigée), en‑tête avec date, titre, type, référent, unités, inscrits |
| UNI-02 | P1 | sys | Validation en lot | Cocher plusieurs lignes → `valids` → confirmer | `nb_unites_valides_effectif` mis à jour uniquement pour les lignes cochées |
| UNI-03 | P1 | sys | Cases masquées décochées | Cocher une ligne, appliquer un filtre qui la masque, valider | La ligne masquée **n'est pas** soumise |
| UNI-04 | P2 | sys | Filtres rapides | Recherche libre, filtre date, filtre famille, filtre type, réinitialisation | Filtrage client immédiat, compteurs (sessions visibles / unités totales / unités sélectionnées) recalculés |
| UNI-05 | P2 | sys | Persistance des filtres | Filtrer, aller sur `valids`, revenir sur `valid` | Filtres restaurés depuis `localStorage` (`uv_filters_v1`) |
| UNI-06 | P2 | sys | Tout cocher tri‑state | Cocher partiellement une carte | La case « tout cocher » passe en état indéterminé ; elle ignore les lignes masquées |
| UNI-07 | P1 | sys | Barre d'action sticky | Aucune ligne cochée | Bouton « Valider » désactivé ; il s'active dès la première case cochée |
| UNI-08 | P2 | sys | Lien « Ouvrir » | Cliquer sur le lien d'une carte | Ouverture de `Admwork_controller/register_one/<id>` de la bonne session |
| UNI-09 | P1 | sys | Unités complémentaires | `Units_controller/list` : ajouter une unité hors session avec commentaire | Ligne `unites` créée, prise en compte dans le total de la famille |
| UNI-10 | P1 | sys/fam | Total consolidé | Comparer le total affiché en `Familys_controller/histo` à la somme SQL `infos` + `unites` | Écart nul |
| UNI-11 | P2 | sys | Seuil d'unités attendues | Famille à moins de `unit_todo` (20) unités puis au‑dessus | Indicateur visuel cohérent de part et d'autre du seuil |
| UNI-12 | P3 | sys | Liste vide | Filtrer sans résultat | Message explicite, pas de tableau vide brut ni d'erreur JS |

### 6.7 FAM — Familles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| FAM-01 | P1 | fam | Mon historique | `Familys_controller/histo` | Unités validées, unités restantes, sessions à venir de **la famille connectée uniquement** |
| FAM-02 | P1 | sys | Historique d'une famille | `histo` en `sys` puis sélection d'une famille | Données de la famille choisie, changement de famille possible |
| FAM-03 | P1 | sys | CRUD famille | `list` / `add` / `edit` / `delete` | Validations actives (email, champs requis), suppression avec confirmation |
| FAM-04 | P2 | sys | E‑mails complémentaires | Ajouter deux e‑mails secondaires à une famille | Lignes `emails` liées à `id_fam`, restituées à l'édition |
| FAM-05 | P2 | sys | Compétences | `skills` : filtrer par compétence | Seules les familles portant la compétence apparaissent |
| FAM-06 | P1 | sys | Statistiques | `stats` | Totaux par famille cohérents avec le contrôle SQL |
| FAM-07 | P2 | sys | Export des statistiques | `stats_export` | Fichier téléchargé, séparateur et encodage corrects, accents non altérés à l'ouverture dans Excel/LibreOffice |
| FAM-08 | P2 | sys | Mise à jour en masse | `MassUpdate` sur une sélection | Seules les familles sélectionnées sont modifiées ; contrôle SQL avant/après |
| FAM-09 | P2 | sys | Chèques de caution | `check/<id_fam>` | Saisie et consultation du statut du chèque |
| FAM-10 | P2 | sys | Détail des unités | `units/<id_fam>` | Détail ligne à ligne, total identique à `histo` |
| FAM-11 | P2 | fam | Préférences d'alerte | `Home/myaccount` : activer/désactiver un type d'alerte | Enregistré dans `alert_pref`, respecté par les notifications ([§ 6.10](#610-mai--e-mails-et-notifications)) |
| FAM-12 | P3 | sys | Volumétrie | Liste avec > 300 familles | Pagination fonctionnelle, temps de réponse < 3 s |

### 6.8 IMP — Import CSV des familles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| IMP-01 | P1 | sys | Import valide — prévisualisation | `Familys_controller/import` avec `CSV-OK` | Écran de différentiel : créations, modifications, membres ajoutés/retirés, **aucune écriture** en base à ce stade |
| IMP-02 | P1 | sys | Application de l'import | `import_apply` après prévisualisation | Base conforme au différentiel affiché, ni plus ni moins |
| IMP-03 | P1 | sys | Fichier invalide | Importer `CSV-KO` | Erreur explicite, aucune modification en base |
| IMP-04 | P1 | sys | Idempotence | Réimporter `CSV-OK` immédiatement après IMP-02 | Différentiel vide, aucun doublon de famille ni de membre |
| IMP-05 | P2 | sys | Normalisation | Fichier avec e‑mails en majuscules, espaces parasites, écoles en libellé long | E‑mails normalisés en minuscules, valeurs nettoyées, école mappée sur `M`/`L`/`B` |
| IMP-06 | P2 | sys | Historique | `import_history` | Historique des imports avec date, opérateur et volumétrie |
| IMP-07 | P2 | sys | Encodage | Fichier en ISO‑8859‑1 et fichier en UTF‑8 avec BOM | Accents corrects en base dans les deux cas (ou refus explicite si un encodage n'est pas supporté) |
| IMP-08 | P2 | sys | Fichier non CSV | Téléverser un `.exe` renommé en `.csv`, puis un fichier de 20 Mo | Rejet propre, pas de fichier conservé sur le serveur |
| IMP-09 | P1 | sys | Préservation des données locales | Importer un CSV pour une famille ayant des unités et des inscriptions | Unités, inscriptions et mot de passe locaux **non écrasés** |

### 6.9 ORG — Organigramme et commissions

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| ORG-01 | P1 | tous | Vue publique | `Orgchart_controller/orga` | Commissions et membres affichés, photos ou image de remplacement si absente |
| ORG-02 | P2 | tous | Organisation | `organisation` | Vue structurelle correcte, classifications (RT, ME, BU, PE, VP) respectées |
| ORG-03 | P2 | sys | CRUD commission | `list` / `add` / `edit` / `delete` | CRUD conforme, aucun membre orphelin après suppression |
| ORG-04 | P2 | sys | Mise en avant | `featured/<id>` | La commission remonte en tête, une seule mise en avant à la fois (ou comportement documenté) |
| ORG-05 | P2 | sys | Membres | `GroupesMembers_controller` : créer un membre relié à une famille | Chaîne `trombi → groupes_member → famille` cohérente (test croisé avec REF-01) |
| ORG-06 | P2 | fam | Candidature | Déposer une candidature à `G-COM` | Ligne `candidatures` créée, notification au responsable ([§ 6.10](#610-mai--e-mails-et-notifications)) |
| ORG-07 | P2 | sys | Traitement d'une candidature | `Candidatures_controller` : accepter puis refuser | Statut mis à jour, membre ajouté à la commission si accepté |
| ORG-08 | P3 | sys | Chaîne référent incohérente | Mettre un `groupes_member.id_fam` non numérique | Aucune ligne remontée, pas d'erreur SQL (conversion implicite documentée) |
| ORG-09 | P3 | tous | Détail d'un membre | `view_one/<id>` | Fiche complète, données personnelles non exposées aux invités |

### 6.10 MAI — E‑mails et notifications

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| MAI-01 | P1 | fam | Notification d'inscription | S'inscrire à `T-FUTUR` en tant que famille | Ligne `sendmail` `statut=0` adressée au référent, objet et corps renseignés |
| MAI-02 | P1 | sys | Pas de notification en saisie admin | Inscrire une famille via `managed_one` | **Aucune** notification d'inscription famille générée |
| MAI-03 | P2 | fam | Notification de désinscription | Se désinscrire | Notification correspondante en file |
| MAI-04 | P2 | fam | Notification cantine | S'inscrire puis se désinscrire d'un créneau cantine | Notifications cohérentes avec le type de session |
| MAI-05 | P2 | fam | Notification de candidature | Déposer une candidature | Notification au responsable de commission |
| MAI-06 | P1 | — | Destinataire sans e‑mail | Référent sans adresse valide | Erreur tracée (fallback), pas d'exception, le reste du traitement se poursuit |
| MAI-07 | P2 | fam | Respect des préférences | Désactiver un type d'alerte puis déclencher l'évènement | Aucun mail de ce type n'est mis en file |
| MAI-08 | P2 | sys | File d'envoi | `Sendmail_controller/list` | Liste, filtres et statuts (0 en attente / 1 envoyé / 2 erreur) lisibles |
| MAI-09 | P2 | sys | Modèles de message | `Templates_controller` : modifier un modèle, déclencher l'évènement associé | Le contenu du mail reflète le modèle, variables substituées |
| MAI-10 | P2 | — | Rendu HTML | Ouvrir un mail capturé | HTML correct sur client bureau et webmail, liens absolus et cliquables |
| MAI-11 | P1 | — | Sécurité des liens de validation | Inspecter le lien référent envoyé | URL en HTTPS, token de 64 caractères hexadécimaux, non devinable |

### 6.11 CRO — Tâches planifiées

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| CRO-01 | P1 | Envoi de la file | `php public/index.php cron sendmail 10` avec 3 mails en attente | 3 mails partis, `statut=1`, log en `sendmail_statut` |
| CRO-02 | P1 | Gestion d'erreur SMTP | Config SMTP volontairement fausse | `statut=2`, message d'erreur enregistré, pas d'interruption du lot |
| CRO-03 | P1 | Taille du lot | `cron sendmail 2` avec 5 mails en attente | Exactement 2 traités |
| CRO-04 | P1 | Verrou d'exécution | Lancer deux exécutions simultanées | La seconde s'arrête immédiatement (`process.loc`) |
| CRO-05 | P2 | Verrou résiduel | Laisser un `process.loc` orphelin | Comportement documenté (déblocage manuel ou expiration) — anomalie si blocage définitif |
| CRO-06 | P1 | Mails de validation référent | `php public/index.php cron send_ref_validation_mails 7` avec une session à J+5 | Token créé (expiration J+30), mail poussé en file, `travaux.ref_mail_sent_at` renseigné |
| CRO-07 | P1 | Non‑duplication | Relancer immédiatement CRO-06 | Aucun nouveau token ni mail pour la même session |
| CRO-08 | P2 | Référent introuvable | Session sans référent ou chaîne `trombi` cassée | Session ignorée sans erreur, trace en log |
| CRO-09 | P2 | Alertes nouvelles sessions | `cron send_new_session_alerts` | Familles concernées notifiées, filtrage par école et préférences respecté |
| CRO-10 | P2 | Exécution web interdite | Appeler `/cron/sendmail` via le navigateur | Accès refusé (seul `cron/send_ref_validation_mails` est déclaré en `guestPages`, et à usage CLI) |
| CRO-11 | P3 | Volumétrie | 500 mails en file, lots successifs | Pas de fuite mémoire ni de timeout ; durée mesurée et notée |

### 6.12 API — API REST

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| API-01 | P1 | Connexion | `POST /api/login` avec identifiants + `api-key` valides | `200` + JWT, `expireAt`, `type`, `role_id` |
| API-02 | P1 | Clé d'API invalide | Même appel avec une mauvaise `api-key` | `403`, pas de JWT |
| API-03 | P1 | Identifiants invalides | Mot de passe erroné | `401`/`403` selon le contrat, message générique |
| API-04 | P1 | Appel sans jeton | `GET /api/mails` sans `Authorization` | `401`/`403`, aucune donnée |
| API-05 | P1 | Jeton altéré | Modifier un caractère de la signature JWT | Refus |
| API-06 | P1 | Jeton expiré | Utiliser un JWT dont `expireAt` est dépassé | Refus |
| API-07 | P1 | Création | `POST /api/mails` avec `reference`, `email`, `object`, `message` | `201` + `{ "id": n }`, ligne `statut=0` en base |
| API-08 | P1 | Lecture | `GET /api/mails` puis `GET /api/mails/<id>` | `200` + JSON conforme |
| API-09 | P1 | Mise à jour | `PUT /api/mails/<id>` | `202`, données modifiées |
| API-10 | P1 | Suppression | `DELETE /api/mails/<id>` | `200`, ligne supprimée |
| API-11 | P1 | Validation | `POST` sans `email` ou avec un e‑mail invalide | `400`, aucune création |
| API-12 | P2 | Identifiant inexistant | `GET /api/mails/999999` | `400`/`404` selon le contrat, pas de trace PHP |
| API-13 | P2 | Verbe non supporté | `PATCH /api/mails/<id>` | Refus propre |
| API-14 | P2 | Pré‑vol CORS | `OPTIONS /api/mails` | En‑têtes attendus, `200`/`204` |
| API-15 | P1 | Injection | `id = 1 OR 1=1` et charge SQL dans les champs | Aucune fuite, aucune erreur SQL renvoyée au client |
| API-16 | P2 | Déconnexion | `POST /api/logout` puis réutiliser le JWT | Comportement documenté (JWT stateless : préciser si le jeton reste valide jusqu'à expiration) |

### 6.13 BOF — Back‑office générique (CRUD)

Ces cas s'appliquent à **chaque** contrôleur reposant sur `CrudController` (`Familys_controller`, `Admwork_controller`, `Options_controller`, `Templates_controller`, `Files_controller`, `Event_controller`, `Sendmail_controller`, `Parameters`, `Acl_*`).

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| BOF-01 | P1 | Liste | Ouvrir `list` sur chaque contrôleur | Colonnes conformes au schéma JSON (`list: true`), tri et pagination opérationnels |
| BOF-02 | P1 | Recherche | Rechercher sur un champ `search: true` | Résultats filtrés ; caractère `%`, apostrophe et accents sans erreur |
| BOF-03 | P2 | Réinitialisation des filtres | `clear_filters` | Retour à la liste complète |
| BOF-04 | P1 | Création | `add` avec champs obligatoires vides puis complets | Messages de validation français, puis création effective |
| BOF-05 | P1 | Édition | `edit/<id>` | Formulaire prérempli, y compris `select_database`, `typeahead`, `memo`, `checkbox`, tables liées |
| BOF-06 | P1 | Suppression | `delete/<id>` | Confirmation demandée, suppression effective |
| BOF-07 | P1 | Actions en lot | `bulk` avec `delete` sur plusieurs éléments | Seuls les éléments sélectionnés sont supprimés ; action inconnue refusée |
| BOF-08 | P1 | Droit sur l'action en lot | Rôle sans droit `delete` | L'action de suppression en lot n'est ni affichée ni exécutable |
| BOF-09 | P2 | Export CSV | `export_csv` | Colonnes cohérentes avec la liste, encodage et séparateur corrects |
| BOF-10 | P2 | Champ fichier / image | Téléverser une image dans `Files_controller` puis dans une fiche membre | Fichier stocké sous `public/files/`, restitué en liste et en vue |
| BOF-11 | P2 | Éditeur riche | Champ `memo`/`html` via CKEditor | Contenu sauvegardé et restitué ; balises `<script>` neutralisées à l'affichage |
| BOF-12 | P2 | Types d'éléments | Champs `date`, `time`, `month`, `select`, `checkboxdb`, `typeahead` | Saisie, sauvegarde et relecture correctes ; format de date `H` en 24 h |
| BOF-13 | P2 | Options et paramètres | `Options_controller/list`, `Parameters/list` | Modification d'une couleur / d'un libellé de type répercutée dans les vues métier |
| BOF-14 | P3 | Identifiant inexistant | `edit/999999`, `view/999999` | Message propre, pas de trace PHP |
| BOF-15 | P2 | Vue publique de fichiers | `Publics/files` sans session | Seuls les fichiers marqués publics sont accessibles |

### 6.14 TRA — Traductions

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| TRA-01 | P2 | Liste des fichiers | `Translations_controller/list` | Fichiers de langue de l'idiome courant listés |
| TRA-02 | P2 | Édition et sauvegarde | Modifier une clé puis `save` | Fichier `*_lang.php` réécrit, valeur visible dans l'interface après rechargement |
| TRA-03 | P1 | Sauvegarde préalable | Vérifier après TRA-02 | Copie de sauvegarde créée avant réécriture |
| TRA-04 | P2 | Caractères spéciaux | Saisir une valeur avec `'`, `"`, `\`, un accent et un retour à la ligne | Échappement correct ; le fichier PHP reste valide (contrôler `php -l`) |
| TRA-05 | P2 | Suppression de clé | `delete_key` | Clé supprimée, aucune autre ligne altérée |
| TRA-06 | P2 | Ajout de langue | `add_language` puis `switch_lang` | Nouveau répertoire créé à partir de la référence, bascule effective |
| TRA-07 | P1 | Traversée de répertoire | Appeler `edit/../../config/config.php` et `edit/french/../../../index.php` | Refus (`_is_safe_idiom` / `_is_safe_file`), **aucun** fichier hors `app/Language/` lu ou écrit |
| TRA-08 | P1 | Droits | Accéder au module en session `fam` | Accès refusé |
| TRA-09 | P3 | Clé manquante | Afficher une page dont une clé a été supprimée | Dégradation propre (clé brute affichée), pas d'erreur fatale |

### 6.15 SEC — Sécurité

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| SEC-01 | P1 | Injection SQL | `'`, `" OR 1=1--`, `%` dans les champs de recherche, les filtres et les identifiants d'URL | Aucun message d'erreur SQL, aucun résultat anormal |
| SEC-02 | P1 | XSS stocké | Enregistrer `<script>alert(1)</script>` dans un titre de session, un commentaire d'unité, un nom de membre | Affiché en texte, jamais exécuté (vérifier la liste, la vue, le PDF **et** l'e‑mail) |
| SEC-03 | P1 | XSS réfléchi | Injecter la charge dans un paramètre de recherche | Échappé à l'affichage |
| SEC-04 | P1 | CSRF | Rejouer un POST de suppression depuis une page externe | Requête rejetée si la protection CSRF de CI est activée — sinon **ouvrir une anomalie P1** |
| SEC-05 | P1 | IDOR | Manipuler `id_famille`, `id_travaux`, `id_info` dans les URL et les POST en session `fam` | Aucune action sur les données d'une autre famille |
| SEC-06 | P1 | Élévation de privilège | En session `fam`, poster vers `Acl_roles_controller/set_rules` et `Familys_controller/edit` d'une autre famille | Refus |
| SEC-07 | P1 | Fixation de session | Comparer l'identifiant de session avant/après connexion | Identifiant régénéré à la connexion |
| SEC-08 | P1 | Cookies | Inspecter le cookie de session en HTTPS | `HttpOnly` et `Secure` positionnés ; `SameSite` défini |
| SEC-09 | P1 | Accès direct aux fichiers | `/app/Config/Travaux.php`, `/.env`, `/writable/logs/`, `/php_errors.log` | `403`/`404` — **aucun** contenu servi |
| SEC-10 | P1 | Fuite d'erreurs | Provoquer une erreur SQL en production | Page d'erreur générique, aucune trace ni requête affichée (`debug_app = none`) |
| SEC-11 | P1 | Upload | Téléverser `.php`, `.phtml`, `.svg` avec script, et un fichier de très grande taille | Rejet ou stockage non exécutable ; vérifier qu'aucun script n'est atteignable via son URL |
| SEC-12 | P1 | Force brute | 20 tentatives de connexion échouées | Limitation ou temporisation en place — sinon ouvrir une anomalie et documenter le risque |
| SEC-13 | P1 | Robustesse du token référent | Tester des tokens tronqués, en majuscules, avec des caractères ajoutés | Refus systématique ; aucune information sur l'existence du token |
| SEC-14 | P2 | En‑têtes HTTP | Inspecter les réponses | `X-Content-Type-Options`, `X-Frame-Options`/CSP, HSTS en production |
| SEC-15 | P2 | HTTPS | Appeler le site en `http://` | Redirection vers HTTPS |
| SEC-16 | P1 | Secrets versionnés | `git grep` sur les mots de passe, clés d'API et identifiants de base | Aucun secret dans le dépôt ; `.env`, `secured.php` et `production/*` bien ignorés |
| SEC-17 | P2 | Mots de passe en base | Inspecter `acl_users.password` et `famille.password` après une campagne de connexions | 100 % en `$2y$` (bcrypt) |

### 6.16 IHM — Ergonomie, responsive, compatibilité

| ID | P | Objectif | Résultat attendu |
|---|---|---|---|
| IHM-01 | P1 | Parcours famille sur mobile 360 px | Inscription à une session réalisable de bout en bout sans scroll horizontal |
| IHM-02 | P1 | Agenda cantine sur mobile | Grille lisible, boutons d'inscription atteignables au pouce |
| IHM-03 | P1 | Écran `Units_controller/valid` sur portable 1366 px | Cartes lisibles, barre sticky visible, pas de chevauchement |
| IHM-04 | P2 | Menu latéral | Ouverture/fermeture correcte sur mobile et bureau, entrées filtrées par type d'utilisateur (`opt`) |
| IHM-05 | P2 | Compatibilité navigateurs | Aucun défaut bloquant sur les navigateurs du [§ 3.2](#32-navigateurs-cibles) |
| IHM-06 | P2 | Console JavaScript | Aucune erreur JS sur les écrans principaux |
| IHM-07 | P2 | Impression / PDF | La feuille de session imprimée est lisible et complète (`assets/css/pdf.css`) |
| IHM-08 | P2 | Langue | Aucune clé de traduction brute (`SOME_KEY`) visible dans l'interface |
| IHM-09 | P3 | Accessibilité de base | Contrastes suffisants, libellés de formulaire associés, navigation au clavier possible sur les formulaires d'inscription |
| IHM-10 | P3 | Images manquantes | Photo de membre absente → image de remplacement (`broken_image.png`), pas d'icône cassée |

### 6.17 PER — Performance et robustesse

| ID | P | Objectif | Résultat attendu |
|---|---|---|---|
| PER-01 | P2 | `Admwork_controller/register` avec 200 sessions | Chargement < 2 s |
| PER-02 | P2 | `Units_controller/valid` avec 100 sessions × 10 inscrits | Chargement < 3 s, filtrage client fluide |
| PER-03 | P2 | Liste des familles (> 300 lignes) | Pagination efficace, < 3 s |
| PER-04 | P2 | 30 inscriptions simultanées | Aucune erreur 500, capacité maximale jamais dépassée |
| PER-05 | P3 | Import CSV de 500 lignes | Traitement sans timeout, ou pagination/lot documenté |
| PER-06 | P2 | Journaux | Après une campagne complète, `writable/logs/` ne contient aucune erreur `ERROR` inattendue |

---

## 7. Checklist de non‑régression avant mise en production

À dérouler avant chaque merge `develop` → `main`, puis à revalider en production après déploiement.

**Préparation**

- [ ] `.env` (database.default.*) à jour sur le serveur
- [ ] `.env` à jour sur le serveur
- [ ] Migrations SQL en attente exécutées (voir `DOCUMENTATION.md` § 13.2)
- [ ] Droits d'écriture vérifiés sur `writable/`, `public/files/`
- [ ] `civil_year` correcte pour la campagne en cours
- [ ] `maintenance = false`, `debug_app = 'none'`
- [ ] Sauvegarde de la base de production effectuée

**Fonctionnel**

- [ ] Smoke test complet ([§ 5](#5-campagne-de-smoke-test-30-min)) — 12/12
- [ ] Connexion admin **et** connexion famille
- [ ] Inscription puis désinscription à une session réelle de test, ensuite nettoyée
- [ ] `Units_controller/valid` affiche les sessions en attente
- [ ] Un mail de test traverse la file (`sendmail` → cron → boîte de réception)
- [ ] `php public/index.php cron sendmail` et `cron send_ref_validation_mails` s'exécutent sans erreur en CLI
- [ ] Crontabs actifs sur le serveur (`*/10 * * * *` et `0 6 * * *`)
- [ ] Aucune erreur dans `writable/logs/` après 30 min d'exploitation

**Sécurité**

- [ ] `/.env` et `/app/` inaccessibles via le navigateur (seul `public/` est exposé)
- [ ] Aucun secret ajouté au dépôt sur ce cycle (`git diff` sur les fichiers de configuration)

---

## 8. Gestion des anomalies

### 8.1 Sévérités

| Sévérité | Définition | Délai attendu |
|---|---|---|
| **S1 — Bloquant** | Le site est inaccessible, les données sont corrompues, une faille de droits expose des données personnelles, les unités sont fausses | Correction immédiate (hotfix depuis `main`) |
| **S2 — Majeur** | Une fonction principale est inutilisable sans contournement (inscription, validation, envoi de mails) | Correction avant la mise en production |
| **S3 — Mineur** | Fonction dégradée avec contournement, défaut d'affichage significatif | Planifiée sur la prochaine itération |
| **S4 — Cosmétique** | Libellé, alignement, confort | Backlog |

### 8.2 Modèle de fiche d'anomalie

```
ID          : ANO-<nnn>
Cas de test : <ID du cas, ex. TRV-07>
Sévérité    : S1 | S2 | S3 | S4
Environnement : recette | production — navigateur, version, résolution
Compte utilisé : U-ADM | U-FAM1 | ...
URL         :
Préconditions :
Étapes de reproduction :
  1.
  2.
Résultat obtenu :
Résultat attendu :
Fréquence   : systématique | intermittente (n/10)
Pièces jointes : capture, extrait de writable/logs/, requête SQL de contrôle
```

### 8.3 Circuit

1. Reproduire deux fois avant d'ouvrir la fiche.
2. Créer une **issue GitHub** sur `tmilefr/site_travaux` avec le libellé de sévérité.
3. Correction sur `feature-*` (depuis `develop`) ou `hotfix-*` (depuis `main`).
4. Re‑test du cas d'origine **plus** les cas connexes du même module.
5. Clôture après validation en recette, puis contrôle après déploiement en production.

---

## 9. Critères d'entrée et de sortie

### 9.1 Entrée en recette

- [ ] La branche est déployée sur `regio.dev-asso.fr` et l'application démarre.
- [ ] Les migrations SQL du lot sont passées.
- [ ] Le jeu de données du [§ 4](#4-jeux-de-données-et-comptes-de-test) est en place.
- [ ] Le smoke test passe à 12/12.

### 9.2 Sortie de recette (autorisation de mise en production)

- [ ] 100 % des cas **P1** exécutés et conformes.
- [ ] ≥ 90 % des cas **P2** exécutés, aucune anomalie **S1** ou **S2** ouverte.
- [ ] Les anomalies **S3/S4** restantes sont tracées et arbitrées avec le métier.
- [ ] La checklist du [§ 7](#7-checklist-de-non-régression-avant-mise-en-production) est complète.
- [ ] Le CHANGELOG et la documentation sont à jour pour les évolutions livrées.

---

## 10. Pistes d'automatisation

Le projet ne dispose aujourd'hui d'**aucune infrastructure de test**. Trois chantiers, par rapport effort/valeur décroissant :

### 10.1 Tests unitaires PHP (PHPUnit) — priorité haute

Cibler les briques sans dépendance HTTP, qui portent les règles métier les plus sensibles :

| Cible | Ce qui est testable en isolation |
|---|---|
| `PasswordAuthenticator` | Vérification bcrypt, migration `crypt()`/MD5, rehash, temps constant |
| `Inscriptions` | Tous les codes de retour (`SESSION_FULL`, `PAST_DATE`, `ALREADY_VALIDATED`…) et le calcul de capacité |
| `Familys_controller::_parse_csv_abcm` / `_build_diff` | Parsing, normalisation, différentiel, idempotence — extraire dans une classe dédiée pour les rendre testables |
| `Acl` | Résolution des droits, `guestPages`, cache par `role_id` |
| `Libpdf`, helpers de dates | Formats, `H` vs `h` |

```bash
composer require --dev phpunit/phpunit ^9
```

### 10.2 Smoke test E2E (Playwright) — priorité moyenne

Automatiser les 12 cas du [§ 5](#5-campagne-de-smoke-test-30-min) contre l'environnement de recette : connexion admin, connexion famille, inscription, désinscription, écran de validation, déconnexion. Environ 150 lignes de script, exécutées après chaque déploiement.

### 10.3 Intégration continue — priorité moyenne

Un workflow GitHub Actions sur `develop` et `main` :

1. `php -l` récursif sur `app/` + `php tools/check_classes.php` (détecte les erreurs de syntaxe avant déploiement) ;
2. PHPUnit ;
3. contrôle qu'aucun secret (`.env`) n'entre dans le dépôt.

---

## 11. Annexes

### 11.1 Fiche de suivi d'exécution

| Cas | Priorité | Testeur | Date | Statut | Anomalie | Commentaire |
|---|---|---|---|---|---|---|
| AUT-01 | P1 | | | ☐ OK ☐ KO ☐ N/A | | |
| … | | | | | | |

Statuts : **OK** (conforme) · **KO** (anomalie ouverte) · **N/A** (non applicable, à justifier) · **Bloqué** (préconditions indisponibles).

### 11.2 Synthèse de couverture

| Module | Cas | dont P1 |
|---|---|---|
| AUT — Authentification | 14 | 6 |
| ACL — Droits et rôles | 11 | 6 |
| TRV — Travaux | 19 | 9 |
| REF — Validation référent | 11 | 7 |
| CAN — Cantine | 11 | 7 |
| UNI — Unités | 12 | 6 |
| FAM — Familles | 12 | 4 |
| IMP — Import CSV | 9 | 4 |
| ORG — Organigramme | 9 | 1 |
| MAI — E‑mails | 11 | 4 |
| CRO — Cron | 11 | 5 |
| API — API REST | 16 | 10 |
| BOF — Back‑office | 15 | 6 |
| TRA — Traductions | 9 | 3 |
| SEC — Sécurité | 17 | 13 |
| IHM — Ergonomie | 10 | 3 |
| PER — Performance | 6 | 0 |
| **Total** | **203** | **94** |

### 11.3 Zones à risque identifiées à la lecture du code

Ces points méritent une attention particulière en recette ; ils sont issus des pièges documentés et de la structure du code.

| Zone | Risque | Cas associés |
|---|---|---|
| `Admwork_model::GetFiltered` | L'année `2025-2026` est codée en dur — les campagnes suivantes risquent de ne rien afficher | TRV-04 |
| Contrôle de capacité (`ADD_registration`) | Lecture puis écriture non atomiques : dépassement possible en cas d'inscriptions simultanées | TRV-11, PER-04 |
| `trombi.ref` / `groupes_member.id_fam` en VARCHAR | Une valeur non numérique casse silencieusement la chaîne référent → aucun mail envoyé | ORG-08, CRO-08 |
| Tokens de validation | Rejeu et expiration : risque de double comptage d'unités | REF-06, REF-07, REF-11 |
| `Translations_controller` | Écriture de fichiers PHP depuis l'interface : traversée de répertoire et fichier corrompu | TRA-04, TRA-07 |
| Verrou cron (`process.loc`) | Un verrou orphelin peut bloquer définitivement les envois | CRO-04, CRO-05 |
| Import CSV | Écrasement de données locales (unités, mots de passe) | IMP-09 |
| Format de date `H` vs `h` | Bug historique sur `updated` — à revérifier après toute modification des éléments de date | BOF-12 |

### 11.4 Références

- `DOCUMENTATION.md` — architecture, modèle de données, modules, API, cron
- `CHANGELOG.md` — refonte de `Units_controller/valid` (cas UNI-01 à UNI-08)
- `database/sql/` — scripts SQL à jouer avant recette
