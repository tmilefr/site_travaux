<?php
/*
 * element_month.php
 * element_month Object in page
 * 
 */

class element_month extends element
{	
	public function RenderFormElement(){
		return $this->RenderTools->input_select($this->name, $this->values, $this->value);
	}
	
	public function Render(){
		$return = '';
		if (strpos($this->value, ',')){
			$values = explode(',',$this->value);
			foreach($values AS $key=>$value){
				$values[$key] = $this->_get_value($value);
			}
			return implode(' - ', $values);				
		} else {
			$return = $this->_get_value($this->value);
		}
		return $return;	
	}
	
	public function _get_value($value){
		if (isset($this->values[$value]))
			return $this->values[$value];
		else
			return $value;		
	}
	
}

