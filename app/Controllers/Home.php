<?php

namespace App\Controllers;

use StdClass;

class Home extends CrudController {

	protected function boot(): void
	{
		$this->_controller_name = 'Home';  //controller name for routing
		$this->_model_name 		= 'Acl_users_model';	   //DataModel
		$this->title .= tr($this->_controller_name);
		$this->data_view['content'] = '';
		$this->_set('_debug', FALSE);
		

		$this->LoadModel('Familys_model');
		$this->LoadModel('Email_model');
		$this->LoadModel('Members_model');

		$this->init();
	}

	public function maintenance(){
		$this->_set('view_inprogress','unique/Home_controller_maintenance');
		$this->render_view();
	}

	public function landing(){
		$this->_set('view_inprogress','unique/langing_page');
		$this->render_view();
	}

	public function login(){


		$captcha_error = '';
		$login_error = '';
		if ($this->request->is('post')){
			if (config('Travaux')->captcha){ //TODO : mettre ça dans l'ACL
				$this->{$this->_model_name}->_get('defs')['recaptchaResponse']->change_password = $this->request->getPost('g-recaptcha-response_check');
				$captcha = json_decode($this->{$this->_model_name}->_get('defs')['recaptchaResponse']->PrepareForDBA($this->request->getPost("g-recaptcha-response")));
				//echo '<pre>'.print_r($captcha, TRUE).'</pre>';
			} else {
				$captcha = new \stdClass();
				$captcha->success = true;
			}
			if (isset( $captcha->{'error-codes'}))
				$captcha_error = implode('<br/>', $captcha->{'error-codes'});

			if ($this->runValidation('Acl_users_model') == true AND isset($captcha) AND $captcha->success == true) {
				$data = $this->request->getPost();
				/* on force le login en admin */
				if ($data['login'] == 'admin' )
					$data['type_cnx'] = 'NORM';
				$login_error = $this->acl->CheckLogin($data);
				//$this->session->setFlashdata('login_error', $this->acl->CheckLogin($data));
				if ($this->acl->IsLog()){ 
					$this->goTo('/Home');
				}
			}	
        }
		//BUG d'appel à l'objet, compensation
		$this->{$this->_model_name}->_get('defs')['recaptchaResponse']->_set('captcha',  config('Travaux')->captcha );
		$this->data_view['required_field'] = $this->{$this->_model_name}->_get('required');
		$this->data_view['captcha_error'] = $captcha_error;
		$this->data_view['login_error'] = $login_error;
		$this->_set('view_inprogress','unique/login_view');
		$this->render_view();
	}

	public function About(){
		$this->_set('view_inprogress','unique/about');
		$this->render_view();
	}

	//gestion de mon compte 
	public function myaccount(){
		$this->LoadModel('Capacity_model');
		$this->LoadModel('AlertPref_model');  
		$this->bootstrap_tools->_SetHead('assets/css/account_form.css', 'css');
		
		//compte de type admin
		if ($this->acl->getType()  == "sys"){
			$this->goTo('Acl_users_controller/edit/'.$this->acl->getUserId());
		}
		$this->data_view['msg'] = '';
		$this->LoadModel('Familys_model'); //loading Infos_model ELements
		$this->_bg_color = 'nicdark_bg_orange';
		$id = $this->acl->getUserId();
		if ($id){
			$this->render_object->_set('id',		$id);
			$this->Familys_model->_set('key_value',$id);
			$dba_data = $this->Familys_model->get_one();
			$this->render_object->_set('dba_data',$dba_data);
			$this->render_object->_set('form_mod', 'edit');
			$this->data_view['id'] = $id;
		}		

		if ($this->runValidation('Familys_model') === FALSE){
			$this->_debug(implode(' ', service('validation')->getErrors()),'edit','form_validation',__FILE__,__LINE__);
		} else {

			//suppression du champ password pour éviter le double PrepareForDBA de l'objet password;
			$override_fields = $this->Familys_model->_get('autorized_fields');
			foreach($override_fields AS $key => $name){
				if ($name == "password")
					unset($override_fields[$key]);
			}
			$datas = $this->_ProcessPost('Familys_model',$override_fields);
			$this->data_view['msg'] = tr('SAVED_OK');
		}
		
		$this->data_view['required_field'] = $this->Familys_model->_get('required');
		$this->_set('view_inprogress','edition/Account_form.php');
		$routes = $this->session->get('routes');
		$this->data_view['routes_history'] = $routes;
		$this->render_view();
	}

	
	public function logout(){
		$this->session->destroy();
        $this->goTo('/Home/login');
	}

	public function index()
	{
		$this->_set('render_view', FALSE);
		/*$api = [
			'base_url'  => base_url('API/') , 
			'user_agent' => "php",
			'headers'=>[
				'Authorization' => 'Bearer '.$this->auth->_get('connected_user')->token
			]
		];
		$this->restclient->init($api);
		//Appel d'une api par la page, avec l'utilisateur connecté
		$result = $this->restclient->get('get/roles');
		if ($result->error)
			echo debug($result->error);
		
		echo debug(json_decode($result->response)); */

		$this->_set('view_inprogress','unique/home_page');
		$this->render_view();
	}

	public function no_right()
	{
		$this->_set('view_inprogress','unique/no_right');
		$routes = $this->session->get('routes');
		$this->data_view['routes_history'] = $routes;
		$this->render_view();
	}


}
