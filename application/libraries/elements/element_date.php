<?php
/*
 * element_date.php
 * Date Object in page
 * 
 */

class element_date extends element
{	
	
	public function __construct(){
		parent::__construct();
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/plugins/js/bootstrap-datepicker.js','js');
			$this->RenderTools->_SetHead('assets/plugins/js/locales/bootstrap-datepicker.fr.js','js');
			$this->RenderTools->_SetHead('assets/plugins/css/datepicker.css','css');		
		}
	}
	
	public function RenderFormElement(){
		return $this->RenderTools->input_date($this->name,$this->value,$this->datatarget);
	}
	
	public function Render(){
		return GetFormatDate($this->value);
	}
}

