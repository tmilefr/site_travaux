<?php

namespace App\Libraries\Elements;
/*
 * element_img.php
 * image Object in page
 * 
 */


class element_img extends element
{
	use UploadTrait;

	var $path = 'public/files/tmp/';
	var $default = 'assets/img/team/empty.jpg';
	var $class = '';
	var $typeof = 'img';
    var $width = '';

	/** Formats d'image acceptés (redimensionnés par le gestionnaire d'images natif). */
	protected $allowed = ['jpg','jpeg','png','gif','webp'];

	public function PrepareForDBA($value){
		$name = $this->receiveUpload($this->allowed, $this->name . '_');
		$new  = $name !== null && $name !== $this->post('memory_' . $this->name);
		if ($new && ! $this->resize(ROOTPATH . $this->path . $name)) {
			// le fichier n'est pas une image valide : on le supprime et on garde l'ancienne valeur
			@unlink(ROOTPATH . $this->path . $name);
			session()->setFlashdata('upload_error', "Le fichier n'est pas une image valide.");

			return $this->post('memory_' . $this->name);
		}

		return $name;
	}

	/** Redimensionne à la largeur configurée (hauteur proportionnelle) ; false si le fichier n'est pas une image. */
	public function resize($filename): bool
	{
		if (@getimagesize($filename) === false) {
			return false;
		}
		if ($this->width) {
			try {
				service('image')->withFile($filename)->resize((int) $this->width, (int) $this->width, true, 'width')->save($filename);
			} catch (\Throwable $e) {
				log_message('error', 'Redimensionnement {file} : {message}', ['file' => basename($filename), 'message' => $e->getMessage()]);

				return false;
			}
		}

		return true;
	}

	public function RenderFormElement(){
		return  '<input type="file" class="text-center form-control-file custom_file" id="'.$this->name.'" name="'.$this->name.'">
		<input type="hidden" name="memory_'.$this->name.'" id="memory_'.$this->name.'" value="'.esc((string) $this->value, 'attr').'">
		<small id="'.$this->name.'HelpBlock" class="form-text text-muted">'.esc((string) $this->value).'</small>';

	}

	public function Render(){
        $filename = base_url().preg_replace('#^public/#', '', $this->path).$this->value; // la racine web est public/
		if (!$this->value)
			$filename = base_url().$this->default;
		return '<img src="'.$filename.'" class="team '.$this->class.'" alt="">';
	}

}

