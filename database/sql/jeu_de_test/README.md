# Jeu de test de recette — `site_travaux`

Jeu de données de référence à charger sur l'environnement de recette avant
une campagne de tests. Il met en place les comptes, les sessions, les
inscriptions et les cas limites décrits au § 5.3 du plan de recette et
utilisés par les cas de test du classeur `plan_tests_site_travaux.xlsx`.

> **À n'exécuter que sur un environnement de recette.** Ces scripts créent
> des comptes dont le mot de passe est public et des jetons de validation
> dont la valeur est fixe. Ne jamais les charger en production.

---

## Contenu

| Fichier | Rôle |
|---|---|
| `00_schema_complement.sql` | Crée les tables absentes des migrations du dépôt et met à niveau les tables existantes. Idempotent. |
| `charger_jeu_de_test.sh` | Script de chargement : demande le mot de passe des comptes, génère les hashes et charge le jeu. |
| `10_jeu_de_test.sql` | Charge le jeu de données. Rejouable : il purge ses propres lignes avant de recharger. |
| `90_purge_jeu_de_test.sql` | Retire le jeu de test et rien d'autre. |
| `csv/import_ok.csv` | Export ABCM valide pour les tests d'import (onglet 13 du classeur). |
| `csv/import_ko.csv` | Même fichier volontairement corrompu : lignes tronquées, code famille vide, e-mail manquant. |

---

## Chargement

### Sur une base vide (environnement monté de zéro)

```bash
DB=travaux_recette
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB CHARACTER SET utf8 COLLATE utf8_general_ci;"

cd database/sql
for f in Acl.sql Options.sql capacitys.sql groupes.sql Trombi.sql members.sql \
         emails.sql ci_sessions.sql mig_cantine.sql; do
  mysql -u root -p "$DB" < "$f"
done

# Puis le socle complémentaire, qui crée famille / travaux / infos / unites.
mysql -u root -p "$DB" < jeu_de_test/00_schema_complement.sql
./jeu_de_test/charger_jeu_de_test.sh "$DB"
```

### Sur une base restaurée depuis la production (anonymisée)

```bash
cd database/sql
mysql -u root -p travaux_recette < jeu_de_test/00_schema_complement.sql
./jeu_de_test/charger_jeu_de_test.sh travaux_recette
```

`00_schema_complement.sql` ne touche pas aux tables déjà conformes : il
n'ajoute que les colonnes manquantes.

---

## Avant de charger : deux réglages

**1. L'année civile.** En tête de `10_jeu_de_test.sql` :

```sql
SET @CY = '2025-2026';
```

Cette valeur doit être **strictement identique** à `civilYear`
dans `app/Config/Travaux.php` (ou `travaux.civilYear` du `.env`). Sinon aucune session de test
n'apparaîtra dans l'application.

**2. Le serveur SMTP.** Le jeu place trois messages en file d'attente. Avant
de lancer `php index.php cron sendmail`, vérifier que le SMTP de recette
pointe sur une boîte de capture (MailHog, Mailtrap, alias interne). Les
adresses du jeu utilisent le domaine inexistant `recette.local`, mais
c'est une ceinture, pas une bretelle.

---

## Comptes créés

Le mot de passe est **choisi au chargement** : `charger_jeu_de_test.sh` le
demande (ou le lit dans la variable d'environnement `MDP_RECETTE`), génère
les hashes et les injecte. Il n'est donc écrit dans aucun fichier du dépôt.
Tous les comptes partagent ce mot de passe.

| Réf. plan | Login | Profil | Particularité |
|---|---|---|---|
| U-ADM | `admin.recette@recette.local` | Administrateur | Tous droits (rôle 1) |
| U-SYS2 | `restreint.recette@recette.local` | Administrateur | Rôle volontairement limité (droits du rôle Famille) |
| U-FAM1 | `famille1@recette.local` | Famille | École Mulhouse, 2 enfants, aucune unité, abonnée aux alertes |
| U-FAM2 | `famille2@recette.local` | Famille | École Lutterbach, unités déjà validées, **non abonnée** aux alertes |
| U-REF | `referent@recette.local` | Famille référente | Référente des sessions de test |
| U-LEGACY | `legacy@recette.local` | Famille | Mot de passe stocké en **MD5** : doit migrer en bcrypt au premier login |
| U-FAM3 | `famille3@recette.local` | Famille | École « les deux », utilisée pour la cantine |
| U-FAM4 | `famille.import@recette.local` | Famille | Cible de l'import CSV |

Pour changer le mot de passe, relancer simplement le script de chargement :
le jeu est rejouable et les comptes sont recréés.

Si vous préférez charger `10_jeu_de_test.sql` à la main, il faut définir
les deux variables **avant** le fichier, sinon le script s'arrête avec un
message explicite :

```bash
MDP='VotreMotDePasse'
php -r "printf(\"SET @PWD_BCRYPT='%s'; SET @PWD_MD5='%s';\",
        password_hash(getenv('MDP'), PASSWORD_BCRYPT), md5(getenv('MDP')));" \
  > /tmp/sets.sql
cat /tmp/sets.sql 10_jeu_de_test.sql | mysql -u root -p travaux_recette
rm /tmp/sets.sql
```

`@PWD_MD5` alimente le compte à hash historique (`legacy@…`), qui sert à
vérifier la migration automatique vers bcrypt à la première connexion.

---

## Sessions créées

| Réf. plan | Id | Date | Ce qu'elle permet de tester |
|---|---|---|---|
| T-BROUILLON | 9001 | J+20 | Statut brouillon : invisible côté famille |
| T-FUTUR | 9002 | J+15 | Inscription nominale, 4 places, 2 unités, école M |
| T-PLEIN | 9003 | J+10 | Session complète : 2 places, 2 inscrits |
| T-PASSE | 9004 | J−5 | Validation par le référent, 3 inscrits non validés |
| T-VIEUX | 9005 | J−45 | Archivage automatique au-delà de 30 jours |
| T-URG | 9006 | aucune | Type urgence : jamais archivé, pas de date |
| T-LUT | 9007 | J+12 | Cloisonnement par école (Lutterbach) |
| T-AN-1 | 9008 | J−300 | Cloisonnement par année civile |
| T-ACTION | 9009 | J−3 | Session de type Action dans la file de validation |
| T-REF-KO | 9010 | J+6 | Chaîne référent rompue : aucun mail, aucune erreur |
| T-DERNIERE-PLACE | 9011 | J+8 | Concurrence sur la dernière place (3 places, 2 prises) |
| T-CAN | 9020–9024 | semaine en cours | Cantine lundi, mardi, jeudi, vendredi (vendredi saturé) |

Les dates sont **relatives au jour de chargement** : le jeu reste valide
quelle que soit la date d'exécution.

---

## Référents de session

Un référent n'est proposé sur une session **que s'il est déclaré membre
d'une commission**. L'application construit la liste déroulante
« Référent » du formulaire de session avec cette requête (`Travaux.json`,
champ `referent_travaux`) :

```sql
SELECT tr.id, CONCAT_WS(' ', gr.short, gm.name, gm.surname) AS title
  FROM trombi tr
  LEFT JOIN groupes_member gm ON tr.ref = gm.id
  LEFT JOIN groupes gr        ON tr.id_grp = gr.id
 WHERE tr.classif IN ('reftra','RT');
```

Trois conditions sont donc nécessaires :

1. une ligne `groupes_member` rattachée à la famille (colonne `id_fam`) ;
2. une ligne `trombi` rattachant ce membre à une commission (`id_grp`),
   avec `classif` valant **`reftra`** (Réfèrent de session) ou **`RT`**
   (Responsable de commission) ;
3. la commission doit porter un libellé court (`groupes`.`short`), qui
   sert de préfixe dans la liste.

Le jeu de test monte deux commissions et cinq rattachements :

| `trombi` | Commission | Personne | `classif` | Proposé comme référent ? |
|---|---|---|---|---|
| 9001 | TRAVAUX | Leroy Sophie (famille 9003) | `reftra` | Oui — référente principale |
| 9002 | TRAVAUX | Chaîne Rompue (`id_fam` non numérique) | `reftra` | Oui, mais aucune famille derrière |
| 9003 | TRAVAUX | Martin Claire (famille 9001) | `reftra` | Oui — second référent |
| 9004 | BUREAU | Petit Julie (famille 9005) | `RT` | Oui — responsable de commission |
| 9005 | TRAVAUX | Petit Julie (famille 9005) | `ME` | **Non** — membre simple |

La liste déroulante doit donc afficher **quatre** entrées, libellées
`TRAVAUX Leroy Sophie`, `TRAVAUX Chaine Rompue`, `TRAVAUX Martin Claire`
et `BUREAU Petit Julie`. La ligne `ME` sert de contrôle inverse : elle ne
doit pas apparaître.

Les sessions sont réparties sur deux référents, pour que l'écran
« mes sessions » donne des listes différentes selon la famille connectée :

- **Leroy Sophie** (famille 9003) : toutes les sessions sauf 9011 ;
- **Martin Claire** (famille 9001) : la session 9011 uniquement ;
- la session 9010 pointe sur la chaîne rompue : aucun référent ne doit
  être retrouvé, et aucun e-mail ne doit partir, sans erreur.

---

## Jetons de validation référent

URL à tester : `<base_url>/Admwork_controller/validate_by_token/<token>`

| Cas | Jeton | Comportement attendu |
|---|---|---|
| Valide | `a1b2c3d4e5f6…8f90` | L'écran de validation s'ouvre sur la session 9004 |
| Expiré | `b1b2c3d4e5f6…8f91` | Refus explicite |
| Déjà utilisé | `c1b2c3d4e5f6…8f92` | Refus, sans double comptage d'unités |

Les valeurs complètes figurent dans `10_jeu_de_test.sql`, section 8.

---

## Import CSV

`csv/import_ok.csv` contient :

- `ZZIMPORT01` — nouvelle famille, fratrie répartie sur les deux écoles :
  l'école calculée doit passer à **B** ;
- `ZZIMPORT02` — nouvelle famille, e-mail en MAJUSCULES : doit être
  normalisé en minuscules ;
- `ZZTEST006` — famille existante avec adresse et ville modifiées :
  doit apparaître en **modification**, sans perdre ses unités ni son
  mot de passe ;
- `ZZTEST001` — famille existante inchangée : ne doit produire **aucune**
  modification.

Un réimport immédiat du même fichier doit donner un différentiel vide
(test d'idempotence, cas T13-04).

Les fichiers sont encodés en **CP1252** avec le séparateur `;`, conformément
au format d'export ABCM attendu par l'application.

---

## Purge

```bash
mysql -u root -p travaux_recette < database/sql/jeu_de_test/90_purge_jeu_de_test.sql
```

Toutes les lignes du jeu portent un identifiant dans la plage réservée
**9000–9999** et un marqueur textuel (`[RECETTE]`, `ZZTEST`, `ZZIMPORT`).
La purge ne filtre que sur cette plage.

Elle retire aussi le type de session `URG` ajouté au référentiel par le jeu
de test. Si ce type doit rester disponible en dehors des campagnes, le
réinsérer à la main dans la table `options` avec un identifiant hors plage
réservée. Le script affiche en fin
d'exécution le nombre de lignes restantes, qui doit être nul.

---

## Référentiel des options

`database/sql/Options.sql` reprend désormais le dump de
production (49 entrées). Les types de session proposés sont donc ceux du
site réel : `MEN`, `TRA`, `GOU`, `LAV`, `DEC`, `can`, `INF`, `URG` et
`cve`. Le jeu de test n'ajoute plus aucune option — en particulier le type
`URG`, que la production déclare déjà.

La table `options` est en `latin1` (comme en production). Ce n'est pas un
défaut : les valeurs se relisent correctement en UTF-8, encodage utilisé
par l'application.

---

## Vérifications réalisées

La séquence complète a été rejouée sur une base MariaDB 10.11 vide :

| Contrôle | Résultat |
|---|---|
| Chargement depuis une base vide | 31 tables créées, aucune erreur |
| Rechargement du jeu (x2) | Effectifs identiques — script rejouable |
| Filtrage des sessions (requête `GetFiltered`) | Brouillon, autre école et année précédente correctement exclus |
| Liste déroulante « Référent » | 4 entrées proposées avec le libellé attendu ; le membre `ME` est bien exclu |
| Chaîne référent | Remonte la famille attendue ; la chaîne volontairement rompue ne remonte rien, sans erreur SQL |
| Répartition des référents | Deux familles référentes distinctes (13 sessions / 1 session) |
| Capacité des sessions | Session complète et session à une place restante conformes |
| Totaux d'unités | 4 unités pour U-FAM2 (1 de session + 3 complémentaires), 0 pour U-FAM1 |
| Jetons référent | Les trois états attendus : valide, expiré, déjà utilisé |
| Mots de passe | Générés au chargement puis vérifiés avec `password_verify()` et `md5()` de PHP 8.4 |
| Garde-fou | Le chargement sans `@PWD_BCRYPT` / `@PWD_MD5` s'arrête avec un message explicite |
| Fichiers CSV | Analysés avec la logique de `_parse_csv_abcm()` : 4 familles et école « B » calculée sur la fratrie ; le fichier corrompu déclenche bien les trois types d'erreur |
| Purge | Retire le jeu et laisse intactes les lignes hors plage réservée |

---

## Point d'attention sur le schéma

Les dumps de migration présents dans le dépôt datent d'une version
antérieure du schéma. Deux écarts notables :

- la table `trombi` n'a pas la colonne `ref`, sur laquelle repose toute la
  chaîne reliant une session à sa famille référente ;
- la table `groupes` n'a pas les colonnes éditoriales (`short`, `intro`,
  `mission`…) que le code affiche ;
- `mig_family_import.sql` commence par `ALTER TABLE famille`, alors
  qu'aucun script du dépôt ne crée cette table : il échoue sur une base
  vide. `00_schema_complement.sql` reprend son contenu, il n'est donc
  pas à jouer séparément.

Conséquence visible en recette : le dump `Trombi.sql` contient d'anciennes
lignes dont la colonne `ref` est vide (elle n'existait pas à l'époque du
dump). Deux d'entre elles portent `classif = 'RT'` et apparaissent donc
comme des **entrées vides** dans la liste déroulante « Référent ». Elles
viennent du dépôt, pas du jeu de test. Pour les neutraliser sur
l'environnement de recette :

```sql
DELETE FROM trombi WHERE (ref IS NULL OR ref = '') AND id < 9000;
```

Une base montée uniquement à partir des dumps du dépôt ne permet donc pas
de faire fonctionner l'application. `00_schema_complement.sql` corrige ces
écarts, mais la remise à niveau des dumps eux-mêmes reste à faire.
