<?php
namespace W5D;

/**
 * Languages: cookie w5d_lang -> Accept-Language -> default. No ?lang= URLs.
 * UI chrome lives in lang/{code}.php. Content maps are picked in helpers.php (tr()).
 */
final class Lang
{
    private static ?string $current = null;
    /** @var array<string,array<string,string>> */
    private static array $strings = [];
    /** Set true when a content string fell back to English on a non-English page. */
    public static bool $fellBack = false;

    public static function codes(): array
    {
        return Config::app('languages', ['en']);
    }

    public static function detect(array $cookies, string $acceptLanguage = ''): string
    {
        $supported = self::codes();
        $cookie = $cookies['w5d_lang'] ?? '';
        if (in_array($cookie, $supported, true)) {
            return $cookie;
        }
        foreach (explode(',', $acceptLanguage) as $part) {
            $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
            if (in_array($code, $supported, true)) {
                return $code;
            }
        }
        return (string) Config::app('default_language', 'en');
    }

    public static function set(string $code): void
    {
        self::$current = in_array($code, self::codes(), true) ? $code : 'en';
        self::$fellBack = false;
    }

    public static function current(): string
    {
        return self::$current ?? (string) Config::app('default_language', 'en');
    }

    public static function isRtl(?string $code = null): bool
    {
        return in_array($code ?? self::current(), Config::app('rtl_languages', []), true);
    }

    public static function strings(string $code): array
    {
        if (!isset(self::$strings[$code])) {
            $file = W5D_ROOT . '/lang/' . $code . '.php';
            self::$strings[$code] = is_file($file) ? require $file : [];
        }
        return self::$strings[$code];
    }

    /** UI string lookup with English fallback. Missing keys are visible, never silent. */
    public static function t(string $key, mixed ...$args): string
    {
        $msg = self::strings(self::current())[$key] ?? self::strings('en')[$key] ?? "[[{$key}]]";
        return $args ? vsprintf($msg, $args) : $msg;
    }
}
