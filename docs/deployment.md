# Deployment

W5D is a **dynamic PHP 8.2+ application**. The default database is **SQLite** (one file,
`storage/w5d.sqlite`, no server). **MySQL 8 / MariaDB 10.6+** is optional (`DB_DRIVER=mysql`). It is not a
static build. Pages render per request from `data/content/` (JSON) and read state from the database.

Architecture decision: the README's dynamic design is the one implemented. `bin/build.php` and
`dist/` do not exist and must not be added (see `config/vocab.php` and the README AI prompt).

## Requirements

- PHP 8.2+ with `pdo_sqlite` (default) or `pdo_mysql` (optional), `mbstring`, `json`, `session`, `openssl`, `curl`
- SQLite 3.35+ (bundled with PHP `pdo_sqlite`), or MySQL 8.0+ (InnoDB, `utf8mb4_unicode_ci`) / MariaDB 10.6+
- Apache 2.4+ with `mod_rewrite` and `mod_headers`, document root = repository root
- No Composer, no Node build step, no CDN

## First install

```bash
cp .env.example .env                       # then edit: APP_KEY, W5D_SAMPLE_SEED, DB_*, WEBMASTER_*
php -r "echo password_hash('YourStrongPassword', PASSWORD_ARGON2ID), PHP_EOL;"
                                           # paste into .env as WEBMASTER_PASSWORD_HASH
php bin/migrate.php                        # applies db/sqlite/*.sql (DB_DRIVER=sqlite), seeds reference data, creates the owner
php bin/sync-content.php                   # mirrors data/content/ into database tables
php bin/lint.php                           # must print "lint: PASS"
```

Generate `APP_KEY` and `W5D_SAMPLE_SEED` with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`.
Both must be kept secret and must not change after data exists: `APP_KEY` keys the HMAC of e-mail and
CIN values, and `W5D_SAMPLE_SEED` fixes the weekly 5% sample.

## Apache

Point the vhost `DocumentRoot` at the repository root. `.htaccess` does four things: serves real files
under `assets/`, denies `src/ config/ data/ lang/ db/ bin/ storage/ docs/ views/ tests/ .git`, denies
`.env .sql .log .md` and other sensitive file types, and rewrites every other request to `index.php`.
Each sensitive folder also carries its own `.htaccess` deny rule.

## Cron

```cron
0  * * * *     php /path/to/w5d/bin/rollup.php            # hourly
*/15 * * * 5   php /path/to/w5d/bin/rollup.php            # every 15 minutes on Fridays
0  3 * * *     php /path/to/w5d/bin/backup.php            # nightly (backup script: planned, see open items)
```

Overlapping rollup runs are safe: `bin/rollup.php` takes a MySQL named lock and every write is an
idempotent upsert.

## Deploy an update

```bash
git pull
php bin/migrate.php          # only if db/migrations changed
php bin/sync-content.php     # only if data/content changed
php bin/lint.php
```

## Local preview (development only)

```bash
php -S 0.0.0.0:8080 tests/serve.php      # from the repository root; mirrors the .htaccess rules
php tests/RouteMatrixTest.php            # every public route × 3 templates × 3 languages
php tests/FlowTest.php                   # registration, login, roles, rate limit, rollups, sampling
```

## Open items before public launch

- Counsel review of the registration consent text and the privacy inventory (`docs/design.md` gate).
- Real SMS and e-mail processors. Both adapters are stubs and send nothing (`src/Sms.php`).
- A backup script and a restore drill (`bin/backup.php` is not yet written).
- Human translation of the French and Arabic content. Placeholders are flagged on every page.

## Database drivers

| Setting | SQLite (default) | MySQL / MariaDB (optional) |
|---|---|---|
| `DB_DRIVER` | `sqlite` | `mysql` |
| Schema | `db/sqlite/*.sql` | `db/mysql/*.sql` |
| Location | `DB_SQLITE_PATH` (default `storage/w5d.sqlite`) | `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` |
| Write locking | `BEGIN IMMEDIATE` (one writer at a time) | row locks (`SELECT ... FOR UPDATE`) |
| Job lock | `job_locks` table, expiring | `job_locks` table, expiring |

Both drivers hold the same tables. `bin/lint.php` fails when the table lists differ or when MySQL-only SQL
appears outside `src/Sql.php`. Switching driver does not copy data: export and import through the
owner portal, or run `bin/migrate.php` on the new driver and re-register.

SQLite file permissions: the file and its `-wal` and `-shm` siblings must be owned by the PHP user and
must sit outside the web root. `storage/.htaccess` denies web access; keep `DB_SQLITE_PATH` inside
`storage/`. Back up with `sqlite3 storage/w5d.sqlite ".backup storage/backups/w5d-$(date +%F).sqlite"`.
