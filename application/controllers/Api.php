<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Api extends MY_Controller {

	public $dbforge;
	public $SQL;

	public function __construct(){
		parent::__construct();
		$this->_api = TRUE; //declaration du mode api sur ce controlleur, impact sur MY_Exceptions.php
	}
	
	/**
	 * Set response of API
	 * @param array $AllowMethods 
	 * @return void 
	 */
	private function _SetHeaders($AllowMethods = ['POST','GET','DELETE','PUT','PATCH','OPTIONS']){
		$notallowed = TRUE;
		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'OPTIONS': //need for js access
				if (in_array('OPTIONS', $AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Credentials: true');
					header('Access-Control-Allow-Methods: '.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: token, Content-Type');
					header('Access-Control-Max-Age: 1728000');
					header('Content-Length: 0');
					header('Content-Type: text/plain');
					die();
				} 
			break;
			case 'POST':
				if (in_array('POST',$AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods:'.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: Content-Type, Authorization');
					header('Access-Control-Allow-Credentials: true');
					header("Content-Type: application/json");
					$notallowed = FALSE;
				}				
			break;
			case 'GET':
				if (in_array('GET',$AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods:'.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: Content-Type, Authorization');
					header('Access-Control-Allow-Credentials: true');
					header("Content-Type: application/json");
					$notallowed = FALSE;
				}				
			break;
			case 'PUT':
				if (in_array('PUT',$AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods:'.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: Content-Type, Authorization');
					header('Access-Control-Allow-Credentials: true');
					header("Content-Type: application/json");
					$notallowed = FALSE;
				}				
			break;
			case 'PATCH':
				if (in_array('PATCH',$AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods:'.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: Content-Type, Authorization');
					header('Access-Control-Allow-Credentials: true');
					header("Content-Type: application/json");
					$notallowed = FALSE;
				}				
			break;	
			case 'DELETE':
				if (in_array('DELETE',$AllowMethods )){
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods:'.implode(',',$AllowMethods));
					header('Access-Control-Allow-Headers: Content-Type, Authorization');
					header('Access-Control-Allow-Credentials: true');
					header("Content-Type: application/json");
					$notallowed = FALSE;
				}				
			break;												
		}
		if ($notallowed ){
			$this->_renderJson(405,["message" => "Method Not Allowed"]);
		}

	}

	/**
	 * Entry Point FOR Templates (exemple of 'correct' implement of API)
	 * Don't forget set rules
	 * And manage Roles 
	 * 
	 * @param mixed $id 
	 * @return void 
	 */
	public function Templates($id = null ){
		$this->_SetHeaders(['GET','OPTIONS']);
		//ONLY GET
		$this->_getObject('Templates_model', $id);
	}


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
		//ONLY GET
		$this->_getObject('Familys_model', $id);
	}


	public function GetTables(){
		$this->_SetHeaders(['GET','OPTIONS']);
		$working_dir = APPPATH.'models\\';
		$models = scandir($working_dir);

		$models = array_diff($models , array('..', '.', 'json','index.html','Core_model.php','GenericSql_model.php'));

		$this->_renderJson(200, $models);
	}

	/**
	 * Entry Point FOR Familys (exemple of 'correct' implement of API)
	 * Don't forget set rules @Acl_controllers_controller/edit/14 [14 = id of api controller]
	 * And manage Roles @Acl_roles_controller/set_rules/1 [1 = id of admin role]
	 * 
	 * @param mixed $id 
	 * @return void 
	 */
	public function SetTable($model_name = 'Sendmail_model'){
		$this->_SetHeaders(['GET','OPTIONS']);
		$this->load->dbforge();
		$this->load->model('GenericSql_model','SQL');
		$this->_model_name = $model_name;
		$this->load->model($this->_model_name);
		// Préparation pour DB forge => TODO : extend db forge ?
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

		$sql = $this->SQL->exec("SHOW FIELDS FROM ".$this->{$this->_model_name}->_get('table').";");
		if (!is_object($sql)){
			$error = get_instance()->db->error();
			switch($error['code']){
				case 1146 : //création de la table puis création des champs
					$attributes = array('ENGINE' => 'InnoDB');
					$this->dbforge->add_field($forge);
					foreach($forge AS $field=>$defs){
						if (isset($defs['auto_increment']) && $defs['auto_increment'] == true){
							$this->dbforge->add_key($field, TRUE);
						}
					}
					$this->dbforge->create_table($this->{$this->_model_name}->_get('table'), FALSE,  $attributes);
				break;
				default:
					$this->_renderJson(500, $error);
					die();
			}
		} else { //juste mise à jour des champs au besoin
			$this->dbforge->modify_column($this->{$this->_model_name}->_get('table'), $forge);
		}
		
		if (!count($forge))
			$this->_renderJson(204, ['message'=>'Not Found']);



		$this->_renderJson(200, $forge);
		
	}

	
	/**
	 * WS de soumission d'e-mail
	 * Un pool d'envois en cron met à jour le statut de celui-ci 
	 * @param mixed $id 
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function mails($id = null){
		$this->_SetHeaders(['POST','PUT','GET','DELETE','OPTIONS']);
		
		$this->LoadModel('Sendmail_statut_model');
		$this->LoadModel('Sendmail_model');
		$this->render_object->_set('_render_model','json');

		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'PUT':
				//TODO : block PUT if STATUS IS SENDED nL 26/06/2023 
				$input = json_decode(file_get_contents("php://input"));
				//dans les données plutot que sur le path
				if (!$id  && isset($input->id))
					$id = $input->id;

				if ($id){
					
					//STD CLass to ARRAY;
					$sendmail = [];
					foreach($input AS $field=>$value){
						$sendmail[$field] = $value;
					}
					//validation des données
					$this->form_validation->set_data($sendmail);

					if ($this->form_validation->run($this->_model_name) === FALSE){
						echo $this->_renderJson(400, validation_errors('{','}'));
						die();
					} else {
						$sendmail['updated'] = date('Y-m-d h:i:s');
						$this->{$this->_model_name}->_set('key_value', $id);	
						$this->{$this->_model_name}->_set('datas', $sendmail);
						$this->{$this->_model_name}->put();

						$this->_renderJson(202 ,["id" => $id,'last_query'=>$this->{$this->_model_name}->_get('_debug_array')]);
					}
				} else {
					$this->_renderJson(400, ['message'=>'id est requis ']);
					die();
				}
			break;			
			case 'POST':
				$input = json_decode(file_get_contents("php://input"));
				//STD CLass to ARRAY;
				$sendmail = [];
				foreach($input AS $field=>$value){
					$sendmail[$field] = $value;
				}
				//validation des données
				$this->form_validation->set_data($sendmail);

				if ($this->form_validation->run($this->_model_name) === FALSE){
					$this->_renderJson(400, validation_errors('{','}'));
					die();
				} else {
					$sendmail['created'] = date('Y-m-d h:i:s');
					$id = $this->{$this->_model_name}->post($sendmail);
					/* Init E-mail Statut  */
					$statut = [];
					$statut['id_sen'] = $id;
					$statut['date'] = date('Y-m-d H:i:s');
					$statut['statut'] = 0; //nouveau
					$statut['created'] = date('Y-m-d h:i:s');
					$id = $this->Sendmail_statut_model->post($statut);

					$this->_renderJson(201 ,["id" => $id]);
				}
			break;
			case 'GET':
				//version standard de l'exposition
				$this->_getObject($this->_model_name, $id);
			break;
			case 'DELETE':
				$input = json_decode(file_get_contents("php://input"));
				//dans les donnes plutot que sur le path
				if (!$id  && isset($input->id))
					$id = $input->id;
				if ($id){
					$this->{$this->_model_name}->_set('key_value',$id);
					$dba_data = $this->{$this->_model_name}->delete();
					$this->_renderJson(200, $dba_data );
				} else {
					$this->_renderJson(400, ['message'=>'id est requis ']);
					die();
				}
			break;
		}
	}
	

	public function logout(){
		session_destroy();
		$this->_renderJson(401 ,["message" => "Good Bye"]);
		die;
	}

	public function login(){
		$this->_SetHeaders(['POST','OPTIONS']);
		$input = json_decode(file_get_contents("php://input")); //TODO check codignter for input use instead

		$data = [];
		$data['login'] = $input->login;
		$data['password'] = $input->password;
		$data['api-key'] = $input->{'api-key'};
		$data['type_cnx'] = $input->{'type_cnx'};

		$usercheck = $this->acl->CheckLogin($data);
		if (!$usercheck || !$usercheck->autorize){
			$this->_renderJson(403,["message" => "Forbiden"]);
			die;
		}
		$data =	array(
			"message" => "Successful login.",
			"jwt" => $usercheck->token,
			"id" => $usercheck->id,
			"role_id" => $usercheck->role_id,
			"type" => $usercheck->type,
			"expireAt" => $usercheck->expireAt,
			"expireAtRender" => date('Y-m-d H:i:s', $usercheck->expireAt)
		);
		$this->_renderJson(200, $data);
	}
	
	/**
	 * Simple GET METHOD JS output
	 * @param mixed $_model_name 
	 * @param mixed $id 
	 * @return void 
	 * @throws RuntimeException 
	 */
	private function _getObject($_model_name = null ,$id = null){
		$this->_model_name = $_model_name;
		$this->LoadModel($this->_model_name);
		$this->render_object->_set('_render_model','json');
		header("Content-Type: application/json");
		if ($id){
			$this->{$this->_model_name}->_set('key_value',$id);
			$dba_data = $this->{$this->_model_name}->get_one();
			if (!$dba_data){
				$this->_renderJson(204, ['message'=>'Not Found']);
				die();
			}
			
			$this->_renderJson(200, $this->_set_render($dba_data));
		} else {
			$datas = $this->{$this->_model_name}->get_all();
			if (!count($datas))
				$this->_renderJson(204, ['message'=>'Not Found']);
			$response = new StdClass();
			$response->raw = $datas;
			$resp = [];
			foreach($datas AS $key=>$data){
				$resp[$key] = $this->_set_render($data);
			}
			$this->_renderJson(200, $resp);
		}
	}

	function _set_render($data){
		$res = [];
		foreach($data AS $field=>$value){
			$obj = new stdClass();
			$obj->raw = $value;
			$obj->render = $this->render_object->RenderElement($field,$value,$data->id, $this->_model_name );
			$res[$field] = $obj;
		}
		return $res;
	}

}

?>
