-- Alertes e-mail de nouvelles sessions (commande spark cron:session-alerts) :
-- date de l'alerte, pour ne pas notifier deux fois la meme session.
ALTER TABLE `travaux` ADD COLUMN `alert_sent_at` DATETIME NULL;
