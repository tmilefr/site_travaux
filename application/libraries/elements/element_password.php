<?php
/*
 * element_password.php
 * PASSWORD Object in page
 *
 * CORRECTIONS v2 :
 *  - PrepareForDBA() : crypt() + sel fixe remplacé par password_hash() bcrypt
 *  - Le hash n'est recalculé QUE si la case "changer le mot de passe" est cochée,
 *    ce qui préserve le hash existant lors d'une édition sans modification du mdp.
 */
require_once(APPPATH.'libraries/elements/element.php');

class element_password extends element
{
	public function __construct()
	{
		parent::__construct();
		if (isset($this->CI->bootstrap_tools)) {
			$this->CI->bootstrap_tools->_SetHead('assets/js/togglefield.js', 'js');
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Rendu du champ dans un formulaire.
	 * En mode édition, affiche des étoiles + une case à cocher pour débloquer la saisie.
	 *
	 * @return string HTML
	 */
	public function RenderFormElement()
	{
		if ($this->disabled) {
			return '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '">'
			     . '<input class="form-control" type="text" value="********" readonly>';
		}

		// En édition : le champ est en readonly par défaut (contrôlé par togglefield.js)
		$txt  = $this->CI->bootstrap_tools->password_text(
			$this->name,
			$this->CI->lang->line($this->name),
			$this->value,
			'readonly'
		);
		$txt .= '<div class="form-check">
					<input class="form-check-input togglefield"
					       data-toggle="input' . $this->name . '"
					       type="checkbox"
					       name="' . $this->name . '_check"
					       id="' . $this->name . '_check"
					       value="change_password">
					<label class="form-check-label" for="' . $this->name . '_check">
						' . $this->CI->lang->line($this->name . '_change') . '
					</label>
				</div>';

		return $txt;
	}

	// -----------------------------------------------------------------------

	/**
	 * Rendu en mode lecture seule (liste, fiche).
	 *
	 * @return string
	 */
	public function Render()
	{
		return '********';
	}

	// -----------------------------------------------------------------------

	/**
	 * Prépare la valeur avant insertion / mise à jour en base.
	 *
	 * Si la case "changer le mot de passe" est cochée → on hashe en bcrypt.
	 * Sinon → on renvoie la valeur brute du POST (hash déjà stocké en base,
	 *          transmis via le champ hidden dans le formulaire).
	 *
	 * CORRECTION : password_hash() bcrypt remplace crypt() + sel fixe.
	 *
	 * @param  string $value  Valeur brute du POST (mot de passe en clair ou hash existant)
	 * @return string         Hash bcrypt ou hash existant inchangé
	 */
	public function PrepareForDBA($value)
	{
		$changeFlag = $this->CI->input->post($this->name . '_check');

		if ($changeFlag === 'change_password' && !empty($value)) {
			// Nouveau mot de passe : on hashe avec bcrypt
			return password_hash($value, PASSWORD_BCRYPT);
		}

		// Pas de changement : on retourne le hash existant tel quel
		return $value;
	}
}
