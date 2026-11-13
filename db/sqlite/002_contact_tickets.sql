-- Migration 002 (SQLite): contact and correction messages need a kind, a body and a sender.
-- Same columns as db/mysql/002_contact_tickets.sql. SQLite cannot add ENUM or choose a column
-- position with AFTER, so kind is TEXT with a CHECK and the column order differs (names match).
-- Personal data here is limited to the e-mail address the sender gave.
ALTER TABLE tickets ADD COLUMN kind TEXT NOT NULL DEFAULT 'contact' CHECK (kind IN ('contact','correction'));
ALTER TABLE tickets ADD COLUMN sender_name TEXT NULL;
ALTER TABLE tickets ADD COLUMN sender_email TEXT NULL;
ALTER TABLE tickets ADD COLUMN body TEXT NULL;
