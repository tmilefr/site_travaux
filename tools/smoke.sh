#!/usr/bin/env bash
# Test de fumee : se connecte puis appelle chaque couple controleur/action
# declare dans l'ACL (GET uniquement) et signale les erreurs serveur.
# Usage : tools/smoke.sh <base_url> <login> <password> <fichier_routes>
#   fichier_routes : lignes "Controleur<TAB>action"
BASE="${1:-http://localhost:8080}"; LOGIN="$2"; PASS="$3"; ROUTES="$4"
JAR="$(mktemp)"
curl -s -c "$JAR" -b "$JAR" -o /dev/null "$BASE/Home/login"
curl -s -c "$JAR" -b "$JAR" -o /dev/null -d "form_mod=&type_cnx=NORM&login=$LOGIN&password=$PASS" "$BASE/Home/login"
fail=0
while IFS=$'\t' read -r ctrl action; do
  [ -z "$ctrl" ] && continue
  case "$action" in logout|delete|bulk|truncate) continue;; esac
  out="$(mktemp)"
  code=$(curl -s -b "$JAR" -c "$JAR" -o "$out" -w '%{http_code}' --max-time 30 "$BASE/$ctrl/$action")
  ctype=$(curl -s -b "$JAR" -c "$JAR" -o /dev/null -w '%{content_type}' --max-time 30 "$BASE/$ctrl/$action")
  if grep -q 'Too few arguments' "$out" 2>/dev/null; then
    code=$(curl -s -b "$JAR" -c "$JAR" -o "$out" -w '%{http_code}' --max-time 30 "$BASE/$ctrl/$action/1")
    action="$action/1"
  fi
  err=$(grep -E -m1 'Fatal error|"type": "(Error|TypeError|ErrorException|RuntimeException|DatabaseException)"|Whoops|Uncaught|Undefined (variable|property|array key|index)|Call to (undefined|a member)' "$out" | cut -c1-160)
  msg=$(grep -E -m1 '"message":' "$out" | cut -c1-200)
  # une page HTML 200 doit etre complete (le rendu des vues ne doit pas etre perdu)
  if [ "$code" = "200" ] && echo "$ctype" | grep -q 'text/html' && ! grep -q '</html>' "$out"; then
    err="page HTML vide ou tronquee"
  fi
  if [ "$code" -ge 500 ] || [ -n "$err" ]; then
    echo "FAIL $code  $ctrl/$action  $msg $err"; fail=$((fail+1))
  else
    echo "ok   $code  $ctrl/$action"
  fi
  rm -f "$out"
done < "$ROUTES"
echo "--- $fail echec(s)"
rm -f "$JAR"
