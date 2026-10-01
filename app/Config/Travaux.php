<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuration applicative du site des travaux.
 *
 * Remplace application/config/app.php et secured.php de CodeIgniter 3.
 * Les valeurs sensibles se surchargent dans .env, par exemple :
 *
 *   travaux.apiKey = ...
 *   travaux.passwordSalt = ...
 *   travaux.civilYear = 2026-2027
 *
 * La configuration SMTP se fait avec les variables natives de Config\Email
 * (email.SMTPHost, email.SMTPUser, email.SMTPPass, email.SMTPPort, email.SMTPCrypto).
 */
class Travaux extends BaseConfig
{
    public string $appName = "Site de l'association des parents de l'école ABCM de Mulhouse et Lutterbach";
    public string $slogan  = 'Outil de gestion des travaux';
    public string $about   = 'By NL';

    /** none | debug */
    public string $debugApp = 'none';
    public string $sidebar  = 'on';
    public int $unitTodo    = 20;
    public bool $maintenance = false;

    /** Année civile (scolaire) courante, format AAAA-AAAA */
    public string $civilYear = '2025-2026';

    /** Rôle ACL attribué par défaut aux familles */
    public int $roleFamille = 2;

    /** Active le reCAPTCHA sur la page de connexion */
    public bool $captcha = false;
    public string $siteCaptchaKey = '';
    public string $siteCaptchaSecretKey = '';

    /** Clé HMAC de signature des JWT de l'API */
    public string $apiKey = '';

    /** Sel historique crypt() (migration des anciens mots de passe) */
    public string $passwordSalt = '';

    public string $mailFromEmail = '';
    public string $mailFromName  = 'ABCM Mulhouse-Lutterbach';
    public string $mailReplyTo   = '';

    /** Connexion Delta (SSO famille) */
    public string $deltaBaseUrl   = 'https://delta-enfance3.fr/familleabcm/ABCMRegios68200/';
    public string $deltaUserAgent = 'abcmschule';
}
