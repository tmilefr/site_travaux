<?php
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
        $this->CI =& get_instance();
        $this->_loadModel();
	}

    public function __destruct()
    {
        unset($this->CI);
    }

    function _getInBase(){
        $this->_loadModel();
        $values = [];
        $id = $this->CI->render_object->_get('id');
        if ($id){
			$this->CI->{$this->model}->_set('filter', [$this->foreignkey => $id ]);
			$this->CI->{$this->model}->_set('order', $this->foreignkey);
			$dba_data = $this->CI->{$this->model}->get_all();
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
                //$this->CI->bootstrap_tools->input_checkbox($this->name, $value);
            }
        }
		return $element;
	}

    private function _loadModel(){
        if (!isset($this->CI->{$this->model}))
            $this->CI->load->model($this->model);
    }
	
	public function PrepareForDBA($value){
        $this->_loadModel();
        $src_post = json_encode($value);
        $id = $this->CI->render_object->_get('id');
        $this->CI->{$this->model}->DeleteLink($this->foreignkey, $id);
        foreach($value AS $key=>$value){
            $obj = new StdClass();
            $obj->{$this->foreignkey} = $id;
            $obj->{$this->ref} = $value;
            $obj->created = date('Y-m-d H:i:s');
            $this->CI->{$this->model}->post($obj);
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
        if ($this->CI->render_object->_get('form_mod') != 'edit')
		    $this->CI->{$this->model}->SetLink($this->foreignkey, $datas['id']);
	}

}

