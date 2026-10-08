<?php

namespace App\Libraries\Elements;
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
		// Dates ISO (AAAA-MM-JJ[ hh:mm:ss]) affichées JJ/MM/AAAA ; autres formats inchangés
		$value = (string) $this->value;
		if ($value === '' || str_starts_with($value, '0000-00-00')) {
			return '';
		}
		if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
			return \CodeIgniter\I18n\Time::createFromFormat('Y-m-d', substr($value, 0, 10))->format('d/m/Y');
		}

		return $value;
	}
}

