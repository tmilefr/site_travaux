# site_travaux

Application web de gestion des **travaux associatifs** et de la participation des familles (association ABCM Mulhouse-Lutterbach) :
inscription des familles aux sessions de travaux, validation des unités par les référents, garde du midi (cantine), organigramme, e-mails, API REST.

CodeIgniter **4.7** · PHP ≥ 8.2 · MariaDB · licence MIT (© Tmile, 2018).

> **Toute la documentation est dans [DOCUMENTATION.md](DOCUMENTATION.md)** : architecture, modèle de données, modules, guide de développement,
> API, tâches planifiées, configuration, déploiement, migration CodeIgniter 3 → 4 (code et base de données) et plan de test.

## Démarrage rapide

```bash
composer install --no-dev -o            # dépendances (PHP >= 8.2 avec intl, mbstring, curl, mysqli, gd)
cp env.example .env                     # puis renseigner app.baseURL, database.default.*, travaux.*, email.*
mysql -u USER -p BASE < database/sql/mig_prod_vers_ci4.sql    # base existante (sauvegarde préalable) — voir § 13
chmod -R u+rwX writable public/files public/data
php spark serve --port 8080             # développement ; en production le document root est public/
```

Tâches planifiées (voir [§ 9](DOCUMENTATION.md#9-tâches-planifiées-spark)) :

```bash
php spark cron:sendmail             # envoie la file d'e-mails
php spark cron:ref-validation       # lien de validation envoyé aux référents
php spark cron:session-alerts       # alertes « nouvelle session » aux familles abonnées
```

Vérifications (voir [§ 14](DOCUMENTATION.md#14-vérifications-automatisées)) :

```bash
php tools/check_classes.php
vendor/bin/phpunit tests/unit
tools/smoke.sh http://localhost:8080 <login> <mot_de_passe> routes.txt
tools/e2e.sh   http://localhost:8080 <login> <mot_de_passe>     # base de recette uniquement
```

## Sommaire de la documentation

| Sujet | Section |
|---|---|
| Vue d'ensemble, architecture, concepts métier, modèle de données | [§ 1 à 4](DOCUMENTATION.md#1-vue-densemble) |
| Authentification, ACL, modules fonctionnels | [§ 5, 6](DOCUMENTATION.md#5-authentification-et-acl) |
| Développer : schémas JSON, modèles, contrôleurs, vues, éléments, validation | [§ 7](DOCUMENTATION.md#7-développer-dans-le-projet) |
| API REST | [§ 8](DOCUMENTATION.md#8-api-rest) |
| Configuration, `.env`, serveur web, déploiement | [§ 10, 11](DOCUMENTATION.md#10-configuration-et-environnements) |
| Migration CodeIgniter 3 → 4 | [§ 12](DOCUMENTATION.md#12-migration-codeigniter-3--codeigniter-4) |
| **Migration de la base de production (script SQL)** | [§ 13](DOCUMENTATION.md#13-migration-de-la-base-de-données) |
| Plan de test | [§ 14, 15](DOCUMENTATION.md#14-vérifications-automatisées) |

Historique des évolutions : [CHANGELOG.md](CHANGELOG.md).
