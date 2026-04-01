<?php
/*
 * element_password.php
 * PASSWORD Object in page
 * 
 */

class element_password extends element
{
	
	protected $change_password = null;
	
	public function __construct(){
		parent::__construct();
		if (isset($this->RenderTools))
		{
			$this->RenderTools->_SetHead('assets/js/togglefield.js','js');
		}
	}
	
	public function RenderFormElement(){ 
		
		if ($this->disabled)
			$txt = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"><input class="form-control" type="text" value="********" readonly>';
		else {
			//en edition, le mot de passe est déjà crypt ... en relation avec le js togglefield
			$txt = $this->RenderTools->password_text($this->name, Lang($this->name) , $this->value, 'readonly');
			$txt .= '<div class="form-check">
						<input class="form-check-input togglefield" data-toggle="input'.$this->name.'" type="checkbox" name="'.$this->name.'_check" id="'.$this->name.'_check" value="change_password">
						<label class="form-check-label" for="'.$this->name.'_check">
						'.Lang(''.$this->name.'_change').'
						</label>
					</div>';
		}
		return $txt;
	}
	
	public function Render(){
		return '********';
	}

	public function PrepareForDBA($value){
		$post_data = $this->render_object->_get('post_data');
		//en edition, le mot de passe est déjà chiffré ...
		if(isset($post_data[$this->name.'_check']) && $post_data[$this->name.'_check'] == "change_password"){
			return crypt($value, PASSWORD_SALT);
		} else {
			return $value;
		}		
	}
}

