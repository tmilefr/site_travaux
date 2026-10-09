<?php

namespace App\Libraries\Elements;
/*
 * element_updated.php
 * created Date Object in page
 * 
 */

class element_updated extends element
{	
	protected $form_mod;
	/** Horodatage posé par le modèle (Model::$useTimestamps) : aucun champ de formulaire. */
	public function RenderFormElement(){
		return '';
	}

	public function Render(){
		return ($this->value);
	}
}

