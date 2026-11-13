<?php
/**
 * Mirrors data/content/ into database tables for joins and search. The JSON files stay the source of truth.
 * Idempotent: run after every content change. Usage: php bin/sync-content.php
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

use W5D\Content;
use W5D\DB;
use W5D\Sql;

$n = ['macros' => 0, 'services' => 0, 'projects' => 0, 'layers' => 0, 'pages' => 0];
foreach (Content::all('macros') as $slug => $m) {
    DB::run(Sql::upsert('macros', ['slug', 'number', 'title_en'], ['slug'], ['number', 'title_en']),
        [$slug, (int) $m['number'], $m['title']['en']]);
    $n['macros']++;
}
foreach (Content::all('services') as $slug => $s) {
    DB::run(Sql::upsert('services', ['slug', 'number', 'macro_slug', 'title_en'], ['slug'], ['number', 'macro_slug', 'title_en']),
        [$slug, (int) $s['number'], $s['macro'], $s['title']['en']]);
    $n['services']++;
}
foreach (Content::all('projects') as $slug => $p) {
    DB::run(Sql::upsert('projects', ['slug', 'title_en', 'status', 'evidence'], ['slug'], ['title_en', 'status', 'evidence']),
        [$slug, $p['title']['en'], $p['status'], $p['evidence']]);
    $n['projects']++;
}
foreach (Content::all('layers') as $slug => $l) {
    DB::run(Sql::upsert('layers', ['slug', 'layer', 'title_en'], ['slug'], ['layer', 'title_en']),
        [$slug, (string) $l['layer'], $l['title']['en']]);
    $n['layers']++;
}
foreach (Content::all('pages') as $id => $p) {
    DB::run(Sql::upsert('pages', ['id', 'path', 'title_en'], ['id'], ['path', 'title_en']),
        [$id, (string) ($p['path'] ?? '/' . $id . '/'), $p['title']['en']]);
    $n['pages']++;
}
echo 'synced: ' . json_encode($n) . PHP_EOL;
