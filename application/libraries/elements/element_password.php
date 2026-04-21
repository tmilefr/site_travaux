<?php
/**
 * element_password.php
 *
 * Objet PASSWORD pour les formulaires.
 *
 * CORRECTIONS v3 :
 *  - Délégation à PasswordAuthenticator pour le hashage (source de vérité
 *    unique, coût bcrypt cohérent entre toutes les couches).
 *  - Le hash n'est recalculé que si la case "changer le mot de passe" est
 *    cochée, ce qui préserve le hash existant lors d'une édition qui ne
 *    touche pas au mot de passe.
 *  - Plus aucun appel direct à crypt() + PASSWORD_SALT.
 */
require_once(APPPATH . 'libraries/elements/element.php');

class element_password extends element
{
	public function __construct()
	{
		parent::__construct();
		$this->CI =& get_instance();
		if (isset($this->CI) && isset($this->CI->bootstrap_tools)) {
			$this->CI->bootstrap_tools->_SetHead('assets/js/togglefield.js', 'js');
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Rendu du champ en mode formulaire.
	 *
	 * @return string
	 */
	public function RenderFormElement()
	{
		if ($this->disabled) {
			return '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '">'
				. '<input class="form-control" type="text" value="********" readonly>';
		}

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
	 * Rendu en mode lecture seule.
	 *
	 * @return string
	 */
	public function Render()
	{
		return '********';
	}

	// -----------------------------------------------------------------------

	/**
	 * Prépare la valeur avant insertion / mise à jour.
	 *
	 * Si la case "changer le mot de passe" est cochée → on hashe en bcrypt.
	 * Sinon → on renvoie la valeur brute du POST (hash déjà stocké en base,
	 *          transmis via le champ hidden dans le formulaire).
	 *
	 * @param  string $value
	 * @return string
	 */
	public function PrepareForDBA($value)
	{
		$changeFlag = $this->CI->input->post($this->name . '_check');

		if ($changeFlag === 'change_password' && !empty($value)) {
			$this->CI->load->library('PasswordAuthenticator', [], 'passauth');
			return $this->CI->passauth->hash($value);
		}

		return $value;
	}
}

/* End of file element_password.php */
/* Location: ./application/libraries/elements/element_password.php */
