<?php
/* ============================================================================
 * FRAGMENT À AJOUTER à application/controllers/Admwork_controller.php
 * ============================================================================
 *
 * Ajoute :
 *   - validate_by_token($token)    : route publique (guest) — lien email
 *   - my_sessions()                : liste des sessions où je suis référent
 *   - validate_one($id_work)       : accès via session fam (alternative au lien)
 *   - _IsReferentOfWork($id_work)  : contrôle de sécurité
 *   - _ProcessRefValidation()      : traitement commun du POST
 *
 * ⚠️ Il faut AUSSI :
 *   - déclarer 'admwork_controller/validate_by_token' dans $guestPages
 *     de application/libraries/Acl.php
 *   - dans le __construct() du contrôleur, charger ValidationToken_model
 *
 * ============================================================================ */

	/**
	 * Point d'entrée GUEST : le référent arrive par un lien email.
	 * Pas de login requis — la sécurité tient au token.
	 *
	 * URL type : /Admwork_controller/validate_by_token/abc123...
	 *
	 * @param string $token
	 * @return void
	 */
	public function validate_by_token($token = null)
	{
		$this->LoadModel('ValidationToken_model');
		$this->LoadModel('Admwork_model');
		$this->LoadModel('Infos_model');
		$this->LoadModel('Familys_model');

		$tk = $this->ValidationToken_model->findValid($token);
		if (!$tk) {
			$this->data_view['error'] = $this->lang->line('REF_TOKEN_INVALID');
			$this->_set('view_inprogress', 'unique/Admwork_controller_token_error');
			$this->render_view();
			return;
		}

		// Session validée, on reproduit la logique de validate_one mais sans ACL
		$this->data_view['msg']     = '';
		$this->data_view['token']   = $tk->token;
		$this->data_view['via_token'] = true;

		// Traitement du POST
		if ($this->input->post('elements')) {
			$this->_ProcessRefValidation($tk->id_travaux, $tk->id_fam_ref);
			$this->ValidationToken_model->markUsed($tk->id);
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_VALIDATE_SAVED') . '</div>';
		}

		$work = $this->_BuildWorkForRefView($tk->id_travaux);
		$this->data_view['work']   = $work;
		$this->data_view['design'] = $this->render_object->GetDesign($work->type);

		$this->_set('view_inprogress', 'unique/Admwork_controller_validate_one_ref');
		$this->render_view();
	}

	/**
	 * Alternative (menu dans le site) : l'utilisateur connecté "fam"
	 * qui est référent de la session peut aussi y accéder.
	 *
	 * @param int $id_work
	 * @return void
	 */
	public function validate_one($id_work)
	{
		if (!$id_work || $this->acl->getType() !== 'fam') {
			redirect('Home/no_right');
		}
		if (!$this->_IsReferentOfWork($id_work)) {
			redirect('Home/no_right');
		}

		$this->data_view['msg']       = '';
		$this->data_view['via_token'] = false;
		$this->data_view['token']     = null;

		if ($this->input->post('elements')) {
			$this->_ProcessRefValidation($id_work, $this->acl->getUserId());
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_VALIDATE_SAVED') . '</div>';
		}

		$work = $this->_BuildWorkForRefView($id_work);
		$this->data_view['work']   = $work;
		$this->data_view['design'] = $this->render_object->GetDesign($work->type);

		$this->_set('view_inprogress', 'unique/Admwork_controller_validate_one_ref');
		$this->render_view();
	}

	/**
	 * Liste des sessions où l'utilisateur connecté est référent.
	 *
	 * @return void
	 */
	public function my_sessions()
	{
		if ($this->acl->getType() !== 'fam') {
			redirect('Home/no_right');
		}
		$this->data_view['my_works'] = $this->Admwork_model->GetWorksAsReferent(
			$this->acl->getUserId()
		);
		$this->_set('view_inprogress', 'unique/' . $this->_controller_name . '_my_sessions');
		$this->render_view();
	}

	// -----------------------------------------------------------------------
	// Helpers privés
	// -----------------------------------------------------------------------

	/**
	 * Assemble l'objet $work enrichi des inscrits + familles (pour la vue ref).
	 *
	 * @param int $id_work
	 * @return stdClass
	 */
	private function _BuildWorkForRefView($id_work)
	{
		$this->Admwork_model->_set('key_value', $id_work);
		$work = $this->Admwork_model->get_one();
		$work->pilot = $this->Trombi_model->GetConsolidateMember($work->referent_travaux);

		$registreds = $this->Infos_model->GetRegistred($id_work);
		if ($registreds) {
			foreach ($registreds as $key => $reg) {
				$this->Familys_model->_set('key_value', $reg->id_famille);
				$registreds[$key]->family = $this->Familys_model->get_one();
			}
		}
		$work->registred = $registreds ?: [];
		return $work;
	}

	/**
	 * Vérifie que l'utilisateur connecté est le référent de la session.
	 *
	 * @param int $id_work
	 * @return bool
	 */
	private function _IsReferentOfWork($id_work)
	{
		$refFamily = $this->Admwork_model->GetReferentFamily($id_work);
		if (!$refFamily) return false;
		return ((int) $refFamily->id === (int) $this->acl->getUserId());
	}

	/**
	 * Traitement commun du POST de validation référent.
	 * Gère : présence, nb d'unités, commentaire, désinscription.
	 *
	 * @param int $id_work
	 * @param int $id_fam_ref  famille.id du référent qui valide
	 * @return void
	 */
	private function _ProcessRefValidation($id_work, $id_fam_ref)
	{
		$elements    = $this->input->post('elements');
		$to_delete   = (array) $this->input->post('unregister');
		$global_com  = $this->input->post('commentaire_global');

		if (!is_array($elements)) $elements = [];

		foreach ($elements as $id_info) {
			$id_info = (int) $id_info;

			// 1) Désinscription prioritaire
			if (in_array($id_info, $to_delete)) {
				$this->Infos_model->_set('key_value', $id_info);
				$this->Infos_model->delete();
				continue;
			}

			// 2) Sinon, mise à jour présence + commentaire
			$present     = $this->input->post('present_' . $id_info);
			$nb_units    = $this->input->post('nb_unites_' . $id_info);
			$commentaire = trim((string) $this->input->post('commentaire_' . $id_info));

			// préfixe de commentaire global si fourni
			if ($global_com) {
				$commentaire = trim($global_com . ($commentaire ? ' | ' . $commentaire : ''));
			}

			$datas = [
				'nb_unites_valides' => $present ? (float) $nb_units : 0,
				'commentaire_ref'   => $commentaire ?: null,
				'valide_par_ref'    => (int) $id_fam_ref,
				'valide_ref_at'     => date('Y-m-d H:i:s'),
			];

			$this->Infos_model->valid_unit($id_info, $datas);
		}
	}
