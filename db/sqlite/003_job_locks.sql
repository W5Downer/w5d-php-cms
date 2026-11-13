-- Migration 003 (SQLite): named job locks. Same table as db/mysql/003_job_locks.sql.
CREATE TABLE job_locks (
  name        TEXT PRIMARY KEY,
  owner       TEXT NOT NULL,
  expires_at  INTEGER NOT NULL
);
