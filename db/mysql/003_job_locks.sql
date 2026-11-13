-- Migration 003: named job locks (rollup). Replaces MySQL GET_LOCK so both drivers share one code path.
-- Sql::acquireLock() inserts a row with an expiry; an expired row is reclaimed automatically.
CREATE TABLE job_locks (
  name        VARCHAR(64) NOT NULL,
  owner       VARCHAR(64) NOT NULL,
  expires_at  INT UNSIGNED NOT NULL,
  PRIMARY KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
