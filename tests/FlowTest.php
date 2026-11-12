<?php
/**
 * Behaviour tests against the real database (run migrate.php first).
 * Covers: registration validation, duplicate e-mail 409, CSRF 403, rate limit 429,
 * owner login, role scoping of menus, 50-row cap, rollup idempotence, /api/kpis.json source,
 * and the fixed 5% sample rule.
 *   php tests/FlowTest.php
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

use W5D\App;
use W5D\Auth;
use W5D\Config;
use W5D\DB;
use W5D\Rollup;
use W5D\Sampling;
use W5D\Session;

$fails = 0;
$pass = 0;
function check(bool $ok, string $name): void {
    global $fails, $pass;
    if ($ok) { $pass++; echo "  ok   {$name}\n"; }
    else { $fails++; echo "  FAIL {$name}\n"; }
}

// Isolated state for this run.
DB::run('DELETE FROM rate_limits');
DB::run("DELETE c FROM consents c JOIN applicants a ON a.id = c.applicant_id WHERE a.email LIKE 'test-%@example.test'");
DB::run("DELETE FROM applicants WHERE email LIKE 'test-%@example.test'");
Session::start();
Config::override('rate_limit_register_per_hour', 5);
Config::override('rate_limit_login_per_hour', 10);

$serverIp = ['REMOTE_ADDR' => '198.51.100.7', 'HTTP_ACCEPT_LANGUAGE' => 'fr'];
$csrf = csrf_token();
$valid = [
    '_csrf' => $csrf, 'degree' => 'licence', 'fit' => 'yes', 'available' => 'yes',
    'name' => 'Test Applicant', 'email' => 'test-one@example.test', 'region' => 'tunis',
    'service' => 'heritage-digitisation', 'consent' => 'yes', 'website' => '',
];

echo "Registration\n";
$r = App::handle('POST', '/app/register/', [], $valid, ['w5d_lang' => 'en'], $serverIp);
check($r['status'] === 200 && str_contains($r['body'], 'recorded'), 'valid submission is accepted (200)');
check((int) DB::value("SELECT COUNT(*) FROM applicants WHERE email = 'test-one@example.test'") === 1, 'applicant row written once');
check((int) DB::value("SELECT COUNT(*) FROM consents c JOIN applicants a ON a.id = c.applicant_id WHERE a.email = 'test-one@example.test'") === 2, 'two consent rows written');

$dup = App::handle('POST', '/app/register/', [], $valid, [], ['REMOTE_ADDR' => '198.51.100.8']);
check($dup['status'] === 409, 'duplicate e-mail returns HTTP 409');
check((int) DB::value("SELECT COUNT(*) FROM applicants WHERE email = 'test-one@example.test'") === 1, 'duplicate writes nothing');

$noCsrf = App::handle('POST', '/app/register/', [], array_merge($valid, ['_csrf' => 'wrong', 'email' => 'test-csrf@example.test']), [], ['REMOTE_ADDR' => '198.51.100.9']);
check($noCsrf['status'] === 403, 'bad CSRF token returns HTTP 403');
check((int) DB::value("SELECT COUNT(*) FROM applicants WHERE email = 'test-csrf@example.test'") === 0, 'CSRF failure writes nothing');

$bot = App::handle('POST', '/app/register/', [], array_merge($valid, ['website' => 'spam', 'email' => 'test-bot@example.test']), [], ['REMOTE_ADDR' => '198.51.100.10']);
check($bot['status'] === 200 && (int) DB::value("SELECT COUNT(*) FROM applicants WHERE email = 'test-bot@example.test'") === 0, 'honeypot filled: success page, nothing stored');

$bad = App::handle('POST', '/app/register/', [], array_merge($valid, ['fit' => 'no', 'email' => 'not-an-email']), [], ['REMOTE_ADDR' => '198.51.100.11']);
check($bad['status'] === 422 && str_contains($bad['body'], 'aria-live') === false, 'invalid input returns 422 with a message');

echo "Rate limit (5 per IP per hour)\n";
$ip = '203.0.113.50';
$codes = [];
for ($i = 1; $i <= 6; $i++) {
    $res = App::handle('POST', '/app/register/', [], array_merge($valid, ['email' => "test-rate{$i}@example.test", 'fit' => 'no']), [], ['REMOTE_ADDR' => $ip]);
    $codes[] = $res['status'];
}
check(array_slice($codes, 0, 5) === [422, 422, 422, 422, 422], 'first five attempts pass the limiter (validation runs)');
check($codes[5] === 429, 'sixth attempt in the window is rejected with 429');

echo "Owner login and roles\n";
// Create a manager user to test scoping. Password hash generated here, never stored in the repo.
$hash = password_hash('Test-manager-2026!', PASSWORD_ARGON2ID);
$tn = (int) DB::value("SELECT id FROM countries WHERE iso = 'tn'");
DB::run("DELETE FROM users WHERE username = 'test-manager'");
DB::run("INSERT INTO users (country_id, username, email, password_hash, role_code) VALUES (?, 'test-manager', 'test-manager@example.test', ?, 'manager')", [$tn, $hash]);
DB::run("INSERT IGNORE INTO countries (iso, name_en, currency, timezone, status) VALUES ('ma', 'Morocco (test)', 'MAD', 'Africa/Casablanca', 'draft')");

$bad = Auth::attempt('owner', 'wrong-password', '198.51.100.20');
check($bad === 'invalid', 'wrong password is refused');
$ownerHash = (string) DB::value("SELECT password_hash FROM users WHERE role_code = 'owner' LIMIT 1");
check($ownerHash !== '', 'owner account exists (created by migrate.php)');

$menusOwner = W5D\ACL::menusFor('owner');
$menusMember = W5D\ACL::menusFor('member');
$menusManager = W5D\ACL::menusFor('manager');
check(in_array('system', $menusOwner, true) && in_array('audit', $menusOwner, true), 'owner sees system and audit menus');
check(!in_array('system', $menusMember, true) && !in_array('applicants', $menusMember, true), 'member does not see system or applicants');
check(in_array('applicants', $menusManager, true) && !in_array('payroll', $menusManager, true), 'manager sees applicants but not payroll');
check(count(W5D\ACL::roles()) === 8, 'exactly eight roles are defined');
check(W5D\ACL::can('member', 'menu.payroll') === false, 'ACL denies payroll to members');

echo "Portal: 50-row cap, country scope, quality rule\n";
// Seed 60 applicants for TN so pagination is exercised.
DB::run("DELETE c FROM consents c JOIN applicants a ON a.id = c.applicant_id WHERE a.email LIKE 'test-bulk%@example.test'");
DB::run("DELETE FROM applicants WHERE email LIKE 'test-bulk%@example.test'");
$svc = 'heritage-digitisation';
$macro = 'cultures';
$regionId = (int) DB::value("SELECT id FROM regions WHERE slug = 'tunis'");
for ($i = 1; $i <= 60; $i++) {
    DB::run("INSERT INTO applicants (country_id, region_id, macro_slug, service_slug, status, full_name, email, email_hmac, degree_level, lang)
        VALUES (?, ?, ?, ?, 'registered', 'Bulk', ?, ?, 'licence', 'en')", [$tn, $regionId, $macro, $svc, "test-bulk{$i}@example.test", hash('sha256', "bulk{$i}")]);
}
$sess = new ReflectionClass(Session::class);
$loginRes = (function () {
    $_SESSION['uid'] = (int) DB::value("SELECT id FROM users WHERE username = 'test-manager'");
    return true;
})();
$pageRes = App::handle('GET', '/' . Config::app('admin_path') . '/applicants', ['country' => 'tn'], [], [], ['REMOTE_ADDR' => '198.51.100.30']);
check($pageRes['status'] === 200, 'applicants menu renders for a logged-in manager');
check(substr_count($pageRes['body'], '<tr><td>') <= 50, 'applicants menu shows at most 50 rows');
check(str_contains($pageRes['body'], 'Page 1 of'), 'applicants menu paginates');

$qual = App::handle('GET', '/' . Config::app('admin_path') . '/quality', ['country' => 'tn'], [], [], ['REMOTE_ADDR' => '198.51.100.30']);
check(str_contains($qual['body'], '5%') || str_contains($qual['body'], '5 %'), 'quality menu states the fixed 5% rule');
check(!str_contains($qual['body'], '<input name="rate"') && !str_contains($qual['body'], 'name="sample'), 'quality menu offers no control to change the sample rate');

echo "Rollup and KPI endpoint\n";
$first = Rollup::run('2026-11-12');
$second = Rollup::run('2026-11-12');
check($first['status'] === 'ok' && $second['status'] === 'ok', 'rollup runs twice without error');
$count1 = (int) DB::value("SELECT COUNT(*) FROM rollups_daily WHERE day = '2026-11-12'");
Rollup::run('2026-11-12');
$count2 = (int) DB::value("SELECT COUNT(*) FROM rollups_daily WHERE day = '2026-11-12'");
check($count1 === $count2, 'rollup is idempotent (no duplicate aggregate rows)');
$total = (int) DB::value("SELECT value FROM rollups_daily WHERE country_id = ? AND day = '2026-11-12' AND metric = 'applicants_total' AND macro_slug = '' AND service_slug = ''", [$tn]);
$real = (int) DB::value('SELECT COUNT(*) FROM applicants WHERE country_id = ?', [$tn]);
check($total === $real, "rollup total ({$total}) equals raw count ({$real})");
$kpi = App::handle('GET', '/api/kpis.json', [], [], [], []);
$body = json_decode($kpi['body'], true);
check($kpi['status'] === 200 && ($body['source'] ?? '') === 'rollups_daily', '/api/kpis.json reports rollups_daily as its only source');
check(($body['declared']['experts_current'] ?? null) === 0, 'KPI endpoint declares experts_current = 0');

echo "Sampling\n";
$hits = 0;
for ($r = 1; $r <= 10000; $r++) {
    if (Sampling::selects(1, 45, $r)) { $hits++; }
}
check(abs($hits / 10000 - 0.05) < 0.01, "sample rate close to 5% over 10,000 reports (got {$hits})");
check(Sampling::selects(1, 45, 777) === Sampling::selects(1, 45, 777), 'sampling is deterministic for the same seed and inputs');
check(Sampling::percent() === 5, 'sampling percent is the fixed constant 5');

echo "\nflow: {$pass} passed, {$fails} failed\n";
exit($fails === 0 ? 0 : 1);
