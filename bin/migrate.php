<?php
/**
 * Applies the schema for the active DB_DRIVER, records each file in schema_migrations, then seeds
 * the reference data (countries, regions, roles, permissions, services_country) and creates the
 * owner account from WEBMASTER_* settings when no user exists.
 *
 *   php bin/migrate.php                 apply pending migrations and seed (driver from DB_DRIVER, default sqlite)
 *   php bin/migrate.php --status        list applied migrations
 *   DB_DRIVER=mysql php bin/migrate.php uses db/mysql/ instead of db/sqlite/
 *
 * Each SQLite migration runs in one transaction (BEGIN IMMEDIATE ... COMMIT). MySQL DDL commits
 * implicitly, so there each statement runs on its own and a failure stops the run.
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Env;
use W5D\Sql;

$driver = DB::driver();
$pdo = DB::pdo();
echo "driver: {$driver}" . ($driver === 'sqlite' ? ' (' . DB::sqlitePath() . ')' : '') . PHP_EOL;

$tracking = $driver === 'sqlite'
    ? "CREATE TABLE IF NOT EXISTS schema_migrations (
  name TEXT NOT NULL PRIMARY KEY,
  applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)"
    : "CREATE TABLE IF NOT EXISTS schema_migrations (
  name VARCHAR(190) NOT NULL PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$pdo->exec($tracking);

if (in_array('--status', $argv, true)) {
    foreach (DB::all('SELECT name, applied_at FROM schema_migrations ORDER BY name') as $r) {
        echo $r['name'] . '  ' . $r['applied_at'] . PHP_EOL;
    }
    exit(0);
}

$dir = W5D_ROOT . '/db/' . $driver;
$files = glob($dir . '/*.sql') ?: [];
sort($files, SORT_STRING);
$applied = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (DB::value('SELECT 1 FROM schema_migrations WHERE name = ?', [$name])) {
        continue;
    }
    echo "applying {$driver}/{$name} ... ";
    $sql = (string) file_get_contents($file);
    if ($driver === 'sqlite') {
        // Triggers contain semicolons, so the whole file runs as one script inside one transaction.
        $pdo->exec('BEGIN IMMEDIATE');
        try {
            $pdo->exec($sql);
            DB::run('INSERT INTO schema_migrations (name) VALUES (?)', [$name]);
            $pdo->exec('COMMIT');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->exec('ROLLBACK');
            }
            echo "FAILED\n";
            throw $e;
        }
    } else {
        // MySQL: split on ; at line end. Comment lines are dropped first so a leading comment
        // does not hide the statement that follows it.
        $sql = implode("\n", array_filter(explode("\n", $sql), static fn ($l) => !str_starts_with(ltrim($l), '--')));
        foreach (array_filter(array_map('trim', preg_split('/;\s*$/m', $sql) ?: [])) as $stmt) {
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }
        DB::run('INSERT INTO schema_migrations (name) VALUES (?)', [$name]);
    }
    echo "ok\n";
    $applied++;
}
echo "migrations applied: {$applied}\n";

// ---------------------------------------------------------------- seed reference data (idempotent)
$roles = require W5D_ROOT . '/config/roles.php';
foreach ($roles['roles'] as $code => $def) {
    DB::run(Sql::upsert('roles', ['code', 'label_en'], ['code'], ['label_en']), [$code, $def['label']]);
    foreach ($def['perms'] as $perm) {
        DB::run(Sql::insertIgnore('permissions', ['code', 'label_en']), [$perm, $perm]);
        DB::run(Sql::insertIgnore('role_permissions', ['role_code', 'permission_code']), [$code, $perm]);
    }
}

foreach (Country::codes() as $iso) {
    $c = Country::get($iso);
    DB::run(Sql::upsert('countries', ['iso', 'name_en', 'currency', 'timezone', 'status'], ['iso'], ['name_en', 'currency', 'timezone']),
        [$iso, $c['names']['en'], $c['currency'], $c['timezone'], 'active']);
    $cid = (int) DB::value('SELECT id FROM countries WHERE iso = ?', [$iso]);
    foreach ($c['regions'] as $r) {
        DB::run(Sql::upsert('regions', ['country_id', 'slug', 'name_en'], ['country_id', 'slug'], ['name_en']),
            [$cid, $r['slug'], $r['name']['en']]);
    }
}

// Services are catalogue entries. Per-country activation starts inactive (country wizard rule).
foreach (Content::all('macros') as $slug => $m) {
    DB::run(Sql::upsert('macros', ['slug', 'number', 'title_en'], ['slug'], ['number', 'title_en']),
        [$slug, (int) $m['number'], $m['title']['en']]);
}
foreach (Content::all('services') as $slug => $s) {
    DB::run(Sql::upsert('services', ['slug', 'number', 'macro_slug', 'title_en'], ['slug'], ['number', 'macro_slug', 'title_en']),
        [$slug, (int) $s['number'], $s['macro'], $s['title']['en']]);
}
foreach (Content::all('projects') as $slug => $p) {
    DB::run(Sql::upsert('projects', ['slug', 'title_en', 'status', 'evidence'], ['slug'], ['title_en', 'status', 'evidence']),
        [$slug, $p['title']['en'], $p['status'], $p['evidence']]);
}
foreach (Content::all('layers') as $slug => $l) {
    DB::run(Sql::upsert('layers', ['slug', 'layer', 'title_en'], ['slug'], ['layer', 'title_en']),
        [$slug, (string) $l['layer'], $l['title']['en']]);
}
foreach (Content::all('pages') as $id => $p) {
    DB::run(Sql::upsert('pages', ['id', 'path', 'title_en'], ['id'], ['path', 'title_en']),
        [$id, (string) ($p['path'] ?? '/' . $id . '/'), $p['title']['en']]);
}
$cid = (int) DB::value('SELECT id FROM countries WHERE iso = ?', [Country::defaultIso()]);
foreach (array_keys(Content::all('services')) as $slug) {
    DB::run(Sql::insertIgnore('services_country', ['country_id', 'service_slug', 'active', 'seats']), [$cid, $slug, 0, 0]);
}
echo "seeded: roles, countries, regions, services, projects, layers\n";

// ---------------------------------------------------------------- owner account
$existing = (int) DB::value('SELECT COUNT(*) FROM users');
$hash = Env::get('WEBMASTER_PASSWORD_HASH');
if ($existing === 0 && $hash !== '') {
    DB::run('INSERT INTO users (country_id, username, email, password_hash, role_code) VALUES (NULL, ?, ?, ?, \'owner\')', [
        Env::get('WEBMASTER_USERNAME', 'owner'), Env::get('WEBMASTER_EMAIL', 'owner@' . Config::app('domain', 'w5d.tech')), $hash,
    ]);
    echo "owner account created\n";
} elseif ($existing === 0) {
    echo "no owner account: set WEBMASTER_PASSWORD_HASH in .env and run again\n";
}
echo "done\n";
