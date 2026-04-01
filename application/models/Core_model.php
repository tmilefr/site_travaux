<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Core_model extends CI_Model {
	
	protected $table; 	//table used in model
	protected $key; 	//id used in model
	protected $key_value; 	
	protected $order	= []; 	//sort used in model
	protected $direction;//direction used in model
	protected $autorized_fields = array();
	protected $autorized_fields_search = array();
	protected $required = array();
	protected $datas = array(); // datas in model
	protected $filter = array();//filter for model
	protected $group_by = array(); //group by for model
	protected $per_page = 20;
	protected $_debug = FALSE;
	protected $page = 1;
	protected $nb = null;
	protected $_debug_array = array();
	protected $like = array();
	protected $global_search = null;
	protected $defs = array();	
	protected $json = null;
	protected $json_path = APPPATH.'models/json/';
	protected $_mode = 'classic'; // classic or join
	protected $_model_name = '';
    /**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		if (!$this->page){
			$this->page = 1;
		}
	}

	/**
	 * @brief 
	 * @param $opt (std class) 
	 * $opt->id 
	 * $opt->value 
	 * $opt->filter_field 
	 * $opt->filter_value 
	 * @returns 
	 * 
	 * 
	 */
	public function distinct($opt){
		try{
		//	$table,$id,$value,$filter_field=null, $filter_value = null;
			//echo debug($opt);
			if (strpos($opt->value,'@')){
				$fields = 'CONCAT_WS(" ",'.str_replace('@',',',$opt->value).') AS ';
				$as = ' '.str_replace('@','_',$opt->value);
			} else {
				$fields = $opt->value;
				$as = $opt->value;
			}
			$this->db->distinct();
			if ( isset($opt->filter_field) &&  isset($opt->filter_value) ){
				$datas = $this->db->select("$opt->table.$opt->id,$fields $as")->where($opt->filter_field,$opt->filter_value)->order_by("$as", 'asc' )->get($opt->table)->result();
			} else {
				$datas = $this->db->select("$opt->table.$opt->id,$fields $as")->order_by("$as", 'asc' )->get($opt->table)->result();
			}
			//echo $this->db->last_query().'<BR/>';
			$this->_debug_array[] = $this->db->last_query();

			return $datas;
		} catch (Exception $e) {
			//echo 'Exception reçue : ',  $e->getMessage(), "\n";
		}
	}	
		
	/**
	 * Method DeleteLink
	 *
	 * @param $foreign_key $foreign_key [explicite description]
	 * @param $id $id [explicite description]
	 *
	 * @return void
	 */
	function DeleteLink($foreign_key, $id = null){
		if ($id){
			$this->db->where_in($foreign_key, $id)
				 ->delete($this->table); 
			$this->_debug_array[] = $this->db->last_query();

			/*if ($this->table == "capacitys"){
				echo debug($this->db->last_query());
				die();
			}*/
		}
		
	}
	
	/**
	 * Method SetLink
	 *
	 * @param $foreign_key $foreign_key [explicite description]
	 * @param $id $id [explicite description]
	 *
	 * @return void
	 */
	function SetLink($foreign_key, $id = null){
		if ($id){
			$this->db->set($foreign_key, $id);
			$this->db->where($foreign_key, 99999);
			$this->db->update($this->table);	
			$this->_debug_array[] = $this->db->last_query();
		}
	}

	/**
	 * @brief 
	 * @param $opt (std class) 
	 * $opt->id 
	 * $opt->value 
	 * $opt->filter_field 
	 * $opt->filter_value 
	 * @returns 
	 * 
	 * 
	 */
	public function query($sql){
		try{
			$datas = $this->db->query($sql)->result();
			//echo $this->db->last_query().'<BR/>';
			$this->_debug_array[] = $this->db->last_query();

			return $datas;
		} catch (Exception $e) {
			//echo 'Exception reçue : ',  $e->getMessage(), "\n";
		}
	}
	
	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function _init_def(){
		$this->defs = array();
		$json = file_get_contents($this->json_path.$this->json);
		$json = json_decode($json);
		foreach($json AS $field => $defs){
			$this->autorized_fields[]  = $field;
			if ($defs->search){
				$this->autorized_fields_search[] = $field;
			}
			if ($defs->rules){
				$this->required[] = $field;
			}
			//FIELD OBJECT ELEMENT
			$fileobject = APPPATH.'libraries/elements/element_'.$defs->type.'.php';
			if (is_file($fileobject)){
				require_once($fileobject);
				$object_name = 'element_'.$defs->type;
			} else {
				require_once(APPPATH.'libraries/elements/element.php');
				$object_name = 'element';
			}
			$obj = new $object_name;
			foreach($defs AS $key => $value){
				$obj->_set($key , $value);
			}			
			if ($obj->_get('param')){
				$op_mg = $obj->SetParams();
				//echo debug($op_mg);

				if (isset($op_mg->method) && method_exists($this,$op_mg->method)){
					//echo debug($op_mg);
					$datas_select = [];
					$datas = $this->{$op_mg->method}($op_mg);
					if (count($datas)){
						foreach($datas AS $data){
							$datas_select[$data->{$op_mg->key}] = $data->{$op_mg->data};
						}
					}
					//echo debug($datas_select);
					$obj->_set('values', $datas_select);
				}
			} else {
				//$obj->_set('values', $defs->values);	
				if (method_exists($obj,'SetValues')){//new methode for set datas
					$obj->SetValues();
				}
			}
			$obj->_set('_model_name', $this->_get('_model_name'));
			$obj->_set('name', $field);
			$this->defs[$field] = $obj;
		}
	}
	
	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function truncate(){
		$this->db->truncate($this->table);	
	}	
	
	/**
	 * @brief 
	 * @param $field 
	 * @param $value 
	 * @param $fields 
	 * @returns 
	 * 
	 * 
	 */
	public function is_exist($field = 'id' ,$value = null, $fields = null){
		$query = $this->db->get_where($this->table , (($fields) ? $fields:array($field => $value)) );
		$this->_debug_array[] = $this->db->last_query();
		
		if ($query->num_rows())
			return $query->row();
		else
			return false;			
	}	

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function get_all(){
		if (is_array($this->filter) AND count($this->filter)){
			foreach($this->filter AS $key => $value){
				$this->db->where($key , $value);
			}
		} 	
		if ($this->global_search){
			foreach($this->autorized_fields_search AS $key => $value){
				$this->db->or_like($value , $this->global_search);
			}
		} 			
		$datas = $this->db->select(implode(',',$this->autorized_fields))
					   ->order_by($this->order, $this->direction )
					   ->get($this->table)
					   ->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}
	
	/**
	 * @brief 
	 * @param $field 
	 * @returns 
	 * 
	 * 
	 */
	public function get_distinct($field){
		$this->db->distinct();
		$datas = $this->db->select($field)->get($this->table)->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}		
	
	/* only one ? really ? */
	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function get_one()
	{
		$this->db->select('*')
				 ->from($this->table)
				 ->where($this->key, $this->key_value);
		$datas = $this->db->get()->row();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	/**
	 * @brief 
	 * @param $datas 
	 * @returns 
	 * 
	 * 
	 */
	public function post($datas)
	{
		/*foreach ($datas AS $key=>$fields){
			if (!in_array($field, $this->$this->autorized_fields)){
				unset($this->datas[$key]);
			}
		}*/
		$this->db->insert($this->table, $datas);
		$this->_debug_array[] = $this->db->last_query();
		return $this->db->insert_id();
	}

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	function _set_filter(){
		if (is_array($this->filter) AND count($this->filter)){
			//echo debug($this->filter);
			$this->db->group_start();
			foreach($this->filter AS $key => $value){
				$this->db->where($key , $value);
			}
			$this->db->group_end();
		} 
	}
	
	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	function _set_group_by(){
		if (is_array($this->group_by) AND count($this->group_by)){
			foreach($this->group_by AS $key => $value){
				$this->db->group_by($value);
			}
		} 	
	}

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	function _set_order_by(){
		if (is_array($this->order) AND count($this->order)){
			foreach($this->order AS $key => $value){
				$this->db->order_by($key, $value);
			}
		} 	
	}	

	function _setField($field){
		$def = $this->_get('defs')[$field];
		if (isset($def->table[$field])){
			$this->_mode = 'join';
			$this->db->join($def->table[$field],$this->table.'.'.$field.'='.$def->table[$field].'.'.$def->foreignKey[$field], 'left' );
			return $def->table[$field].'.'.$def->foreignField[$field];
		} else {
			if ($this->_mode == 'join'){
				return $this->table.'.'.$field.'';
			} else {
				return $field;
			}
		}		
	}
	

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	function _set_search(){
		if ($this->global_search){
			$this->db->group_start();
			foreach($this->autorized_fields_search AS $key => $value){
				if (!$key AND is_array($this->filter) AND count($this->filter)){
					$this->db->like( $this->_setField($value) , $this->global_search);
				} else {
					$this->db->or_like( $this->_setField($value)  , $this->global_search);					
				}
			}
			$this->db->group_end();
		} 	
	}


	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function get_pagination(){
		if (!$this->nb){
			$this->_set_filter();
			$this->_set_search();
			$this->nb = $this->db->select( $this->table.'.'.$this->key )->get($this->table)->num_rows();
		} 
		$this->_debug_array[] = 'get_pagination : '. $this->nb; 
		return $this->nb;
	}	
	

	function _set_list_fields(){
		$string_field = '';
		if ($this->autorized_fields){
			foreach($this->autorized_fields AS $field ){
				if ($this->_mode == 'join'){
					$string_field .= $this->table.'.'.$field.',';
				} else {
					$string_field .= $field.',';
				}
			}
			$string_field .= substr($string_field,-1);
		} else {
			$string_field = '*';
		} 
		return $string_field;
	}

    /**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function get(){
		$this->_set_filter();
		$this->_set_search();		  		
		if ($this->per_page  ){
			if (!$this->page)
				$this->page = 1 ;
			$this->db->limit(intval($this->per_page), ($this->page - 1 ) * $this->per_page);
		}
        $datas = $this->db->select( $this->_set_list_fields()  )
                           ->order_by($this->order, $this->direction )
                           ->get($this->table);
		$this->_debug_array[] = $this->db->last_query();
		return $datas->result();
    }

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function put($id = null)
	{
		foreach ($this->datas AS $field=>$data){
			if (!in_array($field, $this->autorized_fields)){
				unset($this->datas[$field]);
			}
		}
		if ($id){
			$this->key_value = $id;
		}
		$this->db->where($this->key, $this->key_value);
		$this->db->update($this->table, $this->datas);		
		$this->_debug_array[] = $this->db->last_query();
	}

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function delete()
	{
		$this->db->where_in($this->key, $this->key_value)
				 ->delete($this->table);
		$this->_debug_array[] = $this->db->last_query();
	}

	/**
	 * @brief 
	 * @param $field 
	 * @param $value 
	 * @returns 
	 * 
	 * 
	 */
	public function _set($field,$value){
		$this->$field = $value;
		//initialisation de l'id parent des objets
		switch($field){
			case 'key_value':
				foreach($this->defs AS $obj){
					$obj->_set('parent_id',$value);
				}
			break;
		}

	}

	/**
	 * @brief 
	 * @param $field 
	 * @returns 
	 * 
	 * 
	 */
	public function _get($field){
		return $this->$field;
	}
	
	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function __destruct(){
		if ($this->_debug){
			echo debug($this->_debug_array, __file__);
			//$this->CI->bootstrap_tools->render_debug($this->_debug_array);
		/*	foreach($this->_debug_array AS $msg)
				log_message('debug', debug($msg, get_class($this)));			*/
		}

	}	

}

/* End of file Core_model.php */
/* Location: ./application/models/Core_model.php */