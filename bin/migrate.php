<?php
/**
 * Applies db/migrations/*.sql in order, records each in schema_migrations, then seeds the
 * reference data (countries, regions, roles, permissions, services_country) and creates the
 * owner account from WEBMASTER_* settings when no user exists.
 *
 *   php bin/migrate.php            apply pending migrations and seed
 *   php bin/migrate.php --status   list applied migrations
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Env;

$pdo = DB::pdo();
$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
  name VARCHAR(190) NOT NULL PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

if (in_array('--status', $argv, true)) {
    foreach (DB::all('SELECT name, applied_at FROM schema_migrations ORDER BY name') as $r) {
        echo $r['name'] . '  ' . $r['applied_at'] . PHP_EOL;
    }
    exit(0);
}

$files = glob(W5D_ROOT . '/db/migrations/*.sql') ?: [];
sort($files, SORT_STRING);
$applied = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (DB::value('SELECT 1 FROM schema_migrations WHERE name = ?', [$name])) {
        continue;
    }
    echo "applying {$name} ... ";
    $sql = (string) file_get_contents($file);
    // Split on ; at line end. Migrations are plain DDL with no semicolons inside string literals.
    // Drop comment lines first so a leading comment does not hide the statement that follows it.
    $sql = implode("\n", array_filter(explode("\n", $sql), static fn ($l) => !str_starts_with(ltrim($l), '--')));
    foreach (array_filter(array_map('trim', preg_split('/;\s*$/m', $sql) ?: [])) as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
    DB::run('INSERT INTO schema_migrations (name) VALUES (?)', [$name]);
    echo "ok\n";
    $applied++;
}
echo "migrations applied: {$applied}\n";

// ---------------------------------------------------------------- seed reference data (idempotent)
$roles = require W5D_ROOT . '/config/roles.php';
foreach ($roles['roles'] as $code => $def) {
    DB::run('INSERT INTO roles (code, label_en) VALUES (?, ?) ON DUPLICATE KEY UPDATE label_en = VALUES(label_en)', [$code, $def['label']]);
    foreach ($def['perms'] as $perm) {
        DB::run('INSERT IGNORE INTO permissions (code, label_en) VALUES (?, ?)', [$perm, $perm]);
        DB::run('INSERT IGNORE INTO role_permissions (role_code, permission_code) VALUES (?, ?)', [$code, $perm]);
    }
}

foreach (Country::codes() as $iso) {
    $c = Country::get($iso);
    DB::run('INSERT INTO countries (iso, name_en, currency, timezone, status) VALUES (?, ?, ?, ?, \'active\')
        ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), currency = VALUES(currency), timezone = VALUES(timezone)',
        [$iso, $c['names']['en'], $c['currency'], $c['timezone']]);
    $cid = (int) DB::value('SELECT id FROM countries WHERE iso = ?', [$iso]);
    foreach ($c['regions'] as $r) {
        DB::run('INSERT INTO regions (country_id, slug, name_en) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE name_en = VALUES(name_en)', [$cid, $r['slug'], $r['name']['en']]);
    }
}

// Services are catalogue entries. Per-country activation starts inactive (country wizard rule).
foreach (Content::all('macros') as $slug => $m) {
    DB::run('INSERT INTO macros (slug, number, title_en) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE number = VALUES(number), title_en = VALUES(title_en)',
        [$slug, (int) $m['number'], $m['title']['en']]);
}
foreach (Content::all('services') as $slug => $s) {
    DB::run('INSERT INTO services (slug, number, macro_slug, title_en) VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE number = VALUES(number), macro_slug = VALUES(macro_slug), title_en = VALUES(title_en)',
        [$slug, (int) $s['number'], $s['macro'], $s['title']['en']]);
}
foreach (Content::all('projects') as $slug => $p) {
    DB::run('INSERT INTO projects (slug, title_en, status, evidence) VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE title_en = VALUES(title_en), status = VALUES(status), evidence = VALUES(evidence)',
        [$slug, $p['title']['en'], $p['status'], $p['evidence']]);
}
foreach (Content::all('layers') as $slug => $l) {
    DB::run('INSERT INTO layers (slug, layer, title_en) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE layer = VALUES(layer), title_en = VALUES(title_en)',
        [$slug, (string) $l['layer'], $l['title']['en']]);
}
foreach (Content::all('pages') as $id => $p) {
    DB::run('INSERT INTO pages (id, path, title_en) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE path = VALUES(path), title_en = VALUES(title_en)',
        [$id, (string) ($p['path'] ?? '/' . $id . '/'), $p['title']['en']]);
}
$cid = (int) DB::value('SELECT id FROM countries WHERE iso = ?', [Country::defaultIso()]);
foreach (array_keys(Content::all('services')) as $slug) {
    DB::run('INSERT IGNORE INTO services_country (country_id, service_slug, active, seats) VALUES (?, ?, 0, 0)', [$cid, $slug]);
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
