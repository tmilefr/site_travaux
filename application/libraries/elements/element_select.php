<?php
/*
 * element_select.php
 * SELECT Object in page
 * 
 */

/** @package  */
class element_select extends element
{
	public function RenderFormElement(){
		if ($this->disabled)
			$txt = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"><input class="form-control" type="text" value="'.$this->Render().'" readonly>';
		else
			$txt = $this->RenderTools->input_select($this->name, $this->values, $this->value);
		return $txt;
	}
	
	public function Render(){
		if (isset($this->values[$this->value]))
			return $this->values[$this->value];
		else
			return $this->value;		
	}
}

