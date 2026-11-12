<?php
namespace W5D;

/** Read-only access to config/*.php. Cached per request. */
final class Config
{
    private static ?array $app = null;
    private static ?array $vars = null;
    private static ?array $vocab = null;

    public static function app(string $key, mixed $default = null): mixed
    {
        self::$app ??= require W5D_ROOT . '/config/app.php';
        return self::dig(self::$app, $key, $default);
    }

    /** Raw nested variable declarations (see Variables for resolution). */
    public static function variables(): array
    {
        if (self::$vars === null) {
            self::$vars = require W5D_ROOT . '/config/variables.php';
            $local = W5D_ROOT . '/config/variables.local.php';
            if (is_file($local)) {
                self::$vars = array_replace_recursive(self::$vars, require $local);
            }
        }
        return self::$vars;
    }

    /** Test/runtime override for a config/app.php value. Not persisted. */
    public static function override(string $key, mixed $value): void
    {
        self::app('name');
        self::$app = self::setDot(self::$app ?? [], $key, $value);
    }

    private static function setDot(array $arr, string $key, mixed $value): array
    {
        $ref = &$arr;
        foreach (explode('.', $key) as $part) {
            $ref[$part] ??= [];
            $ref = &$ref[$part];
        }
        $ref = $value;
        return $arr;
    }

    public static function vocab(string $key): array
    {
        self::$vocab ??= require W5D_ROOT . '/config/vocab.php';
        return self::$vocab[$key] ?? [];
    }

    public static function vocabAll(): array
    {
        self::$vocab ??= require W5D_ROOT . '/config/vocab.php';
        return self::$vocab;
    }

    /** Test hook: overlay variable declarations and drop the resolved cache. Never used by the web app. */
    public static function overrideVariables(array $overrides): void
    {
        self::variables();
        self::$vars = array_replace_recursive(self::$vars, $overrides);
        Variables::reset();
    }

    private static function dig(array $arr, string $key, mixed $default): mixed
    {
        $node = $arr;
        foreach (explode('.', $key) as $part) {
            if (!is_array($node) || !array_key_exists($part, $node)) {
                return $default;
            }
            $node = $node[$part];
        }
        return $node;
    }
}
