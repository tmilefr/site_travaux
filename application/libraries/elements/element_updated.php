<?php
/*
 * element_updated.php
 * created Date Object in page
 * 
 */

class element_updated extends element
{	
	protected $form_mod;
	public function RenderFormElement(){
		if ($this->form_mod == 'edit'){
			return form_hidden($this->name , date('Y-m-d h:i:s'));
		} else {
			return form_hidden($this->name , '');
		}
	}
	
	public function Render(){
		return ($this->value);
	}
}

