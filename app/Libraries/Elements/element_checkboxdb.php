<?php

namespace App\Libraries\Elements;

use StdClass;
/*
 * element_checkboxdb.php
 * CHECKBOX Object in page
 * 
 */

class element_checkboxdb extends element
{	

	protected $mode; //view, form.
	protected $name   	= null; //unique id ?
	protected $value  	= NULL;
	protected $values 	= [];
	protected $model	= '';
	protected $foreignkey = '';
    protected $ref = '';


	public function __construct(){
		parent::__construct();
        $this->_loadModel();
	}

    public function __destruct()
    {
    }

    function _getInBase(){
        $this->_loadModel();
        $values = [];
        $id = $this->render_object->_get('id');
        if ($id){
			$this->mdl()->_set('filter', [$this->foreignkey => $id ]);
			$this->mdl()->_set('order', $this->foreignkey);
			$dba_data = $this->mdl()->get_all();
            foreach($dba_data AS $key=>$obj){
                $values[] = $obj->{$this->ref};
            }
        }
        return $values;
    }


	public function RenderFormElement(){
        $values = $this->_getInBase();
        $element = ''; 
        if (count($this->values)){
            foreach($this->values AS $key=>$value){
                $element .= '<div class="form-check">
                                <input class="form-check-input" type="checkbox" name="'.$this->name.'[]" id="'.$this->name.$key.'" value="'.$key.'" '.((in_array($key, $values)) ? "checked":"").'>
                                <label class="form-check-label" for="'.$this->name.$key.'">
                                '.$value.'
                                </label>
                            </div>';
                //$this->RenderTools->input_checkbox($this->name, $value);
            }
        }
		return $element;
	}

    private function _loadModel(){
        // Le modèle est instancié à la demande par mdl()
    }
	
	public function PrepareForDBA($value){
        $this->_loadModel();
        $src_post = json_encode($value);
        $id = $this->render_object->_get('id');
        $this->mdl()->DeleteLink($this->foreignkey, $id);
        foreach($value AS $key=>$value){
            $obj = new \stdClass();
            $obj->{$this->foreignkey} = $id;
            $obj->{$this->ref} = $value;
            $obj->created = date('Y-m-d H:i:s');
            $this->mdl()->post($obj);
        }
		return $src_post;
	}

	public function Render(){
        $values = $this->_getInBase();
        $tmp  = '';
        if (is_array($values))
        foreach($values AS $val){
            $tmp .= $this->values[$val].' - ';
        }

		return (($this->value) ? substr($tmp,0,-3):LANG($this->name.'_NO'));
	}

    public function AfterExec($datas){
        $this->_loadModel();
        if ($this->render_object->_get('form_mod') != 'edit')
		    $this->mdl()->SetLink($this->foreignkey, $datas['id']);
	}

}

