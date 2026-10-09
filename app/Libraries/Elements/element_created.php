<?php

namespace App\Libraries\Elements;
/*
 * element_created.php
 * created Date Object in page
 * 
 */

class element_created extends element
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

