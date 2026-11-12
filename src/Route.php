<?php
declare(strict_types=1);

namespace W5D;

use W5D\Support\Text;

/**
 * Route — URL construction for a static, language-prefixed site.
 *
 * The default language lives at the site root; every other language is
 * prefixed (/fr/…, /ar/…). Paths are always produced here and never assembled
 * in a template, so a moved page cannot leave a stale link behind.
 *
 * Route::href() is used at build time (absolute, root-relative paths) and by
 * the browser-side language switcher, which reads the `data-route` attributes
 * written into the page by View.
 */
final class Route
{
    private array $config;

    /** @var array<string,string> */
    private array $paths = [];

    public function __construct(array $config, private readonly Content $content)
    {
        $this->config = $config;
        $this->index();
    }

    /** Build the id → path map once. */
    private function index(): void
    {
        foreach ($this->content->pages() as $id => $page) {
            $this->paths['page:' . $id] = $this->normalise((string) ($page['path'] ?? '/' . $id . '/'));
        }
        foreach ($this->content->macros() as $slug => $macro) {
            $this->paths['macro:' . $slug] = '/macro-works/' . $slug . '/';
        }
        foreach ($this->content->services() as $slug => $service) {
            $this->paths['service:' . $slug] = '/services/' . $slug . '/';
        }
        foreach ($this->content->projects() as $slug => $project) {
            $this->paths['project:' . $slug] = '/ecosystem/' . $slug . '/';
        }
        foreach ($this->content->declarations() as $slug => $declaration) {
            $this->paths['declaration:' . $slug] = '/declarations/' . $slug . '/';
        }
        foreach ($this->content->layers() as $key => $layer) {
            $this->paths['layer:' . $key] = '/ontology/' . $key . '/';
        }
        $this->paths['ontology:index'] = '/ontology/';
        $this->paths['services:index'] = '/services/';
        $this->paths['ecosystem:index'] = '/ecosystem/';
        $this->paths['declarations:index'] = '/declarations/';
    }

    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : $path . '/';
    }

    /** The path of a record in the default language, e.g. "/about/". */
    public function path(string $key): ?string
    {
        return $this->paths[$key] ?? null;
    }

    /** Every registered key → path, for the sitemap and the 404 suggestions. */
    public function all(): array
    {
        return $this->paths;
    }

    /**
     * Language-aware href.
     *   Route::href('page','about','fr') → "/fr/about/"
     */
    public static function href(string $kind, string $id, string $lang, ?string $base = null): string
    {
        $config = Registry::config();
        $base ??= (string) ($config['base_url'] ?? '/');
        $default = (string) ($config['default_lang'] ?? 'en');

        $route = Registry::route();
        $path = $route->path($kind . ':' . $id) ?? '/' . trim($kind, ':') . '/' . $id . '/';

        return self::compose($path, $lang, $default, $base);
    }

    /** Absolute path for a page id, no language prefix logic duplicated. */
    public static function page(string $id, string $lang): string
    {
        return self::href('page', $id, $lang);
    }

    public static function compose(string $path, string $lang, string $default, string $base): string
    {
        $prefix = $lang === $default ? '' : '/' . $lang;
        $path = '/' . ltrim($path, '/');
        $base = '/' . trim($base, '/');
        $base = $base === '/' ? '' : $base;

        $full = $base . $prefix . ($path === '/' ? '/' : $path);
        return $full === '' ? '/' : $full;
    }

    /** Absolute URL for canonical / og:url / sitemap. */
    public static function absolute(string $href): string
    {
        $config = Registry::config();
        $origin = rtrim((string) ($config['origin'] ?? ''), '/');
        if ($origin === '') {
            return $href;
        }
        return $origin . ($href === '/' ? '/' : $href);
    }

    /** Path to a static asset, optionally inside a template's asset folder. */
    public static function asset(string $file, ?string $template = null): string
    {
        $config = Registry::config();
        $base = (string) ($config['base_url'] ?? '/');
        $base = rtrim($base, '/');
        $template ??= (string) ($config['active_template'] ?? 'ledger');
        $file = ltrim($file, '/');

        // "_base" holds assets every template can rely on.
        if (str_starts_with($file, '_base/')) {
            return $base . '/assets/' . $file;
        }
        return $base . '/assets/' . $template . '/' . $file;
    }

    /** Where the export writes a given href (pretty URLs → dir/index.html). */
    public function outputPath(string $href, bool $pretty = true): string
    {
        $path = ltrim($href, '/');
        if ($path === '') {
            return 'index.html';
        }
        if (str_ends_with($path, '/')) {
            return $pretty ? $path . 'index.html' : rtrim($path, '/') . '.html';
        }
        if (str_contains(basename($path), '.')) {
            return $path;
        }
        return $pretty ? $path . '/index.html' : $path . '.html';
    }

    /** The page id that owns a path, used to resolve dev-server URLs. */
    public function resolve(string $path): ?array
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            $path = '/';
        }

        // Strip a language prefix.
        $lang = (string) ($this->config['default_lang'] ?? 'en');
        $langs = (array) ($this->config['langs'] ?? ['en']);
        $segments = array_values(array_filter(explode('/', $path), static fn ($s) => $s !== ''));
        if ($segments !== [] && in_array($segments[0], $langs, true) && $segments[0] !== $lang) {
            $lang = array_shift($segments);
        }

        $normalised = '/' . implode('/', $segments) . ($segments === [] ? '' : '/');
        if ($segments === []) {
            $normalised = '/';
        }

        foreach ($this->paths as $key => $candidate) {
            if ($candidate === $normalised) {
                [$kind, $id] = explode(':', $key, 2);
                return ['kind' => $kind, 'id' => $id, 'lang' => $lang];
            }
        }

        return null;
    }
}
