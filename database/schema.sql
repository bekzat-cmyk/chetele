-- Ниет: схема MySQL 8+ для пользователей, ежедневных дел и рейтинга.
-- Выполните этот файл в выбранной базе данных.

CREATE TABLE IF NOT EXISTS users (
  id CHAR(36) NOT NULL PRIMARY KEY,
  display_name VARCHAR(24) NOT NULL,
  login VARCHAR(24) NULL UNIQUE,
  pin_hash VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_name (display_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_entries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  entry_date DATE NOT NULL,
  tasks JSON NOT NULL,
  qaza JSON NOT NULL,
  completed_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  points SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT uq_user_day UNIQUE (user_id, entry_date),
  INDEX idx_entries_rating (entry_date, points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Подписки устройств для серверных Web Push-уведомлений.
CREATE TABLE IF NOT EXISTS push_subscriptions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id CHAR(36) NOT NULL,
  endpoint TEXT NOT NULL,
  endpoint_hash CHAR(64) NOT NULL,
  p256dh VARCHAR(512) NOT NULL,
  auth VARCHAR(256) NOT NULL,
  timezone VARCHAR(64) NOT NULL DEFAULT 'Asia/Bishkek',
  reminder_time CHAR(5) NOT NULL DEFAULT '12:00',
  reminder_settings TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_sent_date DATE NULL,
  last_sent_marker CHAR(16) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_push_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_push_endpoint (endpoint_hash),
  KEY idx_push_reminders (active, reminder_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
