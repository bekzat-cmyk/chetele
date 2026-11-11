-- Event moderation, pause and reminders. api/index.php adds these columns itself on the first request.
ALTER TABLE events ADD COLUMN moderation TINYINT(1) NOT NULL DEFAULT 0 AFTER results_visibility;
ALTER TABLE events ADD COLUMN remind_before_end TINYINT(1) NOT NULL DEFAULT 1 AFTER moderation;
ALTER TABLE events ADD COLUMN reminded_on DATE NULL AFTER remind_before_end;
ALTER TABLE events ADD COLUMN manual_reminded_at DATETIME NULL AFTER reminded_on;
-- approved | pending | rejected; only approved participants count in totals and ratings.
ALTER TABLE event_participants ADD COLUMN status VARCHAR(12) NOT NULL DEFAULT 'approved' AFTER personal_target;
-- events.status may now also be 'paused': still shown in the app, closed to new participants.
