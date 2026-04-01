<?php
/*
 * element.php
 * Object in page
 * 
 */

class element_memo extends element
{
	protected $mode; //view, form.
	protected $name   	= null; //unique id ?
	protected $value  	= null;
	protected $values 	= [];
	protected $type 	= '';
	protected $rows 	= 10;
	protected $required 	= false;
	protected $disabled = false;
	protected $param; //view, form.
	
	public function RenderFormElement(){
		if ($this->disabled)
			$txt = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"><input class="form-control" type="text" value="'.$this->Render().'" readonly>';
		else
			$txt = $this->RenderTools->textarea($this->name,  $this->value, Lang($this->name), $this->required, $this->rows);
		return $txt;
	}
	
	public function Render(){
		return nl2br($this->value);
	}

	/**
	 * Generic set
	 * @return void
	 */
	public function _set($field,$value){
		$this->$field = $value;
	}
	/**
	 * Generic get
	 * @return void
	 */
	public function _get($field){
		return $this->$field;
	}

}

