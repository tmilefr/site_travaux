<?php

namespace App\Controllers;

use StdClass;

/**
 * @brief
 *
 *
 */
class Parameters extends CrudController {

	protected $params = []; //list of parameters
	/**
	 * @brief
	 * @returns
	 *
	 *
	 */
	protected function boot(): void
	{
		$this->_model_name 		= 'Parameters_model';	   //DataModel
		$this->_controller_name = 'Parameters';  //controller name for routing
		$this->init();

		 //['app_name','slogan','debug_app','protocol','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_crypto','charset','mailtype','wordwrap','newline','crlf'];

	}

	/** Champ du formulaire => [classe de config, propriété] */
	protected $config_map = [
		'app_name'    => ['Travaux', 'appName'],
		'slogan'      => ['Travaux', 'slogan'],
		'debug_app'   => ['Travaux', 'debugApp'],
		'protocol'    => ['Email', 'protocol'],
		'smtp_host'   => ['Email', 'SMTPHost'],
		'smtp_port'   => ['Email', 'SMTPPort'],
		'smtp_user'   => ['Email', 'SMTPUser'],
		'smtp_pass'   => ['Email', 'SMTPPass'],
		'smtp_crypto' => ['Email', 'SMTPCrypto'],
		'charset'     => ['Email', 'charset'],
		'mailtype'    => ['Email', 'mailType'],
		'wordwrap'    => ['Email', 'wordWrap'],
		'newline'     => ['Email', 'newline'],
		'crlf'        => ['Email', 'CRLF'],
	];

	/**
	 * Affiche (et modifie pour la requête en cours) les paramètres de configuration.
	 */
	public function list(){
		$fields = $this->Parameters_model->_get('autorized_fields');
		$dba_data = new \stdClass();
		foreach($fields AS $field){
			[$class, $prop] = $this->config_map[$field] ?? [null, null];
			$cfg = $class ? config($class) : null;
			if ($cfg && ($item_value = $this->request->getPost($field))){
				$cfg->{$prop} = $item_value;
			}

			$dba_data->{$field} = $cfg ? $cfg->{$prop} : null;
		}
		$this->render_object->_set('dba_data', $dba_data);

		$this->_set('view_inprogress','edition/Parameters_form');
		$this->render_view();
	}
}
