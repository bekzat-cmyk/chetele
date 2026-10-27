-- Выполните только если push_subscriptions была создана старой версией API.
-- Новая API добавляет этот столбец сама при первом обращении.
ALTER TABLE push_subscriptions ADD COLUMN last_sent_marker CHAR(16) NULL AFTER last_sent_date;
