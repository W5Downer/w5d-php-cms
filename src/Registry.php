<?php
declare(strict_types=1);

namespace W5D;

use W5D\Support\Vars;

/**
 * Registry — the tiny service container the whole engine reads from.
 *
 * Static access is deliberate: helpers such as `v()` and `tr()` are called from
 * inside templates, and threading a container through every view would make the
 * templates noisier for no benefit. The container is booted once by
 * `src/bootstrap.php` and never mutated afterwards, except for the active
 * language while the builder walks the languages.
 */
final class Registry
{
    private static ?array $config = null;
    private static ?Vars $vars = null;
    private static ?Lang $lang = null;
    private static ?Content $content = null;
    private static ?Route $route = null;
    private static ?View $view = null;
    private static string $code = 'en';

    public static function boot(array $config): void
    {
        self::$config = $config;
        self::$code = (string) ($config['default_lang'] ?? 'en');
        self::$vars = Vars::load((string) $config['root'] . '/config', self::$code);
        self::$lang = Lang::boot((string) $config['root'], $config);
        self::$content = new Content((string) $config['content_dir'], $config, self::$vars);
        self::$route = new Route($config, self::$content);
        self::$view = new View((string) $config['root'], $config);
    }

    public static function config(?string $key = null): mixed
    {
        if (self::$config === null) {
            throw new \RuntimeException('Registry is not booted.');
        }
        if ($key === null) {
            return self::$config;
        }
        return self::$config[$key] ?? null;
    }

    public static function vars(): Vars
    {
        return self::$vars ?? throw new \RuntimeException('Registry is not booted.');
    }

    public static function lang(): Lang
    {
        return self::$lang ?? throw new \RuntimeException('Registry is not booted.');
    }

    public static function content(): Content
    {
        return self::$content ?? throw new \RuntimeException('Registry is not booted.');
    }

    public static function route(): Route
    {
        return self::$route ?? throw new \RuntimeException('Registry is not booted.');
    }

    public static function view(): View
    {
        return self::$view ?? throw new \RuntimeException('Registry is not booted.');
    }

    public static function code(): string
    {
        return self::$code;
    }

    /** Switch the active language for the next render. */
    public static function useLang(string $code): void
    {
        self::$code = $code;
        self::$lang?->set($code);
        self::$vars?->withLang($code);
        self::$vars = self::vars()->withLang($code);
    }
}
