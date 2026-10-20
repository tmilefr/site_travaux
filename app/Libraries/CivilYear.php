<?php

namespace App\Libraries;

/**
 * Année civile (scolaire) de travail : celle choisie dans la barre de menu, mémorisée en session, qui borne les listes
 * (familles, unités, travaux), l'historique, les stats et la cantine. Par défaut : Config\Travaux::$civilYear.
 */
class CivilYear
{
	public const SESSION_KEY = 'work_civil_year';
	/** Paramètre d'URL qui change l'année (?civil_year=2025-2026) */
	public const PARAM = 'civil_year';

	public function __construct(private ?object $session = null)
	{
		$this->session ??= session();
	}

	/** Années proposées dans le sélecteur (clé = valeur stockée en base), de la plus ancienne à la plus récente. */
	public function years(): array
	{
		$years = config('Travaux')->civilYears;
		$default = config('Travaux')->civilYear;
		$years[$default] ??= $default;
		ksort($years);

		return $years;
	}

	/** Année de travail : le choix mémorisé s'il est valide, sinon l'année par défaut de la config. */
	public function get(): string
	{
		$year = $this->session->get(self::SESSION_KEY);

		return $this->isValid($year) ? (string) $year : config('Travaux')->civilYear;
	}

	/** Mémorise l'année ; false (sans rien changer) si elle n'est pas dans la liste. */
	public function set($year): bool
	{
		if (! $this->isValid($year)) {
			return false;
		}
		$this->session->set(self::SESSION_KEY, (string) $year);

		return true;
	}

	public function isValid($year): bool
	{
		return is_scalar($year) && array_key_exists((string) $year, $this->years());
	}
}
