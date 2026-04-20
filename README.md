
# Documentation

## Arboresence

```js
codeignter_implement/
└── application/
	├── core/
	│	└── MY_Controller.php => Core Controlleur (essentiellement un CRUD)
	├── libraries
	│	├── Render_object.php (factory)
	│	├── Form_validation.php (override core Form_validation)
	│	├── Acl.php (auth)
	│	├── Bootstrap_tools.php => implémentation de bootstrap dans le rendu.
	│	├── Render_object.php => Objet de Rendu utilisé pour générer un élement ( de formulaire, de liste, de vue ... )
	│	└── Elements
	│			└──── xxx.php (element)
	├── models/
	│	├── json
	│	│	├── XXX.json => Définition de la table 
	│	│	└── Menu.json => Menu
	│	├── Acl_users_model.php ( model implement )
	│	└── Core_model.php
	│
	└── views
		├── template
		│	├── head.php
		│	└── footer.php
		├── edtion
		│	└── XXXX_form.php => edition de la vue XXX
		└── unique
			├── XXXX_view.php => vue complète d'un élement XXX 
			└── list_view.php => vue en liste classique
```

## Controlleur

```php
class Users_controller extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->_controller_name = 'Users_controller';		//controller name for routing
		$this->_model_name	= 'Users_model';	   		//DataModel
		$this->_edit_view	= 'edition/Users_form';		//Vue d'édition
		$this->_list_view	= 'unique/Users_view.php';  //Vue de rendu d'un élément
		$this->_autorize	= array('add'=>true,'edit'=>true,'list'=>true,'delete'=>true,'view'=>true); //Vue activée
		$this->title .= ' - '.$this->lang->line($this->_controller_name); //pour spécialiser la page.
		$this->init(); //lancement.
	}

}
```

## Model

Acl_users_model.php
```php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

class Acl_users_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		$this->_set('table'	, 'acl_users');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Acl_users.json');
	}

}
```

## Schéma Json d'une table (exemple partiel)

```json
{
	"id": {
		"type": "hidden",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "INT",
			"constraint": "11",
			"unsigned": true,
			"auto_increment": true
		}
	},
	"name": {
		"type": "input",
		"list": true,
		"search": true,
		"rules": "trim|required|min_length[5]|max_length[255]",
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
	"section": {
		"type": "select",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"values": {
			"1": "Motonautisme",
			"2": "Ski",
			"3": "Voile",
			"4": "Wake"
		},
		"dbforge": {
			"type": "INT",
			"constraint": "5"
		}
	},
	"family": {
		"type": "select_database",
		"list": true,
		"search": false,
		"rules": null,
		"since": 2,
		"values": "distinct(family,id:name)",
		"dbforge": {
			"type": "INT",
			"constraint": "5"
		}
	}	
}
```

## Vue d'édtion  (exemple partiel)

```html
<div class="container-fluid">
<?php
echo form_open('Users_controller/add', array('class' => '', 'id' => 'edit') , array('form_mod'=>$form_mod,'id'=>$id) );

echo form_error('name', 	'<div class="alert alert-danger">', '</div>');
?>
<div class="form-row">
	<div class="form-group col-md-4">
		<?php 
			echo $this->bootstrap_tools->label('name');
			echo $this->render_object->RenderFormElement('name'); 
		?>
	</div>
</div>
<button type="submit" class="btn btn-primary"><?php echo Lang($form_mod.'_'.$this->router->class);?></button>
<?php
echo form_close();
?>
</div>
```

## rendu d'un element  (exemple partiel)

```html
<div class="card">
	  <div class="card-header">
		<?php echo $this->render_object->RenderElement('name').' '.$this->render_object->RenderElement('surname');?> / <?php echo $this->render_object->RenderElement('family');?>
	  </div>
	  <div class="card-body">
		<h5 class="card-title">
			<?php 
				echo $this->render_object->RenderElement('email'); 
			?>
		</h5>
	  </div>
</div>
```

## Objets

### element.php

#### Événement exploité par le CORE Controller sur Action (ADD/EDIT)
```php
class element_XXXX extends element
{
	private function RenderElement(){}
	public function PrepareForDBA($value){}
	public function RenderFormElement(){}
	public function Render(){}
	public function AfterExec($datas){}
}

```

#### Param Json

```json
	"name": {
		"type": "element",
		"list": true/false, // in list view
		"search": true/false,// integrate in global search 
		"rules": "trim|required|min_length[2]|max_length[255]", //see Rules Reference 
		"since": 1,
		"dbforge": {
			"sql":"sql to make this field in case of migration",
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

#### Rules Reference 

| Rule	| Parameter| Description| 	Example| 
| :---:|:---:|:---:|:---:|
| required| 	No	| Returns FALSE if the form element is empty.	 | |
| matches| 	Yes	| Returns FALSE if the form element does not match the one in the parameter.| 	matches[form_item]| 
| regex_match| 	Yes	| Returns FALSE if the form element does not match the regular expression.| 	regex_match[/regex/]| 
| differs| 	Yes| 	Returns FALSE if the form element does not differ from the one in the parameter.	| differs[form_item]| 
| is_unique| 	Yes| 	Returns FALSE if the form element is not unique to the table and field name in the parameter. Note: This rule requires Query Builder to be enabled in order to work.| 	is_unique[table.field]| 
| min_length| 	Yes	| Returns FALSE if the form element is shorter than the parameter value.| 	min_length[3]| 
| max_length| 	Yes	| Returns FALSE if the form element is longer than the parameter value.| 	max_length[12]| 
| exact_length| 	Yes	| Returns FALSE if the form element is not exactly the parameter value.| 	exact_length[8]| 
| greater_than	| Yes| 	Returns FALSE if the form element is less than or equal to the parameter value or not numeric.|	greater_than[8]|
| greater_than_equal_to| 	Yes	| Returns FALSE if the form element is less than the parameter value, or not numeric.|	greater_than_equal_to[8]|
| less_than| 	Yes| 	Returns FALSE if the form element is greater than or equal to the parameter value or not numeric.|	less_than[8]|
| less_than_equal_to| 	Yes	| Returns FALSE if the form element is greater than the parameter value, or not numeric.|	less_than_equal_to[8]|
| in_list| 	Yes| 	Returns FALSE if the form element is not within a predetermined list.|	in_list[red,blue,green]|
| alpha| 	No	| Returns FALSE if the form element contains anything other than alphabetical characters.||	 
| alpha_numeric| 	No	| Returns FALSE if the form element contains anything other than alpha-numeric characters.||	 
| alpha_numeric_spaces| 	No| 	Returns FALSE if the form element contains anything other than alpha-numeric characters or spaces. Should be used after trim to avoid spaces at the beginning or end.||	 
| alpha_dash| 	No| 	Returns FALSE if the form element contains anything other than alpha-numeric characters, underscores or dashes.||	 
| numeric| 	No| 	Returns FALSE if the form element contains anything other than numeric characters.||	 
| integer	| No| 	Returns FALSE if the form element contains anything other than an integer.	 ||
| decimal| 	No| 	Returns FALSE if the form element contains anything other than a decimal number.||	 
| is_natural| 	No	| Returns FALSE if the form element contains anything other than a natural number: 0, 1, 2, 3, etc.	 ||
| is_natural_no_zero| 	No|	Returns FALSE if the form element contains anything other than a natural number, but not zero: 1, 2, 3, etc.||	 
| valid_url| 	No| 	Returns FALSE if the form element does not contain a valid URL.	 ||
| valid_email| 	No| 	Returns FALSE if the form element does not contain a valid email address.	|| 
| valid_emails| 	No| 	Returns FALSE if any value provided in a comma separated list is not a valid email.	|| 
| valid_ip| 	Yes	| Returns FALSE if the supplied IP address is not valid. Accepts an optional parameter of ‘ipv4’ or ‘ipv6’ to specify an IP format.	 ||
| valid_base64	| No| 	Returns FALSE if the supplied string contains anything other than valid Base64 characters.	|| 




### element_captcha.php

param re-captcha

```php 
secured.php
CONST SITE_CAPTCHA_KEY = '';
CONST SITE_CAPTCHA_SECRET_KEY = '';
CONST SITE_CAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

$config['captcha'] = TRUE/FALSE;
```

### element_checkbox.php
Liste de chechbox sur la base des valeurs dans "values"
```json
	"checkbox": {
		"type": "checkbox",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"values": {
			"1": "valeur 1",
			"2": "Valeur 2",
			"3": "Vakeur 3"
		},		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

### element_checkboxdb.php
Un objet checkbox en relation avec une liste dans une table.
ex de la table option avec un filtre sur la table.

Parent
|id|Field_1|Field_2| 
| :---:|:---:|:---:|
|1|1|xxx|
|2|2|yyy|

liaison
| id | key | Field_3 | 
| :---:|:---:|:---:|
|1|1|aaaa|
|2|1|bbbb|

Relation : Parent.id = Liaison.key

```json
"checkboxdb":{
		"sql" : "",
		"type": "checkboxdb",
		"list": false,
		"search": false,
		"rules": null,
		"param":"distinct(options,cle:value#filter=yyyy)",
		"values":[],
		"model": "xxx_model", //model qui pilote la modification sur la table de liaison
		"ref":"Field_1", //référence formulaire
		"foreignkey":"key",	//clé étrangère dans la table de laison	
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

xxx_model.json
```json
{
	"id": {
		"type": "hidden",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "INT",
			"constraint": 11,
			"unsigned": true,
			"auto_increment": true
		}
	},
	"key": {
		"type": "select_database",
		"param":"distinct(Parent,id:Field_1@Field_2)",
		"alternate_field":"Field_1",
		"values":[],
		"list": true,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "INT",
			"constraint": "11"
		}
	},
	"created": {
		"type": "created",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "DATETIME"
		}
	},
	"updated": {
		"type": "updated",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "DATETIME"
		}
	}						
}
```

### element_date.php
Popup avec choix de la date
```json
"date": {
		"type": "date",
		"list": true,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
### element_file.php

### element_html.php
Élément WSYWIG (ckeditor)
```json
"html": {
		"type": "html",
		"list": false,
		"search": true,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
### element_memo.php
```json
	"adresse": {
		"type": "memo",
		"rows" : 1,
		"list": false,
		"search": true,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},	
```
### element_month.php
```json
```
### element_password.php
```json
	"password": {
		"type": "password",
		"list": true,
		"search": true,
		"rules": "trim|required|min_length[2]|max_length[255]",
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
### element_select_database.php
```json
	"id": {
		"type": "select_database",
		"param":"distinct(acl_controllers,id:controller)",
		"values":[],
		"list": false,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"type": "INT",
			"constraint": "10"
		}
	},
```
### element_select.php
```json
	"select": {
		"type": "select",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"values": {
			"1": "valeur 1",
			"2": "Valeur 2",
			"3": "Vakeur 3"
		},		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
### element_service.php
```json
```
### element_table.php
Un objet table dynamique en relation avec une liste dans une table.
ex de la table option avec un filtre sur la table.

voir element_checkboxdb.php

```json
"table": {
		"type": "table",
		"link" : "",
		"sql":"ALTER TABLE `famille` ADD `e_mail_comp` VARCHAR(255) NULL AFTER `e_mail`;", //infos
		"list": false,
		"search": false,
		"rules": "trim",
		"since": 1,
		"model": "xxx_model", //model à utiliser pour la table liaison
		"ref":"Field_1", //champ de référence
		"foreignkey":"key", //lien entre les tables maire et secondaire.
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},	
```

### element_time.php
Popup avec choix de l'heure
```json
	"time": {
		"type": "time",
		"list": false,
		"search": false,
		"rules": "trim|required",
		"since": 1,
		"minTime": "08:00:00",
		"maxHour": 20,
		"maxMinutes": 30,
		"interval": 15,
		"startTime":14,		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
### element_typeahead.php
Moteur de recherche dynamique qui pointe sur une table
```json
	"referent_travaux": {
		"type": "typeahead",
		"list": true,
		"search": true,
		"rules": null,
		"param": "distinct(groupes,id:title)",
		"values": [],
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

### element_created.php
date time pour les traces
```json
	"created": {
		"type": "created",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "DATETIME"
		}
	},
```

### element_updated.php
date time pour les traces
```json
	"updated": {
		"type": "updated",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "DATETIME"
		}
	}
```

## API CALL

### Param du controlleur API

```php
class Api extends MY_Controller {

	/* Chaque objet exposé à besoin d'un entrée de controller */

	/**
	 * Entry Point FOR Familys (exemple of 'correct' implement of API)
	 * Don't forget set rules @Acl_controllers_controller/edit/14 [14 = id of api controller]
	 * And manage Roles @Acl_roles_controller/set_rules/1 [1 = id of admin role]
	 * 
	 * @param mixed $id 
	 * @return void 
	 */
	public function Familys($id = null ){
		$this->_SetHeaders(['GET','OPTIONS']); 
		$this->_getObject('Familys_model', $id);
	}


```

### Dans un Controller

```php
$api = [
	'base_url'  => base_url('API/'),
	'user_agent' => "php",
	'headers'=>[
		'Authorization' => 'Bearer '.$this->auth->_get('connected_user')->token            
	]
];        
$this->restclient->init($api);
//Appel d'une api par la page, avec l'utilisateur connecté        
$result = $this->restclient->get('Familys');        
if ($result->error)
	echo debug($result->error);

echo debug(json_decode($result->response)); 
```

### Dans une page au format JS
```html
<div id="get_api_content"></div>

<script type="text/javascript">
    function GETAPI() {
        var settings = {
            'cache': false,
            'dataType': "json",
            "async": false,
            "crossDomain": true,
            "url": "<?php echo base_url('API/Familys');?>",
            "method": "GET",
            "headers": {
                "accept": "application/json",
                "Access-Control-Allow-Origin":"<?php echo base_url();?>",
                "Authorization" : "Bearer <?php echo $this->auth->_get('connected_user')->token;?>"
            }
        }
        $.ajax(settings).done(function (response) {
          console.log(response[0].role_name);

          $( "#get_api_content" ).html( response[0].role_name.render );;
        });
    }

    GETAPI();
    //const intervalID = setInterval(GETCOUNT, 1000);
</script>
```
## Test manuel

POST /api/login
```json
{
  "login":"xxxxxxx",
  "password":"xxxxxxx",
  "api-key":"xxxxxxx",
  "type_cnx":"NORM"
}
```
=> resultat
```json
{
    "message": "Successful login.",
    "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJBdXRoIiwiYXVkIjoiQVBJIGFjY2VzcyIsImlhdCI6MTY4NzM2MDYxOCwibmJmIjoxNjg3MzYwNjE5LCJleHAiOjE2ODczNjY2MTgsImRhdGEiOnsiYXV0b3JpemUiOnRydWUsInR5cGUiOiJzeXMiLCJuYW1lIjoiYWRtaW4iLCJpZCI6IjEiLCJyb2xlX2lkIjoiMSIsIm1zZyI6IjxpPkpXVF9BQ0NFU1M8L2k-IiwidG9rZW4iOiIiLCJleHBpcmVBdCI6MTY4NzM1ODU5MX19.xxxxxxxxxx",
    "id": "1",
    "role_id": "1",
    "type": "sys",
    "expireAt": 1687366618,
    "expireAtRender": "2023-06-21 16:56:58"
}
```

## Envois d'e-mail

### Création d'un e-mail
POST /api/mails ( Authorization Bearer Token xxxx token du login xxxxx )
```json
{
  "reference":"envois",
  "email":"test@test.com",
  "message":"ceci est un test de message",
  "object":"test"
}

```

### Lecture 
GET /api/mails/ renvoi les e-mails dans la base
GET /api/mails/7 renvoi l'e-mail id = 7

=> resultat 

```json
{
    "id": {
        "raw": "7",
        "render": "7"
    },
    "reference": {
        "raw": "envois",
        "render": "envois"
    },
    "email": {
        "raw": "test@test.com",
        "render": "test@test.com"
    },
    "object": {
        "raw": "test 2",
        "render": "test 2"
    },
    "message": {
        "raw": "ceci est un test de message",
        "render": "ceci est un test de message"
    },
    "statut": {
        "raw": "1",
        "render": "envoyé"
    },
    "error": {
        "raw": "<pre>\n\n</pre>",
        "render": "<pre>\n\n</pre>"
    },
    "created": {
        "raw": "2023-06-21 01:03:25",
        "render": "2023-06-21 01:03:25"
    },
    "updated": {
        "raw": "2023-06-21 02:20:02",
        "render": "2023-06-21 02:20:02"
    }
}
```
### Modifcation de l'e-mail

PUT /api/mails/
```json
{
  "id":7,
  "reference":"envois",
  "email":"test@test.com",
  "message":"ceci est un test de message",
  "object":"test 2"
}

ou

PUT /api/mails/7
```json
{
  "reference":"envois",
  "email":"test@test.com",
  "message":"ceci est un test de message",
  "object":"test 2"
}
```

### Implementation de l'envoi
En console 
```php
php index.php cron sendmail

```




## Git Flow :

develop est rattaché à l'environnement https://regio.dev-asso.fr
main est rattaché à l'environnement https://mulhouse-travaux.abcmzwei.eu/

branches de feature "feature-xxx" à partir de "develop" et faire une demande de merge c'est quand fini
Ensuite, on met à jour l'environnement regio pour tester (git deploy sur le serveur )
sur notre validation, on pousse en prod (git deploy sur le serveur)

Pour les hotfix, branche de hotfix "hotfix-xxx" à partir de "main", et cherry pick sur main après validation sur la production.

# Framework CI3 — Optimisations v2

## Fichiers modifiés

| Fichier | Statut |
|---|---|
| `application/models/Acl_users_model.php` | ✅ Corrigé |
| `application/models/Core_model.php` | ✅ Corrigé |
| `application/libraries/Acl.php` | ✅ Corrigé |
| `application/libraries/elements/element_password.php` | ✅ Corrigé |
| `application/libraries/elements/element_file.php` | ✅ Corrigé |
| `migration_passwords.sql` | 🆕 Nouveau |

---

## Détail des corrections

### 🔴 Acl_users_model.php — Bug critique d'authentification

**Problème :** `verifyLogin()` accordait l'accès à n'importe quel utilisateur existant,
quel que soit le mot de passe. Le `if (hash_equals(...))` ne faisait rien car
`$usercheck->autorize = true` était positionné **en dehors** de la condition.
De plus, un `echo "Mot de passe correct !"` était présent en production.

**Correction :**
- La condition de vérification du mot de passe contrôle maintenant réellement l'accès.
- `crypt()` + sel fixe remplacé par `password_verify()` (bcrypt).
- Migration transparente : les anciens hash `crypt()` sont migrés vers bcrypt
  silencieusement lors de la prochaine connexion réussie.
- Nouvelle méthode `hashPassword()` centralisée pour le hashage.
- `echo` de debug supprimé.

---

### 🔴 element_password.php — Hashage faible

**Problème :** `crypt()` avec un sel fixe (`PASSWORD_SALT`) est vulnérable aux
attaques par table arc-en-ciel et brute-force modernes.

**Correction :** `password_hash($value, PASSWORD_BCRYPT)` — bcrypt avec sel
aléatoire par défaut, coût adaptatif.

---

### 🔴 element_file.php — Upload sans validation

**Problème :** Aucune vérification du type de fichier ni de la taille.
Un attaquant pouvait uploader un fichier PHP et l'exécuter.
`die()` et `echo debug()` présents en production.

**Correction :**
- Validation de l'extension (liste blanche).
- Validation du type MIME réel via `finfo_open()` (pas le Content-Type HTTP).
- Limite de taille configurable (2 Mo par défaut).
- Nom de fichier unique généré avec `uniqid()` pour éviter les collisions
  et les attaques par path traversal.
- `die()` remplacé par un `set_flashdata()` + `return`.
- `echo debug()` supprimé.

---

### 🟠 Acl.php — DontCheck TRUE par défaut

**Problème :** `$DontCheck = TRUE` signifie que TOUTES les pages sont
accessibles sans authentification si on oublie de mettre `DontCheck = FALSE`.
C'est l'inverse de ce qu'on veut (secure by default).

**Correction :** `$DontCheck = FALSE` — les contrôleurs publics doivent
explicitement déclarer `protected $DontCheck = TRUE`.

---

### 🟠 Acl.php — Permissions rechargées à chaque requête

**Problème :** `getRolePermissions()` était appelé à chaque requête HTTP,
générant une requête SQL inutile pour chaque page.

**Correction :** Cache en session par `role_id` :
- Chargé une fois depuis la BDD, stocké en session sous la clé `acl_perms_{role_id}`.
- Invalidé automatiquement à la déconnexion.
- Méthodes `_getPermissionsFromCache()` et `_loadAndCachePermissions()` ajoutées.

---

### 🟠 Acl.php — Bug CheckLogin()

**Problème :** `CheckLogin()` lisait `$this->usercheck->autorize` après avoir
mis à jour la session, mais `$this->usercheck` n'était pas rechargé.

**Correction :** On vérifie `$usercheck->autorize` (variable locale, fraîchement
retournée par `verifyLogin()`) plutôt que `$this->usercheck->autorize`.

---

### 🟡 Core_model.php — Bug _set_list_fields()

**Problème :** `$string_field .= substr($string_field, -1)` ajoutait le
dernier caractère de la chaîne (une virgule) au lieu de rien de utile,
ce qui pouvait générer des requêtes SQL invalides.

**Correction :** Refactorisation complète avec `array_map` + `implode` :
plus de concaténation manuelle, plus de virgule finale.

---

### 🟡 Core_model.php — get_all() sans limite

**Problème :** `get_all()` sans clause `LIMIT` pouvait charger des centaines
de milliers de lignes en mémoire.

**Correction :** Paramètre `$limit = 1000` (passer `0` pour désactiver).

---

## Migration des mots de passe

Voir `migration_passwords.sql` pour identifier les comptes avec d'anciens
hash `crypt()` et les migrer.

La migration est **automatique** lors de la prochaine connexion de chaque
utilisateur (gérée dans `Acl_users_model::verifyLogin()`).

Penser à agrandir la colonne `password` :
```sql
ALTER TABLE acl_users MODIFY COLUMN password VARCHAR(255) NOT NULL;
```
