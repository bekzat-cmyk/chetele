-- Event moderation, pause and reminders. api/index.php adds these columns itself on the first request.
ALTER TABLE events ADD COLUMN moderation TINYINT(1) NOT NULL DEFAULT 0 AFTER results_visibility;
ALTER TABLE events ADD COLUMN remind_before_end TINYINT(1) NOT NULL DEFAULT 1 AFTER moderation;
ALTER TABLE events ADD COLUMN reminded_on DATE NULL AFTER remind_before_end;
ALTER TABLE events ADD COLUMN manual_reminded_at DATETIME NULL AFTER reminded_on;
-- approved | pending | rejected; only approved participants count in totals and ratings.
ALTER TABLE event_participants ADD COLUMN status VARCHAR(12) NOT NULL DEFAULT 'approved' AFTER personal_target;
-- events.status may now also be 'paused': still shown in the app, closed to new participants.
-- Book and custom events. A custom event is not tied to the app's deeds: participants add amounts.
ALTER TABLE events ADD COLUMN custom_unit VARCHAR(16) NULL AFTER metric;
ALTER TABLE events ADD COLUMN custom_step INT UNSIGNED NOT NULL DEFAULT 1 AFTER custom_unit;
CREATE TABLE IF NOT EXISTS event_contributions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  event_id BIGINT UNSIGNED NOT NULL,
  user_id CHAR(36) NOT NULL,
  amount INT UNSIGNED NOT NULL,
  entry_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_contributions_event_user (event_id, user_id),
  CONSTRAINT fk_contribution_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_contribution_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
