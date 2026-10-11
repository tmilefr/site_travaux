<?php

namespace App\Libraries\Elements;
/*
 * element_file.php
 * Document uploadé (champ fichier)
 * 
 */


class element_file extends element
{
	use UploadTrait;

	var $path = 'public/files/tmp/';
	var $class = '';
	var $typeof = 'file';
	var $linkname = '';

	/** Extensions de documents acceptées (jamais de script exécutable). */
	protected $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','odt','ods','odp','txt','csv','rtf','zip','jpg','jpeg','png','gif','webp'];

	public function PrepareForDBA($value){
		return $this->receiveUpload($this->allowed);
	}

	public function RenderFormElement(){

		return  '<input type="file" class="text-center form-control-file custom_file" id="'.$this->name.'" name="'.$this->name.'">
		<input type="hidden" name="memory_'.$this->name.'" id="memory_'.$this->name.'" value="'.esc((string) $this->value, 'attr').'">
		<small id="'.$this->name.'HelpBlock" class="form-text text-muted">'.esc((string) $this->value).'</small>';
	}

	public function Render(){
		$filename = base_url().preg_replace('#^public/#', '', $this->path).$this->value; // la racine web est public/
		return $filename;
	}
}

