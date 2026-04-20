<?php
/*
 * element_file.php
 * File upload Object in page
 *
 * CORRECTIONS v2 :
 *  - PrepareForDBA() : validation de l'extension et du type MIME
 *  - PrepareForDBA() : validation de la taille maximale
 *  - PrepareForDBA() : nom de fichier unique (uniqid) pour éviter les collisions et
 *                      empêcher l'écrasement de fichiers existants
 *  - PrepareForDBA() : suppression du echo debug() en production
 *  - PrepareForDBA() : die() remplacé par un flashdata d'erreur + return false
 *  - Le répertoire cible est configurable via $upload_path
 */
require_once(APPPATH.'libraries/elements/element.php');

class element_file extends element
{
	/** Extensions autorisées (sans le point, en minuscules) */
	protected $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

	/** Types MIME autorisés */
	protected $allowed_mimes = [
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/webp',
	];

	/** Taille maximale en octets (défaut : 2 Mo) */
	protected $max_size = 2097152;

	/** Chemin de destination relatif à APPPATH (sans 'application/') */
	protected $upload_path = 'assets/img/team/';

	// -----------------------------------------------------------------------

	/**
	 * Prépare le fichier uploadé avant insertion en base.
	 *
	 * CORRECTIONS :
	 *  1. Validation de l'extension.
	 *  2. Validation du type MIME (finfo, pas seulement le nom).
	 *  3. Limite de taille.
	 *  4. Nom de fichier unique pour éviter les collisions et les path traversal.
	 *  5. Suppression du echo debug() et du die().
	 *
	 * @param  string $value  Valeur courante du champ (non utilisée ici)
	 * @return string|false   Nom du fichier enregistré, ou valeur mémoire si aucun upload
	 */
	public function PrepareForDBA($value)
	{
		// Aucun fichier envoyé → on conserve la valeur précédente
		if (empty($_FILES[$this->name]['name']) || $_FILES[$this->name]['size'] === 0) {
			return $this->CI->input->post('memory_' . $this->name);
		}

		$file      = $_FILES[$this->name];
		$origName  = $file['name'];
		$tmpPath   = $file['tmp_name'];
		$fileSize  = $file['size'];

		// 1. Vérification de la taille
		if ($fileSize > $this->max_size) {
			$this->CI->session->set_flashdata(
				'error',
				sprintf('Fichier trop volumineux (max %d Mo).', $this->max_size / 1048576)
			);
			return $this->CI->input->post('memory_' . $this->name);
		}

		// 2. Vérification de l'extension
		$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
		if (!in_array($ext, $this->allowed_extensions, true)) {
			$this->CI->session->set_flashdata(
				'error',
				'Type de fichier non autorisé. Extensions acceptées : ' . implode(', ', $this->allowed_extensions)
			);
			return $this->CI->input->post('memory_' . $this->name);
		}

		// 3. Vérification du type MIME réel (via finfo, pas le Content-Type HTTP)
		if (function_exists('finfo_open')) {
			$finfo    = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_file($finfo, $tmpPath);
			finfo_close($finfo);

			if (!in_array($mimeType, $this->allowed_mimes, true)) {
				$this->CI->session->set_flashdata(
					'error',
					'Le contenu du fichier ne correspond pas à un type autorisé.'
				);
				return $this->CI->input->post('memory_' . $this->name);
			}
		}

		// 4. Génération d'un nom de fichier unique et sûr
		$safeBasename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
		$uniqueName   = uniqid($safeBasename . '_', true) . '.' . $ext;

		// 5. Déplacement du fichier
		$targetDir  = str_replace('application', '', APPPATH) . ltrim($this->upload_path, '/');
		$targetFile = $targetDir . $uniqueName;

		if (!is_dir($targetDir)) {
			mkdir($targetDir, 0755, true);
		}

		if (!move_uploaded_file($tmpPath, $targetFile)) {
			$this->CI->session->set_flashdata('error', 'Erreur lors du déplacement du fichier uploadé.');
			log_message('error', 'element_file::PrepareForDBA() — move_uploaded_file() a échoué pour : ' . $origName);
			return $this->CI->input->post('memory_' . $this->name);
		}

		return $uniqueName;
	}

	// -----------------------------------------------------------------------

	/**
	 * Rendu du champ dans un formulaire.
	 *
	 * @return string HTML
	 */
	public function RenderFormElement()
	{
		return '<input type="file"
		               class="text-center form-control-file custom_file"
		               id="'   . $this->name . '"
		               name="' . $this->name . '">
		        <input type="hidden"
		               name="memory_' . $this->name . '"
		               id="memory_'   . $this->name . '"
		               value="'       . htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') . '">
		        <small id="' . $this->name . 'HelpBlock" class="form-text text-muted">'
		            . htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8') .
		        '</small>';
	}
}
