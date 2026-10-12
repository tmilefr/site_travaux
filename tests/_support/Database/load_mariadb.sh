#!/usr/bin/env bash
# Charge dans MariaDB le schéma de production (sans données personnelles) puis la migration CI4.
# Variables : DB_HOST DB_PORT DB_USER DB_PASS DB_NAME (défauts ci-dessous).
set -euo pipefail

DIR="$(cd "$(dirname "$0")/../../.." && pwd)"
HOST="${DB_HOST:-127.0.0.1}"
PORT="${DB_PORT:-3306}"
USER="${DB_USER:-root}"
PASS="${DB_PASS:-root}"
NAME="${DB_NAME:-site_travaux_test}"

MYSQL=(mysql --protocol=tcp -h "$HOST" -P "$PORT" -u "$USER" "-p$PASS")

"${MYSQL[@]}" -e "DROP DATABASE IF EXISTS \`$NAME\`; CREATE DATABASE \`$NAME\` CHARACTER SET utf8mb4;"
"${MYSQL[@]}" "$NAME" < "$DIR/tests/_support/Database/sql/prod_schema.sql"
"${MYSQL[@]}" "$NAME" < "$DIR/database/sql/mig_prod_vers_ci4.sql"
# la migration est idempotente : on la rejoue pour le vérifier
"${MYSQL[@]}" "$NAME" < "$DIR/database/sql/mig_prod_vers_ci4.sql"
echo "Base $NAME prête."
