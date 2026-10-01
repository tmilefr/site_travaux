<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

/**
 * API REST (JSON) : e-mails, familles, modèles de texte, login JWT, maintenance des tables.
 *
 * Chaque action renvoie un objet Response (jamais d'exit) ; les en-têtes CORS sont posés par cors().
 */
class Api extends CrudController
{

	/** Pas d'actions CRUD génériques pour ce contrôleur. */
	protected $_expose_crud = false;

	public $SQL;

	protected function boot(): void
	{
		$this->_api = TRUE; //déclaration du mode api sur ce contrôleur
	}

	/**
	 * Pose les en-têtes CORS selon la méthode HTTP.
	 *
	 * @param array $allowMethods méthodes acceptées par l'action
	 * @return ResponseInterface|null une réponse à renvoyer immédiatement
	 *                                (pré-requête OPTIONS ou méthode refusée), sinon null
	 */
	private function cors(array $allowMethods = ['POST','GET','DELETE','PUT','PATCH','OPTIONS']): ?ResponseInterface
	{
		$method = strtoupper($this->request->getMethod());

		if (! in_array($method, $allowMethods, true)) {
			return $this->_renderJson(405, ["message" => "Method Not Allowed"]);
		}

		$this->response->setHeader('Access-Control-Allow-Origin', '*');
		$this->response->setHeader('Access-Control-Allow-Credentials', 'true');

		if ($method === 'OPTIONS') { //nécessaire pour l'accès JS
			return $this->response
				->setHeader('Access-Control-Allow-Methods', implode(',', $allowMethods))
				->setHeader('Access-Control-Allow-Headers', 'token, Content-Type, Authorization')
				->setHeader('Access-Control-Max-Age', '1728000')
				->setContentType('text/plain')
				->setBody('');
		}

		$this->response->setHeader('Access-Control-Allow-Methods', implode(',', $allowMethods));
		$this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

		return null;
	}

	/**
	 * Entry Point FOR Templates (exemple of 'correct' implement of API)
	 * Don't forget set rules
	 * And manage Roles
	 *
	 * @param mixed $id
	 */
	public function Templates($id = null ){
		if ($early = $this->cors(['GET','OPTIONS'])) return $early;
		//ONLY GET
		return $this->_getObject('Templates_model', $id);
	}

	/**
	 * Entry Point FOR Familys (exemple of 'correct' implement of API)
	 * Don't forget set rules @Acl_controllers_controller/edit/14 [14 = id of api controller]
	 * And manage Roles @Acl_roles_controller/set_rules/1 [1 = id of admin role]
	 *
	 * @param mixed $id
	 */
	public function Familys($id = null ){
		if ($early = $this->cors(['GET','OPTIONS'])) return $early;
		//ONLY GET
		return $this->_getObject('Familys_model', $id);
	}

	public function GetTables(){
		if ($early = $this->cors(['GET','OPTIONS'])) return $early;

		$models = scandir(APPPATH.'Models/');
		$models = array_diff($models , array('..', '.', 'json','index.html','Core_model.php','GenericSql_model.php'));

		return $this->_renderJson(200, array_values($models));
	}

	/**
	 * Crée ou met à jour la table d'un modèle à partir de la définition "dbforge" de son schéma JSON.
	 *
	 * @param string $model_name
	 */
	public function SetTable($model_name = 'Sendmail_model'){
		if ($early = $this->cors(['GET','OPTIONS'])) return $early;

		$this->_model_name = $model_name;
		$this->LoadModel($this->_model_name);
		$forge_db = Database::forge();

		// Préparation pour DB forge
		$defs = $this->{$this->_model_name}->_get('defs');
		$forge = [];
		foreach($defs  AS $key=>$data){
			$def = [];
			foreach( $data->dbforge AS $type=>$value){
				$def[$type] = $value;
			}
			if (!isset($def['null']) && (!isset($def['auto_increment']) || !$def['auto_increment']))
				$def['null'] = TRUE;//on autorise le NULL par défaut si non défini
			$forge[$key] = $def;
		}

		$table = $this->{$this->_model_name}->_get('table');
		if (! $this->db->tableExists($table)) { //création de la table puis création des champs
			$forge_db->addField($forge);
			foreach($forge AS $field=>$def){
				if (isset($def['auto_increment']) && $def['auto_increment'] == true){
					$forge_db->addKey($field, TRUE);
				}
			}
			$forge_db->createTable($table, FALSE, ['ENGINE' => 'InnoDB']);
		} else { //juste mise à jour des champs au besoin
			$forge_db->modifyColumn($table, $forge);
		}

		if (!count($forge))
			return $this->_renderJson(204, ['message'=>'Not Found']);

		return $this->_renderJson(200, $forge);
	}

	/**
	 * WS de soumission d'e-mail
	 * Un pool d'envois en cron met à jour le statut de celui-ci
	 *
	 * @param mixed $id
	 */
	public function mails($id = null){
		if ($early = $this->cors(['POST','PUT','GET','DELETE','OPTIONS'])) return $early;

		$this->LoadModel('Sendmail_statut_model');
		$this->LoadModel('Sendmail_model');
		$this->_model_name = 'Sendmail_model';
		$this->render_object->_set('_render_model','json');

		$method = strtoupper($this->request->getMethod());
		$input  = $this->request->getJSON();

		switch($method)
		{
			case 'PUT':
				//TODO : block PUT if STATUS IS SENDED nL 26/06/2023
				//dans les données plutot que sur le path
				if (!$id  && isset($input->id))
					$id = $input->id;

				if (!$id){
					return $this->_renderJson(400, ['message'=>'id est requis ']);
				}

				$sendmail = (array) $input;
				//validation des données
				if ($this->runValidation($this->_model_name, $sendmail) === FALSE){
					return $this->_renderJson(400, ['message' => implode(' ', service('validation')->getErrors())]);
				}
				$sendmail['updated'] = date('Y-m-d H:i:s');
				$this->{$this->_model_name}->_set('key_value', $id);
				$this->{$this->_model_name}->_set('datas', $sendmail);
				$this->{$this->_model_name}->put();

				return $this->_renderJson(202 ,["id" => $id,'last_query'=>$this->{$this->_model_name}->_get('_debug_array')]);

			case 'POST':
				$sendmail = (array) $input;
				//validation des données
				if ($this->runValidation($this->_model_name, $sendmail) === FALSE){
					return $this->_renderJson(400, ['message' => implode(' ', service('validation')->getErrors())]);
				}
				$sendmail['created'] = date('Y-m-d H:i:s');
				$id = $this->{$this->_model_name}->post($sendmail);
				/* Init E-mail Statut  */
				$statut = [];
				$statut['id_sen'] = $id;
				$statut['date'] = date('Y-m-d H:i:s');
				$statut['statut'] = 0; //nouveau
				$statut['created'] = date('Y-m-d H:i:s');
				$id = $this->Sendmail_statut_model->post($statut);

				return $this->_renderJson(201 ,["id" => $id]);

			case 'GET':
				//version standard de l'exposition
				return $this->_getObject($this->_model_name, $id);

			case 'DELETE':
				//dans les donnes plutot que sur le path
				if (!$id  && isset($input->id))
					$id = $input->id;
				if (!$id){
					return $this->_renderJson(400, ['message'=>'id est requis ']);
				}
				$this->{$this->_model_name}->delete($id);

				return $this->_renderJson(200, ['id' => $id]);
		}

		return $this->_renderJson(405, ["message" => "Method Not Allowed"]);
	}

	public function logout(){
		$this->session->destroy();

		return $this->_renderJson(401 ,["message" => "Good Bye"]);
	}

	/**
	 * Connexion API : retourne un JWT.
	 * Corps JSON : login, password, type_cnx (NORM | DELTA).
	 */
	public function login(){
		if ($early = $this->cors(['POST','OPTIONS'])) return $early;

		$input = $this->request->getJSON();

		$data = [];
		$data['login']    = $input->login ?? '';
		$data['password'] = $input->password ?? '';
		$data['api-key']  = $input->{'api-key'} ?? '';
		$data['type_cnx'] = $input->type_cnx ?? 'NORM';

		$usercheck = service('auth')->Login($data);
		if (empty($usercheck->autorize)){
			return $this->_renderJson(403,["message" => "Forbiden"]);
		}

		return $this->_renderJson(200, array(
			"message" => "Successful login.",
			"jwt" => $usercheck->token,
			"id" => $usercheck->id,
			"role_id" => $usercheck->role_id,
			"type" => $usercheck->type,
			"expireAt" => $usercheck->expireAt,
			"expireAtRender" => date('Y-m-d H:i:s', $usercheck->expireAt)
		));
	}

	/**
	 * Simple GET METHOD JSON output
	 *
	 * @param mixed $_model_name
	 * @param mixed $id
	 */
	private function _getObject($_model_name = null ,$id = null){
		$this->_model_name = $_model_name;
		$this->LoadModel($this->_model_name);
		$this->render_object->_set('_render_model','json');
		if ($id){
			$this->{$this->_model_name}->_set('key_value',$id);
			$dba_data = $this->{$this->_model_name}->get_one();
			if (!$dba_data){
				return $this->_renderJson(204, ['message'=>'Not Found']);
			}

			return $this->_renderJson(200, $this->_set_render($dba_data));
		}

		$datas = $this->{$this->_model_name}->get_all();
		if (!count($datas))
			return $this->_renderJson(204, ['message'=>'Not Found']);

		$resp = [];
		foreach($datas AS $key=>$data){
			$resp[$key] = $this->_set_render($data);
		}

		return $this->_renderJson(200, $resp);
	}

	function _set_render($data){
		$res = [];
		foreach($data AS $field=>$value){
			$obj = new \stdClass();
			$obj->raw = $value;
			$obj->render = $this->render_object->RenderElement($field,$value,$data->id, $this->_model_name );
			$res[$field] = $obj;
		}
		return $res;
	}
}
