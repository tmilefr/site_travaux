#!/usr/bin/env bash
# =====================================================================
#  Chargement du jeu de test de recette — site_travaux
# ---------------------------------------------------------------------
#  Génère les hashes de mot de passe puis charge le jeu de données.
#  Les mots de passe ne sont jamais écrits dans un fichier versionné.
#
#  Usage :
#     ./charger_jeu_de_test.sh <base> [utilisateur_mysql]
#
#  Le mot de passe des comptes de test est demandé de façon interactive,
#  ou repris de la variable d'environnement MDP_RECETTE si elle existe.
#
#  Exemples :
#     ./charger_jeu_de_test.sh travaux_recette
#     MDP_RECETTE='MonMotDePasse' ./charger_jeu_de_test.sh travaux_recette root
# =====================================================================
set -euo pipefail

DB="${1:-}"
DBUSER="${2:-root}"
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ -z "$DB" ]; then
  echo "Usage : $0 <base> [utilisateur_mysql]" >&2
  exit 1
fi

command -v php >/dev/null || { echo "Erreur : php est requis pour générer le hash bcrypt." >&2; exit 1; }

MDP="${MDP_RECETTE:-}"
if [ -z "$MDP" ]; then
  read -rsp "Mot de passe à donner aux comptes de test : " MDP; echo
  read -rsp "Confirmer : " MDP2; echo
  [ "$MDP" = "$MDP2" ] || { echo "Erreur : les deux saisies diffèrent." >&2; exit 1; }
fi
[ -n "$MDP" ] || { echo "Erreur : mot de passe vide." >&2; exit 1; }

# Hashes générés à la volée, jamais écrits sur disque.
SETS=$(MDP="$MDP" php -r '
  $p = getenv("MDP");
  printf("SET @PWD_BCRYPT = %s; SET @PWD_MD5 = %s;",
         "\x27".password_hash($p, PASSWORD_BCRYPT)."\x27",
         "\x27".md5($p)."\x27");
')

echo "Chargement du jeu de test dans la base « $DB »…"
{ printf '%s\n' "$SETS"; cat "$DIR/10_jeu_de_test.sql"; } \
  | mysql -u "$DBUSER" -p "$DB"

echo
echo "Jeu de test chargé. Mot de passe des comptes : celui que vous venez de saisir."
echo "Comptes : admin.recette@…, famille1@…, famille2@…, referent@…, legacy@…, famille3@…"
