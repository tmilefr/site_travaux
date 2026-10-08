<?php

namespace App\Libraries\Elements;

use StdClass;
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
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/js/dynamic_row.js','js');
		}
	}	

	public function AfterExec($datas){
		$this->mdl()->SetLink($this->foreignkey, $datas['id']);
	}

	public function PrepareForDBA($value){
		//d($this->post(null));
		//$this->mdl()->_set('debug',TRUE);

		$id_parent = $this->render_object->_get('id'); //PUSH data in object instead ?
		$obj = [];
		$datas = [];
		//return json_encode($obj);
		if (method_exists($this->mdl(),'DeleteLink'))
			$this->mdl()->DeleteLink($this->foreignkey, $id_parent);

		foreach($this->mdl()->_get('defs') AS $field=>$defs){
			$datas[$field] = $this->post($field.'_'.$this->model);
		}	

		/*if ($this->model == 'Trombi_model'){
			d($datas);
			d($this->post(null));
	
			die();
		}*/

		foreach($datas[$this->ref] AS $key=>$value){
			if ($value != '...'){
				$lgn = new \stdClass();
				foreach($this->mdl()->_get('defs') AS $field=>$defs){
					$lgn->{$field} = $datas[$field][$key];
				}
				if ($lgn->{$this->ref}){
					if ($id_parent){
						$lgn->{$this->foreignkey} = $id_parent;
					} else {
						$lgn->{$this->foreignkey} = 99999; //todo : find best way ?
					}					
					$this->mdl()->post($lgn);
					$obj[] = $lgn->{$this->ref};
				}
			}
		}
		return json_encode($obj);
	}

	public function RenderFormElement(){
		//return $this->RenderTools->input_text($this->name, tr($this->name) , $this->value);
		$id = $this->render_object->_get('id');
		$ref = [];
		$table = '<div class="Dynamic_row" id="DR_'.$this->name.'">';
		if ($id){
			$this->mdl()->_set('filter', [$this->foreignkey => $id ]);
			$this->mdl()->_set('order', $this->foreignkey);
			$datas = $this->mdl()->get_all();
			if (count($datas)){
				foreach($datas AS $key => $data){
					$table .= '<div class="input-group mb-3">';
					foreach($this->mdl()->_get('defs') AS $field=>$defs){
						//echo debug($this->render_object->_get('form_mod'), __file__.' '.__line__);
						$defs->_set('form_mod', $this->render_object->_get('form_mod'));
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
					$table .= '<div class="input-group-append"><button id="removeRow'.$data->id.'" type="button" class="removeRow btn btn-danger">'.tr('RemoveRow').'</button></div></div>';
				}
			}
		}
		$table .= '<div class="d-none" id="model'.$this->name.'"><div class="input-group mb-3">';
		foreach($this->mdl()->_get('defs') AS $field=>$defs){
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
		$table .= '<div class="input-group-append"><button id="removeRow" type="button" class="removeRow btn btn-danger">'.tr('RemoveRow').'</button></div></div></div>';
		$table .= '</div><button type="button" ref="'.$this->name.'" class="addRow btn btn-info">'.tr('AddRow').'</button> '.tr($this->name.'_AddRow').'';
		return form_hidden($this->name, (string) $this->value).$table;

	}

	//TODO : pilote render mode ( json, html, raw ...)	
	public function Render($format = false){
		$tmp = $this->value;
		if($this->parent_id){
			if ($this->model){
				$this->mdl()->_set('filter', [$this->foreignkey => $this->parent_id ]);
				$this->mdl()->_set('order', $this->foreignkey);
				$datas = $this->mdl()->get_all();
				$dts = [];
				foreach($datas AS $data){
					$lgn = [];
					foreach($this->mdl()->_get('defs') AS $field=>$defs){
						$obj = new \stdClass();
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

