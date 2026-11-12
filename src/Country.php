<?php
namespace W5D;

use RuntimeException;

/**
 * Country entity. Every country-specific fact is read from data/countries/{iso}.json.
 * No country-specific fact is hard-coded in src/, config/ or views/_shared/.
 */
final class Country
{
    /** @var array<string,array> */
    private static array $cache = [];

    public static function get(string $iso): array
    {
        $iso = strtolower($iso);
        if (!preg_match('/^[a-z]{2}$/', $iso)) {
            throw new RuntimeException("Invalid ISO code: {$iso}");
        }
        if (!isset(self::$cache[$iso])) {
            $file = W5D_ROOT . '/data/countries/' . $iso . '.json';
            if (!is_file($file)) {
                throw new RuntimeException("Unknown country: {$iso}");
            }
            self::$cache[$iso] = Content::read($file);
        }
        return self::$cache[$iso];
    }

    /** @return string[] ISO codes that have a country file */
    public static function codes(): array
    {
        $codes = [];
        foreach (glob(W5D_ROOT . '/data/countries/*.json') ?: [] as $f) {
            $codes[] = basename($f, '.json');
        }
        sort($codes);
        return $codes;
    }

    /** @return array<string,array{slug:string,name:array,type:string}> regions keyed by slug */
    public static function regions(string $iso): array
    {
        $out = [];
        foreach (self::get($iso)['regions'] ?? [] as $r) {
            $out[$r['slug']] = $r;
        }
        return $out;
    }

    public static function region(string $iso, string $slug): ?array
    {
        return self::regions($iso)[$slug] ?? null;
    }

    public static function name(string $iso, ?string $lang = null): string
    {
        $names = self::get($iso)['names'] ?? [];
        $lang ??= Lang::current();
        return $names[$lang] ?? $names['en'] ?? strtoupper($iso);
    }

    public static function defaultIso(): string
    {
        return (string) Config::app('default_country', 'tn');
    }
}
