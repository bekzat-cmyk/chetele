-- Выполните один раз только если push_subscriptions уже создана ранее.
ALTER TABLE push_subscriptions ADD COLUMN reminder_settings TEXT NULL AFTER reminder_time;
