
url="https://regio.dev-asso.fr/api"
body='{"login":"admin","password":"adminregio2022!","api-key":"ABCDEFGHIJKLMNOP","type_cnx":"NORM"}'
token=$(curl -s -d "$body" -X POST "$url/login" | jq -r '.jwt')

sleep 1
curl -H "Authorization: Bearer $token" -X GET "$url/mails/" %1 | jq '.'

