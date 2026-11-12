<?php
declare(strict_types=1);

/**
 * bin/build.php — static site generator.
 *
 * Renders every page, in every language, in the active template, and writes a
 * directory that can be uploaded directly to a web root (https://w5d.tech/).
 * No installer, no database, no SSH step: the output is plain HTML/CSS/JS.
 *
 *   php bin/build.php                 → build with config/app.php
 *   php bin/build.php --template=atlas
 *   php bin/build.php --langs=en,fr
 *   php bin/build.php --preview=1
 *   php bin/build.php --out=/tmp/site
 *
 * Output (default dist/):
 *   index.html                 en home
 *   fr/index.html  ar/index.html
 *   about/index.html …
 *   services/{slug}/index.html …
 *   assets/{template}/…  assets/_base/…
 *   404.html  robots.txt  sitemap.xml  _headers  site.webmanifest
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

use W5D\Content;
use W5D\Registry;
use W5D\Route;
use W5D\Site;

// ── Options ───────────────────────────────────────────────────────────────
$options = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $arg, $m)) {
        $options[$m[1]] = $m[2] ?? '1';
    }
}

$config = Registry::config();
$content = Registry::content();
$site = new Site((string) $config['root'], $config);
$view = Registry::view();

$outDir = rtrim((string) ($options['out'] ?? $config['export_dir']), '/');
$template = (string) ($options['template'] ?? $config['active_template']);
$langs = isset($options['langs'])
    ? array_values(array_filter(array_map('trim', explode(',', (string) $options['langs']))))
    : (array) ($config['build']['langs'] ?? $config['langs']);
$preview = (bool) ($options['preview'] ?? $config['preview']);
$pretty = (bool) ($config['build']['pretty_urls'] ?? true);

$view->setTemplate($template);
$view->setCache(false);

// ── Helpers ───────────────────────────────────────────────────────────────
$written = 0;
$errors = [];

$write = static function (string $relative, string $body) use ($outDir, &$written): void {
    $target = $outDir . '/' . ltrim($relative, '/');
    $dir = dirname($target);
    if (!is_dir($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
        throw new RuntimeException("Cannot create {$dir}");
    }
    if (file_put_contents($target, $body) === false) {
        throw new RuntimeException("Cannot write {$target}");
    }
    $written++;
};

$copyTree = static function (string $from, string $to) use (&$written): void {
    if (!is_dir($from)) {
        return;
    }
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($items as $item) {
        /** @var SplFileInfo $item */
        $target = $to . '/' . substr($item->getPathname(), strlen($from) + 1);
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0o775, true);
            }
            continue;
        }
        $dir = dirname($target);
        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }
        copy($item->getPathname(), $target);
        $written++;
    }
};

// ── Clean the output directory (only files this script owns) ──────────────
if (is_dir($outDir)) {
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        /** @var SplFileInfo $item */
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
}

// ── Collect the documents ─────────────────────────────────────────────────
/**
 * @return list<array{key:string,doc:array}>
 */
$documents = static function (string $lang) use ($content, $site): array {
    $docs = [];

    // Home first: the site root.
    $home = $site->documentForPage('index', $lang);
    if ($home !== null) {
        $docs[] = ['key' => 'page:index', 'doc' => $home];
    }

    foreach ($content->pages() as $id => $page) {
        if ($id === 'index') {
            continue;
        }
        // These three are generated from their collections, not from the page file.
        if (in_array($id, ['ecosystem', 'services', 'declarations'], true)) {
            continue;
        }
        $doc = $site->documentForPage((string) $id, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'page:' . $id, 'doc' => $doc];
        }
    }

    // Derived indexes.
    $docs[] = ['key' => 'page:ecosystem', 'doc' => $site->ecosystemIndexDocument($lang)];
    $docs[] = ['key' => 'page:services', 'doc' => $site->servicesIndexDocument($lang)];
    $docs[] = ['key' => 'page:declarations', 'doc' => $site->declarationsIndexDocument($lang)];

    foreach ($content->macros() as $slug => $_) {
        $doc = $site->documentForMacro((string) $slug, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'macro:' . $slug, 'doc' => $doc];
        }
    }

    foreach ($content->services() as $slug => $_) {
        $doc = $site->documentForService((string) $slug, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'service:' . $slug, 'doc' => $doc];
        }
    }

    foreach ($content->projects() as $slug => $_) {
        $doc = $site->documentForProject((string) $slug, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'project:' . $slug, 'doc' => $doc];
        }
    }

    foreach ($content->declarations() as $slug => $_) {
        $doc = $site->documentForDeclaration((string) $slug, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'declaration:' . $slug, 'doc' => $doc];
        }
    }

    foreach ($content->layers() as $key => $_) {
        $doc = $site->documentForLayer((string) $key, $lang);
        if ($doc !== null) {
            $docs[] = ['key' => 'layer:' . $key, 'doc' => $doc];
        }
    }

    return $docs;
};

// ── Build ─────────────────────────────────────────────────────────────────
$started = microtime(true);
$route = Registry::route();
$urls = [];

foreach ($langs as $lang) {
    Registry::useLang($lang);
    $view->setTemplate($template);

    foreach ($documents($lang) as $entry) {
        $doc = $entry['doc'];

        // The 404 page is exported once, at the root, not per language.
        if (($doc['id'] ?? '') === '404') {
            continue;
        }

        try {
            $html = $site->render($doc, $lang, $template);
        } catch (Throwable $e) {
            $errors[] = "[{$lang}] {$entry['key']}: " . $e->getMessage();
            continue;
        }

        $kind = $doc['route_kind'] ?? 'page';
        $path = $route->path($kind . ':' . $doc['id']) ?? '/' . $doc['id'] . '/';
        $href = Route::compose($path, $lang, (string) $config['default_lang'], (string) $config['base_url']);
        $relative = $route->outputPath($href, $pretty);

        $write($relative, $html);
        $urls[] = ['href' => $href, 'lang' => $lang, 'title' => $doc['title'], 'description' => $doc['description']];
    }
}

// ── 404 (one file, default language) ─────────────────────────────────────
Registry::useLang((string) $config['default_lang']);
$view->setTemplate($template);
try {
    $notFound = $site->documentForPage('404', (string) $config['default_lang']);
    if ($notFound !== null) {
        $write('404.html', $site->render($notFound, (string) $config['default_lang'], $template));
    }
} catch (Throwable $e) {
    $errors[] = '404: ' . $e->getMessage();
}

// ── Assets ────────────────────────────────────────────────────────────────
$copyTree((string) $config['root'] . '/assets/_base', $outDir . '/assets/_base');
$copyTree((string) $config['root'] . '/assets/' . $template, $outDir . '/assets/' . $template);

// ── robots.txt ────────────────────────────────────────────────────────────
if (!empty($config['build']['robots'])) {
    $origin = rtrim((string) $config['origin'], '/');
    $robots = $preview
        ? "User-agent: *\nDisallow: /\n\n# Preview build: not indexed.\n"
        : "User-agent: *\nAllow: /\n\nSitemap: {$origin}/sitemap.xml\n";
    $write('robots.txt', $robots);
}

// ── sitemap.xml ───────────────────────────────────────────────────────────
if (!empty($config['build']['sitemap']) && !$preview) {
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">\n";
    foreach ($urls as $url) {
        $loc = htmlspecialchars(Route::absolute($url['href']), ENT_XML1);
        $xml .= "  <url>\n    <loc>{$loc}</loc>\n";
        foreach ($langs as $code) {
            $alt = Route::compose(
                $url['href'] === '/' ? '/' : '/' . ltrim($url['href'], '/'),
                $code,
                (string) $config['default_lang'],
                (string) $config['base_url']
            );
            // Replace the language prefix if present.
            $altPath = preg_replace('#^/' . (string) $config['default_lang'] . '/#', '/', (string) $url['href']);
            $alt = Route::compose((string) $altPath, (string) $code, (string) $config['default_lang'], (string) $config['base_url']);
            $xml .= '    <xhtml:link rel="alternate" hreflang="' . htmlspecialchars((string) $code, ENT_XML1) . '" href="'
                . htmlspecialchars(Route::absolute($alt), ENT_XML1) . "\" />\n";
        }
        $xml .= "  </url>\n";
    }
    $xml .= "</urlset>\n";
    $write('sitemap.xml', $xml);
}

// ── Security headers (Netlify/Cloudflare Pages format) ────────────────────
if (!empty($config['build']['security_headers'])) {
    $headers = <<<'TXT'
/*
  X-Content-Type-Options: nosniff
  X-Frame-Options: SAMEORIGIN
  Referrer-Policy: strict-origin-when-cross-origin
  Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()
  Cross-Origin-Opener-Policy: same-origin
  Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; font-src 'self'; connect-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'self'

/assets/*
  Cache-Control: public, max-age=31536000, immutable

/*.html
  Cache-Control: public, max-age=600, must-revalidate

TXT;
    $write('_headers', $headers);
}

// ── Web app manifest ─────────────────────────────────────────────────────
$manifest = [
    'name' => (string) (Registry::vars()->value('site.name_long') ?? 'W5D'),
    'short_name' => (string) (Registry::vars()->value('site.name') ?? 'W5D'),
    'description' => (string) (Registry::vars()->value('site.tagline') ?? ''),
    'start_url' => (string) $config['base_url'],
    'display' => 'standalone',
    'background_color' => '#070711',
    'theme_color' => '#070711',
    'icons' => [
        ['src' => Route::asset('img/favicon.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml'],
    ],
];
$write('site.webmanifest', (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

// ── Report ────────────────────────────────────────────────────────────────
$elapsed = round((microtime(true) - $started) * 1000);
$missing = Registry::vars()->missing();

printf("\nW5D build\n");
printf("  template : %s\n", $template);
printf("  languages: %s\n", implode(', ', $langs));
printf("  output   : %s\n", $outDir);
printf("  pages    : %d files in %d ms\n", $written, $elapsed);

if ($missing !== []) {
    printf("\n  WARNING: %d unresolved variable token(s):\n", count($missing));
    foreach (array_slice($missing, 0, 12) as $token) {
        printf("    - %s\n", $token);
    }
    printf("  Fix them in config/variables.php (or config/variables.local.php).\n");
    printf("  Unresolved tokens render as an em dash, never as a wrong number.\n");
}

if ($errors !== []) {
    printf("\n  %d render error(s):\n", count($errors));
    foreach ($errors as $error) {
        printf("    ! %s\n", $error);
    }
    exit(1);
}

printf("\n  Upload the *contents* of %s to the web root.\n", $outDir);
printf("  No installer, no database, no SSH command.\n\n");
exit(0);
