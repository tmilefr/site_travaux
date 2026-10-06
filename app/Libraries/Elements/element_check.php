<?php

namespace App\Libraries\Elements;
/*
 * element.php
 * Object in page
 * 
 */

class element_check extends element
{
	protected $mode; //view, form.
	protected $name   	= null; //unique id ?
	protected $value  	= null;
	protected $values 	= [];
	protected $type 	= '';
	

	private function RenderElement($value,$name){
		$val = '';
		if (isset($value->{$name})){
			$val =  preg_replace("/\s+/", " ", $value->{$name});
		}
		return '<div class="input-group mb-3">
		<div class="input-group-prepend">
			<span class="input-group-text" id="basic-addon1">'.tr($name).'</span>
		</div>
		'.$this->RenderTools->input_select($this->name.'_'.$name, $this->values, $val).'
		</div>';
	}

	public function PrepareForDBA($value){
		$obj = new \stdClass();
		$obj->encaisse = $this->post($this->name.'_encaisse');
		$obj->todo = $this->post($this->name.'_todo');
		$obj->have = $this->post($this->name.'_have');

		return json_encode($obj);
	}

	public function RenderFormElement(){

		$value = json_decode($this->value);
		//echo '<pre>'.print_r($value,true).'</pre>';

		$input = $this->RenderElement($value,'encaisse');
		$input .= $this->RenderElement($value,'todo');
		$input .= $this->RenderElement($value,'have');
		return $input;
	}
	
	public function Render(){
		$value= json_decode($this->value);
		return 'encaisse cette année :'.((isset($value->encaisse)) ? $value->encaisse: 'N/A').'<br/> à faire : '.((isset($value->todo)) ? $value->todo: 'N/A').'<br/> en notre possession : '.((isset($value->have)) ? $value->have: 'N/A');
	}

	/**
	 * Constructor of class element.
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Destructor of class element.
	 * @return void
	 */
	public function __destruct()
	{
		//echo '<pre><code>'.print_r($this , 1).'</code></pre>';
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

