url="https://regio.dev-asso.fr/api"
echo "étape 1 : se logger, obtenir le token"
body='{"login":"admin","password":"adminregio2022!","api-key":"ABCDEFGHIJKLMNOP","type_cnx":"NORM"}'
token=$(curl -s -d "$body" -X POST "$url/login" | jq -r '.jwt')
echo "token : $token"
sleep 1
echo "Sendmail_model"
curl -s -H "Authorization: Bearer $token" -X GET "$url/SetTable/Sendmail_model" | jq '.' 
sleep 1
echo "Sendmail_statut_model"
curl -s -H "Authorization: Bearer $token" -X GET "$url/SetTable/Sendmail_statut_model" | jq '.' 
sleep 1
echo "Sendmail_statut_model"
curl -s -H "Authorization: Bearer $token" -X GET "$url/SetTable/GetTables" | jq '.' 

