-- Выполните один раз, если таблица users уже была создана старой версией schema.sql.
ALTER TABLE users ADD COLUMN login VARCHAR(24) NULL UNIQUE AFTER display_name;
ALTER TABLE users ADD COLUMN pin_hash VARCHAR(255) NULL AFTER login;
