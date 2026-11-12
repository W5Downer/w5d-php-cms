<?php
declare(strict_types=1);

/**
 * W5D Site Engine — application configuration.
 *
 * Every value here can be overridden with an environment variable of the same
 * name in upper case prefixed with W5D_ (e.g. W5D_ACTIVE_TEMPLATE=atlas).
 * Nothing in this file is content: all content lives in data/content/*.json and
 * all figures/type vocabularies live in config/variables.php.
 */

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv('W5D_' . strtoupper($key));
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
};

$root = dirname(__DIR__);

return [
    // Absolute path to the project root.
    'root' => $root,

    // Where the built static site is written (upload the *contents* to a web root).
    'export_dir' => $env('export_dir', $root . '/dist'),

    // Content root. One JSON file per page, one per project/service/macro.
    'content_dir' => $root . '/data/content',

    // Public base path. "/" for a domain root (https://w5d.tech/).
    // Set to "/site/" when the build is uploaded into a sub-directory.
    'base_url' => $env('base_url', '/'),

    // Use relative links (works when opened from file:// or any sub-folder).
    'relative_urls' => filter_var($env('relative_urls', '0'), FILTER_VALIDATE_BOOL),

    // Templates live in views/{name}/ with assets in assets/{name}/.
    'active_template' => $env('active_template', 'ledger'),
    'templates' => ['ledger', 'atlas', 'folio'],

    // Languages. The first one is the default and lives at the site root.
    'default_lang' => $env('default_lang', 'en'),
    'langs' => ['en', 'fr', 'ar'],
    'rtl_langs' => ['ar'],
    'lang_names' => ['en' => 'English', 'fr' => 'Français', 'ar' => 'العربية'],
    'lang_dirs' => ['en' => 'ltr', 'fr' => 'ltr', 'ar' => 'rtl'],

    // Locale metadata for <html lang>, hreflang and og:locale.
    'locales' => [
        'en' => ['html' => 'en', 'og' => 'en_GB'],
        'fr' => ['html' => 'fr', 'og' => 'fr_FR'],
        'ar' => ['html' => 'ar', 'og' => 'ar_TN'],
    ],

    // Presentation locale used for numbers and dates in the static build.
    'format_locales' => ['en' => 'en_GB', 'fr' => 'fr_FR', 'ar' => 'ar_TN'],

    'timezone' => $env('timezone', 'Africa/Tunis'),

    // Canonical origin used for absolute URLs (sitemap, og:url, canonical).
    'origin' => $env('origin', 'https://w5d.tech'),

    // Preview mode: noindex + banner + no canonical URLs (pre-launch behaviour).
    'preview' => filter_var($env('preview', '0'), FILTER_VALIDATE_BOOL),

    // Cache directory for compiled views / rendered fragments.
    'cache_dir' => $root . '/storage/cache',

    // Admin (the dynamic CMS; not part of the static export).
    'admin_path' => $env('admin_path', 'admin'),
    'admin_session' => 'w5d_admin',
    'db_path' => $env('db_path', $root . '/storage/w5d.sqlite'),

    // Static build options.
    'build' => [
        // Languages exported. Remove a language to shrink the build.
        'langs' => ['en', 'fr', 'ar'],
        // Also write pretty URLs as directory/index.html (default) or .html files.
        'pretty_urls' => true,
        // Write sitemap.xml, robots.txt, 404.html and the security headers file.
        'sitemap' => true,
        'robots' => true,
        'security_headers' => true,
        // Fail the build instead of warning when a translation is missing.
        'strict_translations' => false,
    ],
];
