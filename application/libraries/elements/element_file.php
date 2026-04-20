<?php
/*
 * element_date.php
 * Date Object in page
 * 
 */


class element_file extends element
{	
	var $path = 'public/files/tmp/';
	var $class = '';
	var $typeof = 'file';
	var $linkname = '';

	public function PrepareForDBA($value){

		if (isset($_FILES[$this->name]["name"]) AND $_FILES[$this->name]["size"] > 0 ){
			echo debug($_FILES[$this->name]); 
			$target_dir = str_replace('application', $this->path, APPPATH );
			$target_file = $target_dir . basename($_FILES[$this->name]["name"]);
			if (move_uploaded_file($_FILES[$this->name]["tmp_name"], $target_file)) {
				
			} else {
				die("Sorry, there was an error uploading your file.");
			}
			return basename($_FILES[$this->name]["name"]);
		} else {
			return $_POST['memory_'.$this->name];
		}
	}

	public function RenderFormElement(){

		return  '<input type="file" class="text-center form-control-file custom_file" id="'.$this->name.'" name="'.$this->name.'">
		<input type="hidden" name="memory_'.$this->name.'" id="memory_'.$this->name.'" value="'.$this->value.'">
		<small id="'.$this->name.'HelpBlock" class="form-text text-muted">'.$this->value.'</small>';
	}

	public function Render(){
		$filename = base_url().$this->path.$this->value;
		return $filename;
	}
}

