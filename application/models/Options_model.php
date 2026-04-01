<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Options_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'options');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'filter');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Options.json');
	}

	function GetOpt($type){
		$options = [];
		$this->_set('filter',['filter'=>$type]);
		$opts = $this->get_all();
		if($opts){
			foreach($opts AS $opt){
				$options[$opt->cle] = $opt;
			}
		}
		return $options;
	}


}
?>