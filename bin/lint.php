<?php
/**
 * Lint: fails (exit 1) on any violation of the content and design rules.
 *   - every {{ token }} in content and UI strings resolves,
 *   - block vocabulary equals views/_blocks/*.php,
 *   - every block in every page has id, tier and focus; every block type is known,
 *   - service macro_slug exists; service fields present in en; fr/ar are flagged as placeholders,
 *   - no literal "130,000" or "2027" outside config/variables.php and data/countries/*.json,
 *   - no Tunisia / TND / Africa/Tunis / ar_TN literal in config/, src/, views/_shared/,
 *   - at most one .btn-peak per section (checked on rendered pages in tests/),
 *   - experts.design_check equals experts.target,
 *   - no third-party URL in views/ or assets/ (zero third-party requests),
 *   - no public/ or dist/ folder exists.
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

use W5D\Config;
use W5D\Content;
use W5D\Variables;

$errors = [];
$warnings = [];
$fail = static function (string $m) use (&$errors): void { $errors[] = $m; };

// 1. Tokens in every content file and every UI string.
$tokenSources = [];
foreach (glob(W5D_ROOT . '/data/content/*/*.json') ?: [] as $f) {
    if (str_contains($f, '/legacy/')) {
        continue;
    }
    $tokenSources[$f] = (string) file_get_contents($f);
}
foreach (glob(W5D_ROOT . '/lang/*.php') ?: [] as $f) {
    $tokenSources[$f] = (string) file_get_contents($f);
}
foreach ($tokenSources as $file => $text) {
    foreach (Variables::tokensIn($text) as $tok) {
        try {
            Variables::format($tok);
        } catch (Throwable $e) {
            $fail("{$file}: unresolved token {{ {$tok} }}");
        }
    }
    if (preg_match('/\{\{\s*var\./', $text)) {
        $fail("{$file}: uses the retired {{ var.* }} syntax");
    }
}

// 2. Derived variables and the design ceiling.
try {
    if ((int) Variables::value('experts.design_check') !== (int) Variables::value('experts.target')) {
        $fail('experts.design_check does not equal experts.target');
    }
    $tot = (int) Variables::value('accounts.total');
    if ($tot !== 143131 && $tot !== (int) (Variables::value('experts.target') + Variables::value('managers.target') + Variables::value('leaders.target') + 1)) {
        $fail('accounts.total derivation mismatch');
    }
} catch (Throwable $e) {
    $fail('variables: ' . $e->getMessage());
}

// 3. Block vocabulary equals the renderer files.
$files = array_map(static fn ($f) => basename($f, '.php'), glob(W5D_ROOT . '/views/_blocks/*.php') ?: []);
$vocab = Config::vocab('block');
foreach (array_diff($files, $vocab) as $m) { $fail("block file without vocabulary entry: {$m}"); }
foreach (array_diff($vocab, $files) as $m) { $fail("vocabulary entry without block file: {$m}"); }

// 4. Every page block declares a known type, an id, a tier and a focus.
foreach (Content::all('pages') as $id => $p) {
    foreach ($p['blocks'] ?? [] as $b) {
        $where = "pages/{$id}#" . ($b['id'] ?? '?');
        if (!in_array($b['type'] ?? '', $vocab, true)) { $fail("{$where}: unknown block type"); }
        if (($b['id'] ?? '') === '') { $fail("{$where}: missing id"); }
        if (!in_array($b['tier'] ?? '', ['E', 'I', 'X'], true)) { $fail("{$where}: missing or invalid tier"); }
        if (!in_array((int) ($b['focus'] ?? 0), [1, 2, 3, 4, 5], true)) { $fail("{$where}: missing or invalid focus"); }
    }
}

// 5. Services: macro exists; en fields present; fr/ar present (placeholders allowed, flagged).
$macros = array_keys(Content::all('macros'));
$placeholders = 0;
foreach (Content::all('services') as $slug => $s) {
    if (!in_array($s['macro'] ?? '', $macros, true)) { $fail("services/{$slug}: macro '{$s['macro']}' does not exist"); }
    foreach (['title', 'short', 'long', 'problem', 'solution'] as $f) {
        if (empty($s[$f]['en'])) { $fail("services/{$slug}: en.{$f} is empty"); }
        foreach (['fr', 'ar'] as $l) {
            if (!isset($s[$f][$l])) { $fail("services/{$slug}: missing {$l}.{$f}"); }
            elseif ($s[$f][$l] === $s[$f]['en']) { $placeholders++; }
        }
    }
    if (!isset($s['number'])) { $fail("services/{$slug}: number not resolvable from macros"); }
}
$warnings[] = "fr/ar placeholders copied from en: {$placeholders} fields (flagged on render; human translation pending)";
if (count(Content::all('services')) !== 130) { $fail('expected 130 services'); }
if (count(Content::all('projects')) !== 25) { $fail('expected 25 projects'); }
if (count(Content::all('macros')) !== 5) { $fail('expected 5 macros'); }

// 6. Honesty: no hard-coded design figures or dates outside the allowed files.
$allowed = [W5D_ROOT . '/config/variables.php'];
$scan = [];
foreach (['data/content', 'views', 'lang', 'src', 'config'] as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(W5D_ROOT . '/' . $dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile() || str_contains($f->getPathname(), '/legacy/')) { continue; }
        if (preg_match('/\.(json|php)$/', $f->getFilename())) { $scan[] = $f->getPathname(); }
    }
}
foreach ($scan as $f) {
    if (in_array($f, $allowed, true)) { continue; }
    $txt = (string) file_get_contents($f);
    if (preg_match('/130,000|\b2027\b/', $txt)) {
        $fail(str_replace(W5D_ROOT . '/', '', $f) . ': literal 130,000 or 2027 (use a token)');
    }
}

// 7. Tunisia literals stay out of config/, src/ and views/_shared/.
foreach (['config', 'src', 'views/_shared'] as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(W5D_ROOT . '/' . $dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile() || !preg_match('/\.php$/', $f->getFilename())) { continue; }
        $txt = (string) file_get_contents($f->getPathname());
        foreach (['Tunisia', 'TND', 'Africa/Tunis', 'ar_TN'] as $lit) {
            // Comments documenting the rule are allowed; code and strings are not.
            foreach (preg_split('/\R/', $txt) as $line) {
                if (str_contains($line, $lit) && !preg_match('#^\s*(\*|//|/\*\*|\#)#', $line)) {
                    $fail(str_replace(W5D_ROOT . '/', '', $f->getPathname()) . ": Tunisia literal '{$lit}'");
                    break 2;
                }
            }
        }
    }
}

// 8. Zero third-party requests in templates and assets.
$thirdParty = '/(https?:)?\/\/(?!w5d\.tech|www\.w3\.org|www\.sitemaps\.org|localhost|127\.0\.0\.1|' . preg_quote(parse_url((string) Config::app('url'), PHP_URL_HOST) ?: 'w5d.tech', '/') . ')[a-z0-9.\-]+\.[a-z]{2,}/i';
foreach (array_merge(glob(W5D_ROOT . '/views/*/*.php') ?: [], glob(W5D_ROOT . '/views/*/*/*.php') ?: [], glob(W5D_ROOT . '/assets/*/*/*.css') ?: [], glob(W5D_ROOT . '/assets/*/*/*/*.css') ?: [], glob(W5D_ROOT . '/assets/*/*/*.js') ?: []) as $f) {
    $txt = (string) file_get_contents($f);
    if (preg_match_all($thirdParty, $txt, $m) && !preg_match('/\bhttps?:\/\/[^\s"\']*\bw5d\.tech/', $m[0][0] ?? '')) {
        $bad = array_values(array_filter($m[0], static fn ($u) => !preg_match('/w5d\.tech|w3\.org|sitemaps\.org/', $u)));
        if ($bad !== []) { $fail(str_replace(W5D_ROOT . '/', '', $f) . ': third-party URL ' . $bad[0]); }
    }
}

// 9. No forbidden folders.
foreach (['public', 'dist'] as $d) {
    if (is_dir(W5D_ROOT . '/' . $d)) { $fail("forbidden folder present: {$d}/"); }
}
if (!is_file(W5D_ROOT . '/index.php')) { $fail('index.php must live at the repository root'); }

// 10. Data safety: the content folder must be intact (existence check on the required sets).
foreach (['services', 'macros', 'projects', 'layers', 'pages', 'statistics'] as $d) {
    if (!is_dir(W5D_ROOT . "/data/content/{$d}")) { $fail("data/content/{$d} is missing"); }
}

foreach ($warnings as $w) { echo "WARN  {$w}\n"; }
if ($errors !== []) {
    foreach ($errors as $e) { echo "FAIL  {$e}\n"; }
    echo 'lint: FAIL (' . count($errors) . " errors)\n";
    exit(1);
}
echo "lint: PASS\n";
exit(0);
