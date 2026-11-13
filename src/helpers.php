<?php
/**
 * Global helpers used by views and controllers. Kept small on purpose.
 */

use W5D\Lang;
use W5D\Variables;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Translate a UI string (lang/{code}.php). */
function t(string $key, mixed ...$args): string
{
    return Lang::t($key, ...$args);
}

/**
 * Pick the active language from a {en,fr,ar} map. Falls back to English and records
 * the fallback (Lang::$fellBack) so the page can say so. Tokens are resolved here.
 */
function loc(mixed $value, ?string $lang = null): string
{
    if (is_scalar($value)) {
        return Variables::resolve((string) $value);
    }
    if (!is_array($value)) {
        return '';
    }
    $lang ??= Lang::current();
    $placeholder = $lang !== 'en' && isset($value[$lang], $value['en']) && $value[$lang] === $value['en'];
    if (isset($value[$lang]) && $value[$lang] !== '' && !$placeholder) {
        return Variables::resolve((string) $value[$lang]);
    }
    if ($lang !== 'en') {
        Lang::$fellBack = true;
    }
    return Variables::resolve((string) ($value['en'] ?? ''));
}

/** Resolve a {{ token }} path to its display form. */
function v(string $path): string
{
    return Variables::format($path);
}

function asset(string $path): string
{
    return '/assets/' . ltrim($path, '/') . '?v=' . W5D_ASSET_VERSION;
}

/** All three language strings for a UI key, as a {en,fr,ar} map (for form labels in blocks). */
function t_all(string $key): array
{
    $out = [];
    foreach (['en', 'fr', 'ar'] as $code) {
        $out[$code] = Lang::strings($code)[$key] ?? Lang::strings('en')[$key] ?? $key;
    }
    return $out;
}

/** English-first map helper for generated labels (no translation fallback flag). */
function tr_loc(array $map): array
{
    return ['en' => (string) ($map['en'] ?? '')] + $map;
}

/** Active template: one config value (config/app.php 'template'). Unknown values fall back to ledger. */
function active_template(): string
{
    $tpl = (string) W5D\Config::app('template', 'ledger');
    return in_array($tpl, W5D\Config::app('templates', []), true) ? $tpl : 'ledger';
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && $token !== '' && isset($_SESSION['csrf'])
        && hash_equals((string) $_SESSION['csrf'], $token);
}
