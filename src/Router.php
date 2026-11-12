<?php
namespace W5D;

/**
 * Route table. Paths are canonical with a trailing slash (except "/").
 * match() returns [handler, params] or null. link() maps route keys
 * (page:about, service:x ...) to URLs; unknown keys return null so the caller
 * renders a visible "pending" link instead of a silent 404.
 */
final class Router
{
    /** Static content pages: canonical path => content page id (data/content/pages/{id}.json). */
    public const PAGES = [
        '/' => 'home',
        '/join' => 'join',
        '/join/eligibility' => 'join-eligibility',
        '/join/thanks' => 'join-thanks',
        '/how-it-works' => 'how-it-works',
        '/countries/tunisia' => 'countries-tunisia',
        '/partners' => 'partners',
        '/investors' => 'investors',
        '/about' => 'about',
        '/ontology' => 'ontology',
        '/careers' => 'careers',
        '/faq' => 'faq',
        '/contact' => 'contact',
        '/status' => 'status',
        '/trust/statement' => 'trust-statement',
        '/trust/statistics' => 'trust-statistics',
        '/trust/corrections' => 'trust-corrections',
    ];

    /** Index pages generated from content collections. */
    public const INDEXES = [
        '/macro-works' => 'macro-index',
        '/services' => 'services-index',
        '/projects' => 'projects-index',
    ];

    public static function canonicalUrl(string $path): string
    {
        // Query strings and fragments are kept intact: "/app/register/?service=x" stays valid.
        [$base, $suffix] = array_pad(preg_split('/(?=[?#])/', $path, 2) ?: [$path, ''], 2, '');
        $p = '/' . trim($base, '/');
        return ($p === '/' ? '/' : $p . '/') . $suffix;
    }

    /** @return array{0:string,1:array<string,string>}|null */
    public static function match(string $method, string $path): ?array
    {
        $p = '/' . trim($path, '/');
        if ($p === '/') {
            return ['page', ['id' => 'home']];
        }
        $admin = '/' . trim((string) Config::app('admin_path', 'panel-x9z'), '/');
        if ($p === $admin || str_starts_with($p, $admin . '/')) {
            return ['portal', ['path' => substr($p, strlen($admin)) ?: '/']];
        }
        if ($p === '/contact' || $p === '/trust/corrections') {
            return ['form', ['id' => substr($p, 1)]];
        }
        if (isset(self::PAGES[$p])) {
            return ['page', ['id' => self::PAGES[$p]]];
        }
        if (isset(self::INDEXES[$p])) {
            return ['index', ['id' => self::INDEXES[$p]]];
        }
        if ($p === '/app/register') {
            return ['register', []];
        }
        if (preg_match('#^/language/([a-z]{2})$#', $p, $m)) {
            return ['language', ['code' => $m[1]]];
        }
        if (preg_match('#^/macro-works/([a-z\-]+)$#', $p, $m) && Content::get('macros', $m[1])) {
            return ['macro', ['slug' => $m[1]]];
        }
        if (preg_match('#^/services/([a-z0-9\-]+)$#', $p, $m) && Content::get('services', $m[1])) {
            return ['service', ['slug' => $m[1]]];
        }
        if (preg_match('#^/projects/([a-z0-9\-]+)$#', $p, $m) && Content::get('projects', $m[1])) {
            return ['project', ['slug' => $m[1]]];
        }
        if (preg_match('#^/ontology/([a-z\-]+)$#', $p, $m) && Content::get('layers', $m[1])) {
            return ['layer', ['slug' => $m[1]]];
        }
        if (preg_match('#^/' . preg_quote(Country::defaultIso(), '#') . '/([a-z\-]+)$#', $p, $m)
            && Country::region(Country::defaultIso(), $m[1]) !== null) {
            return ['governorate', ['slug' => $m[1]]];
        }
        return match ($p) {
            '/api/kpis.json' => ['kpis', []],
            '/sitemap.xml' => ['sitemap', []],
            '/robots.txt' => ['robots', []],
            '/404' => ['notfound', []],
            default => null,
        };
    }

    /** Resolve an internal link target. Returns null when the target does not exist. */
    public static function link(string $target): ?string
    {
        if (preg_match('#^(https?://|mailto:|tel:)#', $target) || str_starts_with($target, '#')) {
            return $target;
        }
        if (str_starts_with($target, '/')) {
            return self::canonicalUrl($target);
        }
        if (!str_contains($target, ':')) {
            return null;
        }
        [$kind, $key] = explode(':', $target, 2);
        return match ($kind) {
            'page' => ($path = array_search($key, self::PAGES, true)) !== false ? self::canonicalUrl((string) $path) : null,
            'service' => Content::get('services', $key) ? "/services/{$key}/" : null,
            'macro' => Content::get('macros', $key) ? "/macro-works/{$key}/" : null,
            'project' => Content::get('projects', $key) ? "/projects/{$key}/" : null,
            'layer' => Content::get('layers', $key) ? "/ontology/{$key}/" : null,
            'register' => '/app/register/' . ($key !== '' ? '?service=' . rawurlencode($key) : ''),
            default => null,
        };
    }

    /** Every public canonical path. Used by the sitemap, the route matrix test and lint. */
    public static function publicPaths(): array
    {
        $paths = ['/'];
        foreach (array_keys(self::PAGES) as $p) {
            $paths[] = self::canonicalUrl($p);
        }
        foreach (array_keys(self::INDEXES) as $p) {
            $paths[] = self::canonicalUrl($p);
        }
        $paths[] = '/app/register/';
        foreach (array_keys(Content::all('macros')) as $s) {
            $paths[] = "/macro-works/{$s}/";
        }
        foreach (array_keys(Content::all('services')) as $s) {
            $paths[] = "/services/{$s}/";
        }
        foreach (array_keys(Content::all('projects')) as $s) {
            $paths[] = "/projects/{$s}/";
        }
        foreach (array_keys(Content::all('layers')) as $s) {
            $paths[] = "/ontology/{$s}/";
        }
        foreach (array_keys(Country::regions(Country::defaultIso())) as $s) {
            $paths[] = '/' . Country::defaultIso() . "/{$s}/";
        }
        return array_values(array_unique($paths));
    }
}
