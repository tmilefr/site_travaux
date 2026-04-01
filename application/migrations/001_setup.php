<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_setup extends CI_Migration {

		/* Need to have
		
		CREATE TABLE IF NOT EXISTS `ci_sessions` (
			`id` varchar(128) NOT NULL,
			`ip_address` varchar(45) NOT NULL,
			`timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
			`data` blob NOT NULL,
			KEY `ci_sessions_timestamp` (`timestamp`)
		);

		ALTER TABLE ci_sessions ADD PRIMARY KEY (id, ip_address);
		*/
        
		protected $json = null;
		protected $json_path = APPPATH.'models/json/';

		public function __construct(){
			parent::__construct();
		}

		public function _get_json($json){
			$json = file_get_contents($this->json_path.$json);
			$json = json_decode($json);
			$fields = array();
			foreach($json AS $field => $define){
				$def = array();
				foreach($define->dbforge AS $key=>$value){
					$def[$key] = $value;
				}
				$fields[$field] = $def;
			}
			return $fields;
		}

		public function LoadData($json,$model,$path){
			if (file_exists($this->json_path.$json)){
				$this->load->model($model);
				$json = file_get_contents($this->json_path.$json);
				$json = json_decode($json);
				foreach($json->{$path} AS $element){
					$this->{$model}->post($element);
				}
			}
		}	        
        
        public function up()
        {		
			$this->Make('Subscription');
			$this->Make('Service');
			$this->Make('Taux');
			$this->Make('Users');
			$this->Make('Contribution');
	    }

		public function Make($name){
			$this->dbforge->drop_table( $name , TRUE);
			$this->dbforge->add_field( $this->_get_json($name.'.json') );
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table($name);    
			/* Feeders  */
			$this->LoadData($name.'_data.json',$name.'_model',$name);	
		}

        public function down()
        {
			$this->dbforge->drop_table('users'  , TRUE);
			$this->dbforge->drop_table('subscription' , TRUE);
			$this->dbforge->drop_table('family' , TRUE);
			
        }
}

?>
