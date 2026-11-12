<?php
namespace W5D;

/**
 * Minimal .env loader. Values come from the file first, then the process environment.
 * The .env file itself is denied by .htaccess and must never be committed.
 */
final class Env
{
    /** @var array<string,string> */
    private static array $vars = [];

    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            self::$vars[$key] = trim($value, "\"'");
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        $env = getenv($key);
        if ($env !== false && $env !== '') {
            return (string) $env;
        }
        return (isset(self::$vars[$key]) && self::$vars[$key] !== '') ? self::$vars[$key] : $default;
    }

    public static function bool(string $key, bool $default): bool
    {
        $raw = strtolower(self::get($key, $default ? '1' : '0'));
        return in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }
}
