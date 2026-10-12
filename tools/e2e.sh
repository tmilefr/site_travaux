#!/usr/bin/env bash
# Recette de bout en bout : envois de fichiers/images, import CSV des familles, generation cantine.
# A lancer sur la BASE DE RECETTE uniquement (ecrit en base et dans public/files, jeu de test requis).
#
# Usage : tools/e2e.sh <base_url> <login_admin> <mot_de_passe>
# Env   : DB="mysql -uroot -proot --default-character-set=utf8mb4 regiomlh_prod"  (client SQL utilise pour verifier les resultats)
set -u
BASE="${1:-http://localhost:8080}"; LOGIN="$2"; PASS="$3"
DB="${DB:-mysql -uroot -proot --default-character-set=utf8mb4 regiomlh_prod}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d)"; JAR="$TMP/jar"; trap 'rm -rf "$TMP"' EXIT
php "$ROOT/tools/e2e/make_fixtures.php" "$TMP" || exit 1
ok=0; ko=0
check() { # check "libelle" "attendu" "obtenu"
  if [ "$2" = "$3" ]; then ok=$((ok+1)); echo "ok    $1"; else ko=$((ko+1)); echo "ECHEC $1 (attendu '$2', obtenu '$3')"; fi
}
sql() { $DB -N -e "$1" 2>/dev/null | tr '\n\t' '  ' | sed 's/ *$//'; }
curl_() { curl -s -b "$JAR" -c "$JAR" "$@"; }
code() { curl_ -o /dev/null -w '%{http_code}' "$@"; }

curl_ -o /dev/null "$BASE/Home/login"
curl_ -o /dev/null -d "form_mod=&type_cnx=NORM&login=$LOGIN&password=$PASS" "$BASE/Home/login"

echo "== Envois de fichiers (Files_controller) =="
up() { code -F form_mod=add -F id= -F "name=$1" -F type=doc -F memo=e2e -F statut=1 -F memory_path= -F "path=@$2" "$BASE/Files_controller/edit"; }
check "document .txt accepte" 302 "$(up e2e-doc "$TMP/doc.txt")"
up e2e-evil "$TMP/evil.php" >/dev/null
check "script .php refuse (aucune valeur enregistree)" "" "$(sql "select ifnull(path,'') from files where name='e2e-evil'")"
check "message d'erreur affiche apres refus" 1 "$(curl_ "$BASE/Files_controller/list" | grep -c 'Type de fichier refus')"
check "doublon renomme et non ecrase" 302 "$(up e2e-doc2 "$TMP/doc.txt")"
check "2 fichiers distincts sur disque" 2 "$(ls "$ROOT/public/files/doc" | wc -l | tr -d ' ')"
check ".php inaccessible par HTTP" 404 "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/files/doc/evil.php")"

echo "== Envois d'images (GroupesMembers_controller) =="
img() { code -F form_mod=add -F id= -F id_fam=9001 -F "name=e2e $1" -F surname=T -F email=t@t.fr -F phone=0 -F memory_thumbnail= -F memory_picture= -F "thumbnail=@$2" -F "picture=@$2" "$BASE/GroupesMembers_controller/edit"; }
check "JPEG accepte" 302 "$(img jpg "$TMP/photo.jpg")"
check "PNG accepte (500 avant correction)" 302 "$(img png "$TMP/photo.png")"
check "faux .jpg (PHP) refuse" 302 "$(img fake "$TMP/disguised.jpg")"
check "faux .jpg non conserve" 0 "$(ls "$ROOT/public/files/img/team" | grep -c fake || true)"
dims() { php -r 'echo implode("x", array_slice(getimagesize($argv[1]), 0, 2));' "$1"; }
check "miniature redimensionnee a 185 px de large" 185x139 "$(dims "$ROOT/public/files/img/team/thumbnail_photo.jpg")"
check "photo redimensionnee a 450 px de large" 450x338 "$(dims "$ROOT/public/files/img/team/picture_photo.png")"

echo "== Import CSV des familles =="
msg() { curl_ -F "csv_file=@$1" "$BASE/Familys_controller/import"; }
check "extension refusee" 1 "$(msg "$TMP/import_bad.txt" | grep -c 'Extension de fichier invalide')"
check "fichier vide refuse" 1 "$(msg "$TMP/import_empty.csv" | grep -c 'est vide')"
msg "$TMP/import_ok.csv" > "$TMP/preview.html"
check "apercu : 1 creation" 1 "$(grep -c 'ZZNEW001' "$TMP/preview.html")"
check "apercu : accents CP1252 decodes" 1 "$(grep -c 'Müller' "$TMP/preview.html")"
check "apercu : 2 lignes en erreur" 1 "$(grep -c 'too_few_cols' "$TMP/preview.html")"
STORED="$(grep -o 'name="stored_path" value="[^"]*"' "$TMP/preview.html" | sed 's/.*value="//;s/"//')"
TRAV="$(curl_ -o /dev/null -w '%{redirect_url}' -d 'stored_path=../../.env' "$BASE/Familys_controller/import_apply")"
check "chemin hors dossier refuse (retour au formulaire)" "$BASE/Familys_controller/import" "$TRAV"
check "import applique" 302 "$(code -d "stored_path=$STORED" "$BASE/Familys_controller/import_apply")"
check "famille creee" "Müller Brunstatt B" "$(sql "select concat(nom,' ',ville,' ',ecole) from famille where code_famille_abcm='ZZNEW001'")"
check "famille mise a jour (ville)" Riedisheim "$(sql "select ville from famille where code_famille_abcm='ZZTEST001'")"
check "enfant ajoute" Zoé "$(sql "select prenom from members where code_membre_abcm='ZZM099'")"
check "familles absentes du CSV marquees" 1 "$(sql "select to_deactivate from famille where code_famille_abcm='ZZTEST002'")"
check "historique enregistre" "1 2" "$(sql "select nb_created, nb_errors from family_import order by id desc limit 1")"

echo "== Generation cantine =="
MON="$(date -d 'next monday' +%F)"; FRI="$(date -d "$MON +4 days" +%F)"; CY="$(sql "select civil_year from cantine_config limit 1")"
code -d "ecole=L&active_1=1&nb_slots_1=2&nb_units_1=1&heure_deb_1=11:45&heure_fin_1=13:30&active_3=1&nb_slots_3=3&nb_units_3=1.5&heure_deb_3=11:45&heure_fin_3=13:30" "$BASE/Cantine_controller/save_config" >/dev/null
check "configuration enregistree (lundi + mercredi)" "2" "$(sql "select count(*) from cantine_config where ecole='L' and active=1")"
check "generation" 302 "$(code -d "ecole=L&period_mode=custom&date_deb=$MON&date_fin=$FRI" "$BASE/Cantine_controller/generate")"
check "2 sessions creees (lundi, mercredi)" 2 "$(sql "select count(*) from travaux where type='can' and accespar='L' and date_travaux between '$MON' and '$FRI'")"
check "journal de generation" "2 0" "$(sql "select nb_created, nb_skipped from cantine_generation order by id desc limit 1")"
code -d "ecole=L&period_mode=custom&date_deb=$MON&date_fin=$FRI" "$BASE/Cantine_controller/generate" >/dev/null
check "regeneration idempotente (2 ignorees)" "0 2" "$(sql "select nb_created, nb_skipped from cantine_generation order by id desc limit 1")"
check "page de configuration" 200 "$(code "$BASE/Cantine_controller/config?ecole=L")"

# nettoyage des fichiers deposes
rm -f "$ROOT"/public/files/doc/* "$ROOT"/public/files/img/team/*_photo.* 2>/dev/null
echo "--- $ok reussi(s), $ko echec(s)"
[ "$ko" -eq 0 ]
