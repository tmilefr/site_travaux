<?php
/*
 * element.php
 * Object in page
 * 
 */

class element_table extends element
{
	protected $mode; //view, form.
	protected $name   	= null; //unique id ?
	protected $value  	= NULL;
	protected $values 	= [];
	protected $type 	= '';
	protected $model	= '';
	protected $foreignkey = '';
	protected $action = '';
	protected $ref = '';
	protected $parent_id = '';

	public function __construct(){
		parent::__construct();
		$this->CI =& get_instance();
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/js/dynamic_row.js','js');
		}
		$this->_load_model();
	}	

	private function _load_model(){
		$this->CI->load->model($this->model);
	}

	public function AfterExec($datas){
		$this->CI->{$this->model}->SetLink($this->foreignkey, $datas['id']);
	}

	public function PrepareForDBA($value){
		//echo debug($_POST);
		//$this->CI->{$this->model}->_set('debug',TRUE);

		$id_parent = $this->CI->render_object->_get('id'); //PUSH data in object instead ?
		$obj = [];
		$datas = [];
		//return json_encode($obj);
		if (method_exists($this->CI->{$this->model},'DeleteLink'))
			$this->CI->{$this->model}->DeleteLink($this->foreignkey, $id_parent);

		foreach($this->CI->{$this->model}->_get('defs') AS $field=>$defs){
			$datas[$field] = $this->CI->input->post($field.'_'.$this->model);
		}	

		/*if ($this->model == 'Trombi_model'){
			echo debug($datas);
			echo debug($_POST);
	
			die();
		}*/

		foreach($datas[$this->ref] AS $key=>$value){
			if ($value != '...'){
				$lgn = new Stdclass();
				foreach($this->CI->{$this->model}->_get('defs') AS $field=>$defs){
					$lgn->{$field} = $datas[$field][$key];
				}
				if ($lgn->{$this->ref}){
					if ($id_parent){
						$lgn->{$this->foreignkey} = $id_parent;
					} else {
						$lgn->{$this->foreignkey} = 99999; //todo : find best way ?
					}					
					$this->CI->{$this->model}->post($lgn);
					$obj[] = $lgn->{$this->ref};
				}
			}
		}
		return json_encode($obj);
	}

	public function RenderFormElement(){
		//return $this->CI->bootstrap_tools->input_text($this->name, $this->CI->lang->line($this->name) , $this->value);
		$id = $this->CI->render_object->_get('id');
		$ref = [];
		$table = '<div class="Dynamic_row" id="DR_'.$this->name.'">';
		if ($id){
			$this->CI->{$this->model}->_set('filter', [$this->foreignkey => $id ]);
			$this->CI->{$this->model}->_set('order', $this->foreignkey);
			$datas = $this->CI->{$this->model}->get_all();
			if (count($datas)){
				foreach($datas AS $key => $data){
					$table .= '<div class="input-group mb-3">';
					foreach($this->CI->{$this->model}->_get('defs') AS $field=>$defs){
						//echo debug($this->CI->render_object->_get('form_mod'), __file__.' '.__line__);
						$defs->_set('form_mod', $this->CI->render_object->_get('form_mod'));
						$defs->_set('value', $data->{$field});
						$defs->_set('parent_id', $data->id);
						
						$defs->set_name('_'.$this->model);
						$defs->SetMultiple(TRUE);
						

						if (in_array( $field , ['id',$this->foreignkey])){							
							$table .= '<input type="hidden" value="'.$data->{$field}.'" name="'.$field.'_'.$this->model.'[]">';
						} else {
							$table .= $defs->RenderFormElement();
						}				
					}
					$table .= '<div class="input-group-append"><button id="removeRow'.$data->id.'" type="button" class="removeRow btn btn-danger">'.$this->CI->lang->line('RemoveRow').'</button></div></div>';
				}
			}
		}
		$table .= '<div class="d-none" id="model'.$this->name.'"><div class="input-group mb-3">';
		foreach($this->CI->{$this->model}->_get('defs') AS $field=>$defs){
			$defs->_set('value', '');
			$defs->set_name('_'.$this->model);
			$defs->SetMultiple(TRUE);
			$defs->_set('parent_id', 'new');

			if (in_array( $field , ['id',$this->foreignkey])){							
				$table .= '<input type="hidden" value="" name="'.$field.'_'.$this->model.'[]">';
			} else {
				$table .= $defs->RenderFormElement();
			}			
		}
		$table .= '<div class="input-group-append"><button id="removeRow" type="button" class="removeRow btn btn-danger">'.$this->CI->lang->line('RemoveRow').'</button></div></div></div>';
		$table .= '</div><button type="button" ref="'.$this->name.'" class="addRow btn btn-info">'.$this->CI->lang->line('AddRow').'</button> '.$this->CI->lang->line($this->name.'_AddRow').'';
		return form_hidden($this->name , $this->value ).$table;

	}

	//TODO : pilote render mode ( json, html, raw ...)	
	public function Render($format = false){
		$this->_load_model();
		$tmp = $this->value;
		if($this->parent_id){
			if (isset($this->CI->{$this->model})){
				$this->CI->{$this->model}->_set('filter', [$this->foreignkey => $this->parent_id ]);
				$this->CI->{$this->model}->_set('order', $this->foreignkey);
				$datas = $this->CI->{$this->model}->get_all();
				$dts = [];
				foreach($datas AS $data){
					$lgn = [];
					foreach($this->CI->{$this->model}->_get('defs') AS $field=>$defs){
						$obj = new StdClass();
						$obj->list = $defs->_get('list');
						$obj->raw = $data->{$field};
						$defs->_set('value', $data->{$field});
						$obj->render = $defs->render();
						$lgn[$field] = $obj;
					}
					$dts[] = $lgn;
				}
				switch($format ){
					case 'json':
						return $dts;
					break;
					case 'raw':
						foreach($dts AS $key=>$dt){
							$tmp ='';
							foreach($dt AS $field=>$obj){
								$tmp .= $obj->render.";";
							}
							$dts[$key] = $tmp;
						}
						return implode("\n", $dts);
					break;
					default:
						$tmp = '<table class="table">';
						foreach($dts AS $key=>$dt){
							$tmp .='<tr>';
							foreach($dt AS $field=>$obj){
								if ($obj->list == 1 && $field != 'id')
								$tmp .= '<td>'.$obj->render."</td>";
							}
							$tmp .= '</tr>';
						}
						return $tmp.'</table>';
					break;
				}	
			} else {
				return $this->model.' not instantiate';
			}
		}
		return $tmp;
		
	}

	/**
	 * Destructor of class element.
	 * @return void
	 */
    public function __destruct()
    {
        unset($this->CI);
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

