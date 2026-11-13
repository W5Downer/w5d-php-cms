-- Migration 002: contact and correction messages need a body and a sender.
-- Applied after 001. Personal data here is limited to the e-mail address the sender gave.
ALTER TABLE tickets
  ADD COLUMN kind ENUM('contact','correction') NOT NULL DEFAULT 'contact' AFTER country_id,
  ADD COLUMN sender_name VARCHAR(120) NULL AFTER kind,
  ADD COLUMN sender_email VARCHAR(190) NULL AFTER sender_name,
  ADD COLUMN body TEXT NULL AFTER subject;
