-- W5D schema, migration 001, SQLite dialect (default driver).
-- Mirrors db/mysql/001_initial.sql table for table. Differences, all deliberate:
--   ENUM(...)            -> TEXT with CHECK (...)
--   AUTO_INCREMENT       -> INTEGER PRIMARY KEY AUTOINCREMENT
--   TIMESTAMP ... ON UPDATE -> trigger (see updated_at trigger below)
--   KEY / UNIQUE KEY     -> CREATE INDEX / UNIQUE constraint
--   audit_log            -> append-only enforced by triggers (MySQL enforces this in the application only)
-- Foreign keys are enforced: bin/migrate.php and src/DB.php run PRAGMA foreign_keys = ON.
-- Migrations run as one script (triggers contain semicolons), so keep this file free of
-- statements that must run outside a transaction.

CREATE TABLE countries (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  iso           TEXT NOT NULL UNIQUE CHECK (length(iso) = 2),
  name_en       TEXT NOT NULL,
  currency      TEXT NOT NULL CHECK (length(currency) = 3),
  timezone      TEXT NOT NULL,
  status        TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','active','paused')),
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE regions (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id  INTEGER NOT NULL REFERENCES countries (id),
  slug        TEXT NOT NULL,
  name_en     TEXT NOT NULL,
  UNIQUE (country_id, slug)
);

-- Content mirrors (data/content/ is the source of truth; bin/sync-content.php fills these).
CREATE TABLE macros (
  slug        TEXT PRIMARY KEY,
  number      INTEGER NOT NULL,
  title_en    TEXT NOT NULL
);

CREATE TABLE services (
  slug         TEXT PRIMARY KEY,
  number       INTEGER NOT NULL UNIQUE,
  macro_slug   TEXT NOT NULL REFERENCES macros (slug),
  title_en     TEXT NOT NULL
);
CREATE INDEX ix_services_macro ON services (macro_slug);

CREATE TABLE projects (
  slug         TEXT PRIMARY KEY,
  title_en     TEXT NOT NULL,
  status       TEXT NOT NULL,
  evidence     TEXT NOT NULL
);

CREATE TABLE layers (
  slug         TEXT PRIMARY KEY,
  layer        TEXT NOT NULL,
  title_en     TEXT NOT NULL
);

CREATE TABLE pages (
  id          TEXT PRIMARY KEY,
  path        TEXT NOT NULL,
  title_en    TEXT NOT NULL
);

-- Services switched on per country. Every service starts inactive (C4 clone rule).
CREATE TABLE services_country (
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  service_slug TEXT NOT NULL REFERENCES services (slug),
  active       INTEGER NOT NULL DEFAULT 0,
  seats        INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (country_id, service_slug)
);

CREATE TABLE audiences (
  code        TEXT PRIMARY KEY
);

CREATE TABLE certification_tasks (
  id           TEXT PRIMARY KEY,
  service_slug TEXT NOT NULL,
  level        INTEGER NOT NULL,
  title_en     TEXT NOT NULL
);
CREATE INDEX ix_cert_service ON certification_tasks (service_slug, level);

CREATE TABLE delivery_contracts (
  service_slug TEXT PRIMARY KEY,
  unit_en      TEXT NOT NULL
);

-- Roles, permissions and users. Eight roles (vocab.php role list).
CREATE TABLE roles (
  code        TEXT PRIMARY KEY,
  label_en    TEXT NOT NULL
);

CREATE TABLE permissions (
  code        TEXT PRIMARY KEY,
  label_en    TEXT NOT NULL
);

CREATE TABLE role_permissions (
  role_code       TEXT NOT NULL REFERENCES roles (code),
  permission_code TEXT NOT NULL REFERENCES permissions (code),
  PRIMARY KEY (role_code, permission_code)
);

CREATE TABLE users (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id     INTEGER NULL REFERENCES countries (id),
  username       TEXT NOT NULL UNIQUE,
  email          TEXT NOT NULL,
  password_hash  TEXT NOT NULL,
  role_code      TEXT NOT NULL REFERENCES roles (code),
  mfa_enabled    INTEGER NOT NULL DEFAULT 0,
  disabled       INTEGER NOT NULL DEFAULT 0,
  created_at     TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_users_country ON users (country_id);

-- Applicants. The CIN column stores a keyed HMAC, never the plain number.
-- Duplicate guard: UNIQUE(country_id, cin_hmac) and UNIQUE(country_id, email_hmac).
-- SQLite treats NULLs as distinct in UNIQUE indexes, the same as MySQL InnoDB.
CREATE TABLE applicants (
  id              INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id      INTEGER NOT NULL REFERENCES countries (id),
  region_id       INTEGER NULL REFERENCES regions (id),
  macro_slug      TEXT NOT NULL,
  service_slug    TEXT NOT NULL,
  status          TEXT NOT NULL DEFAULT 'registered'
                  CHECK (status IN ('registered','verified','screened','assigned','l1','l2','l3','l4','certified')),
  full_name       TEXT NOT NULL,
  email           TEXT NOT NULL,
  email_hmac      TEXT NOT NULL,
  phone           TEXT NULL,
  phone_verified  INTEGER NOT NULL DEFAULT 0,
  degree_level    TEXT NOT NULL DEFAULT 'none'
                  CHECK (degree_level IN ('none','licence','maitrise','ingenieur','other')),
  field_text      TEXT NULL,
  available_from  TEXT NULL,
  cin_hmac        TEXT NULL,
  lang            TEXT NOT NULL DEFAULT 'en',
  utm_source      TEXT NULL,
  created_at      TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (country_id, cin_hmac),
  UNIQUE (country_id, email_hmac)
);
CREATE INDEX ix_applicants_status ON applicants (country_id, status);
CREATE INDEX ix_applicants_macro ON applicants (country_id, macro_slug, status);
CREATE INDEX ix_applicants_region ON applicants (country_id, region_id, status);

CREATE TABLE documents (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  applicant_id  INTEGER NOT NULL REFERENCES applicants (id),
  kind          TEXT NOT NULL CHECK (kind IN ('diploma','cin_scan','other')),
  storage_key   TEXT NOT NULL,
  sha256        TEXT NOT NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_documents_applicant ON documents (country_id, applicant_id);

CREATE TABLE verifications (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  applicant_id  INTEGER NOT NULL REFERENCES applicants (id),
  method        TEXT NOT NULL CHECK (method IN ('otp_sms','document_review','manual')),
  outcome       TEXT NOT NULL DEFAULT 'pending' CHECK (outcome IN ('pending','passed','failed')),
  reviewer_id   INTEGER NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_verifications_applicant ON verifications (country_id, applicant_id);

CREATE TABLE cohorts (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  service_slug TEXT NOT NULL,
  label        TEXT NOT NULL,
  capacity     INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX ix_cohorts_service ON cohorts (country_id, service_slug);

CREATE TABLE cert_submissions (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  applicant_id  INTEGER NOT NULL REFERENCES applicants (id),
  task_id       TEXT NOT NULL,
  outcome       TEXT NOT NULL DEFAULT 'submitted' CHECK (outcome IN ('submitted','approved','rejected')),
  reviewer_id   INTEGER NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_cert_applicant ON cert_submissions (country_id, applicant_id);

CREATE TABLE teams (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  service_slug TEXT NOT NULL,
  leader_id    INTEGER NULL
);
CREATE INDEX ix_teams_country ON teams (country_id, service_slug);

-- Manager cap: at most 12 experts per manager (enforced in the application and by lint).
CREATE TABLE team_members (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  team_id       INTEGER NOT NULL REFERENCES teams (id),
  applicant_id  INTEGER NULL,
  user_id       INTEGER NULL,
  role_code     TEXT NOT NULL,
  manager_id    INTEGER NULL
);
CREATE INDEX ix_team_members_team ON team_members (country_id, team_id);
CREATE INDEX ix_team_members_manager ON team_members (country_id, manager_id);

CREATE TABLE directives (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  service_slug TEXT NOT NULL,
  week_id      INTEGER NOT NULL,
  body         TEXT NOT NULL,
  created_by   INTEGER NULL,
  created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_directives_week ON directives (country_id, week_id);

-- Weekly reports. Sampling: status = 'submitted' rows, 5% selected deterministically (see src/Sampling.php).
CREATE TABLE weekly_reports (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  applicant_id  INTEGER NOT NULL REFERENCES applicants (id),
  service_slug  TEXT NOT NULL,
  manager_id    INTEGER NULL,
  week_id       INTEGER NOT NULL,
  status        TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','submitted','approved','rejected')),
  sampled       INTEGER NOT NULL DEFAULT 0,
  sample_result TEXT NULL CHECK (sample_result IN ('pending','sampled_pass','sampled_fail')),
  units_done    NUMERIC(10,2) NOT NULL DEFAULT 0,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_wr_week_status ON weekly_reports (country_id, week_id, status);
CREATE INDEX ix_wr_manager_week ON weekly_reports (country_id, manager_id, week_id);
CREATE INDEX ix_wr_applicant ON weekly_reports (country_id, applicant_id);

CREATE TABLE approvals (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id  INTEGER NOT NULL REFERENCES countries (id),
  subject     TEXT NOT NULL,
  subject_id  INTEGER NOT NULL,
  decision    TEXT NOT NULL CHECK (decision IN ('approved','rejected')),
  actor_id    INTEGER NOT NULL,
  created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_approvals_subject ON approvals (country_id, subject, subject_id);

CREATE TABLE clients (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id  INTEGER NOT NULL REFERENCES countries (id),
  name        TEXT NOT NULL,
  created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_clients_country ON clients (country_id);

CREATE TABLE contracts (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id  INTEGER NOT NULL REFERENCES countries (id),
  client_id   INTEGER NOT NULL REFERENCES clients (id),
  scope       TEXT NOT NULL,
  status      TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','signed','closed'))
);
CREATE INDEX ix_contracts_country ON contracts (country_id, status);

CREATE TABLE invoices (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  contract_id  INTEGER NOT NULL REFERENCES contracts (id),
  amount_minor INTEGER NOT NULL,
  currency     TEXT NOT NULL CHECK (length(currency) = 3),
  status       TEXT NOT NULL DEFAULT 'issued' CHECK (status IN ('issued','paid','overdue')),
  issued_on    TEXT NOT NULL
);
CREATE INDEX ix_invoices_country ON invoices (country_id, status);

CREATE TABLE payroll_runs (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  period       TEXT NOT NULL,
  simulated    INTEGER NOT NULL DEFAULT 1,
  total_minor  INTEGER NOT NULL DEFAULT 0,
  created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_payroll_country ON payroll_runs (country_id, period);

CREATE TABLE campaigns (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  name         TEXT NOT NULL,
  channel      TEXT NOT NULL,
  utm_source   TEXT NOT NULL,
  status       TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','active','paused','ended'))
);
CREATE INDEX ix_campaigns_country ON campaigns (country_id, status);

CREATE TABLE messages (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  channel      TEXT NOT NULL CHECK (channel IN ('sms','email','whatsapp')),
  lang         TEXT NOT NULL,
  body         TEXT NOT NULL,
  status       TEXT NOT NULL DEFAULT 'queued' CHECK (status IN ('queued','sent','failed')),
  created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_messages_country ON messages (country_id, status);

CREATE TABLE tickets (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  applicant_id INTEGER NULL,
  subject      TEXT NOT NULL,
  status       TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open','answered','closed')),
  due_at       TEXT NULL,
  created_at   TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_tickets_country ON tickets (country_id, status, due_at);

-- Append-only: the application never issues UPDATE or DELETE against audit_log.
-- SQLite enforces it with triggers, so a bug cannot rewrite history.
CREATE TABLE audit_log (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id  INTEGER NULL,
  actor_id    INTEGER NULL,
  action      TEXT NOT NULL,
  subject     TEXT NULL,
  subject_id  TEXT NULL,
  detail      TEXT NULL,
  ip_hash     TEXT NULL,
  created_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_audit_country ON audit_log (country_id, created_at);
CREATE TRIGGER audit_log_no_update BEFORE UPDATE ON audit_log
BEGIN
  SELECT RAISE(ABORT, 'audit_log is append-only');
END;
CREATE TRIGGER audit_log_no_delete BEFORE DELETE ON audit_log
BEGIN
  SELECT RAISE(ABORT, 'audit_log is append-only');
END;

-- Consent records. Each registration writes one row per consent purpose.
CREATE TABLE consents (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id    INTEGER NOT NULL REFERENCES countries (id),
  applicant_id  INTEGER NOT NULL REFERENCES applicants (id),
  purpose       TEXT NOT NULL,
  notice_version TEXT NOT NULL,
  granted       INTEGER NOT NULL,
  created_at    TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX ix_consents_applicant ON consents (country_id, applicant_id);

-- Rate-limit buckets for registration and login (per IP and per CIN).
CREATE TABLE rate_limits (
  bucket        TEXT PRIMARY KEY,
  window_start  INTEGER NOT NULL,
  hits          INTEGER NOT NULL DEFAULT 0
);

-- Daily aggregates. Every dashboard and /api/kpis.json reads only this table.
CREATE TABLE rollups_daily (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id   INTEGER NOT NULL REFERENCES countries (id),
  day          TEXT NOT NULL,
  macro_slug   TEXT NOT NULL DEFAULT '',
  service_slug TEXT NOT NULL DEFAULT '',
  metric       TEXT NOT NULL,
  value        INTEGER NOT NULL DEFAULT 0,
  computed_at  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (country_id, day, macro_slug, service_slug, metric)
);
CREATE INDEX ix_rollup_day ON rollups_daily (country_id, day);
CREATE INDEX ix_rollup_macro_day ON rollups_daily (country_id, macro_slug, day);

-- ON UPDATE CURRENT_TIMESTAMP equivalent for applicants.updated_at.
CREATE TRIGGER applicants_updated_at AFTER UPDATE ON applicants
WHEN NEW.updated_at = OLD.updated_at
BEGIN
  UPDATE applicants SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END;
