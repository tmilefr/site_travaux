<?php
/*
 * Modele de app/Config/legacy/secured.php (fichier NON versionne).
 * Copier ce fichier en "secured.php" et renseigner les vraies valeurs.
 */

define('API_KEY', 'changeme');              // cle HMAC pour la signature des JWT
define('PASSWORD_SALT', 'changeme');        // sel legacy crypt() (migration des anciens mots de passe)
define('SITE_CAPTCHA_KEY', '');             // reCAPTCHA : cle publique
define('SITE_CAPTCHA_SECRET_KEY', '');           // reCAPTCHA : cle secrete

$config['smtp_host']       = 'smtp.example.com';
$config['smtp_port']       = 587;
$config['smtp_user']       = '';
$config['smtp_pass']       = '';
$config['smtp_crypto']     = 'tls';
$config['mail_from_email'] = 'noreply@example.com';
$config['mail_from_name']  = 'ABCM Mulhouse-Lutterbach';
$config['mail_reply_to']   = '';
