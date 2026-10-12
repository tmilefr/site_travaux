-- Le journal d'envoi SMTP depasse 255 caracteres : la fin (qui contient l'erreur reelle) etait tronquee.
ALTER TABLE `sendmail_statut` MODIFY `error` TEXT NULL;
