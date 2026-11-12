<?php
declare(strict_types=1);

namespace W5D;

/**
 * Lang — language resolution and UI translation.
 *
 * Two separate jobs, deliberately kept apart:
 *
 *   1. UI chrome (buttons, table headers, form labels) lives in lang/{code}.php.
 *      These are the only strings the engine itself needs.
 *   2. Page content lives in each page's JSON file as a {lang: value} map.
 *      Content is *not* in lang/*.php, so a page can never contradict another.
 *
 * Fallback chain: requested language → default language → the key itself.
 * A missing key is recorded so `bin/lint.php` can report translation coverage.
 */
final class Lang
{
    /** @var array<string,array<string,string>> */
    private array $strings = [];

    /** @var array<string,true> */
    private array $missing = [];

    private string $lang;

    public function __construct(private readonly string $langDir, private readonly array $config)
    {
        $this->lang = (string) ($config['default_lang'] ?? 'en');
    }

    public static function boot(string $root, array $config): self
    {
        return new self($root . '/lang', $config);
    }

    public function code(): string
    {
        return $this->lang;
    }

    public function set(string $lang): self
    {
        if (in_array($lang, $this->config['langs'] ?? [], true)) {
            $this->lang = $lang;
        }
        return $this;
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_values($this->config['langs'] ?? ['en']);
    }

    public function isRtl(?string $lang = null): bool
    {
        $lang ??= $this->lang;
        return in_array($lang, $this->config['rtl_langs'] ?? [], true);
    }

    public function direction(?string $lang = null): string
    {
        return $this->isRtl($lang) ? 'rtl' : 'ltr';
    }

    public function name(?string $lang = null): string
    {
        $lang ??= $this->lang;
        return (string) (($this->config['lang_names'] ?? [])[$lang] ?? strtoupper($lang));
    }

    /** Native, unescaped name for list display. */
    public function nativeName(?string $lang = null): string
    {
        $lang ??= $this->lang;
        return (string) (($this->config['lang_names'] ?? [])[$lang] ?? strtoupper((string) $lang));
    }

    public function htmlCode(?string $lang = null): string
    {
        $lang ??= $this->lang;
        return (string) (($this->config['locales'] ?? [])[$lang]['html'] ?? $lang);
    }

    public function ogLocale(?string $lang = null): string
    {
        $lang ??= $this->lang;
        return (string) (($this->config['locales'] ?? [])[$lang]['og'] ?? $lang);
    }

    public function formatLocale(?string $lang = null): string
    {
        $lang ??= $this->lang;
        return (string) (($this->config['format_locales'] ?? [])[$lang] ?? 'en_GB');
    }

    /** Translate a UI key. Supports {name} interpolation. */
    public function t(string $key, array $replace = [], ?string $lang = null): string
    {
        $lang ??= $this->lang;
        $value = $this->lookup($lang, $key);
        if ($value === null && $lang !== $this->config['default_lang']) {
            $value = $this->lookup((string) $this->config['default_lang'], $key);
        }
        if ($value === null) {
            $this->missing[$key] = true;
            return $key;
        }
        foreach ($replace as $name => $replacement) {
            $value = str_replace('{' . $name . '}', (string) $replacement, $value);
        }
        return $value;
    }

    public function has(string $key, ?string $lang = null): bool
    {
        $lang ??= $this->lang;
        return $this->lookup($lang, $key) !== null;
    }

    /** @return array<string,string> Flat key list for the admin translation screen. */
    public function all(?string $lang = null): array
    {
        $lang ??= $this->lang;
        return $this->load($lang);
    }

    /** @return list<string> */
    public function missingKeys(): array
    {
        return array_keys($this->missing);
    }

    /** Coverage of a language against the default language. */
    public function coverage(string $lang): array
    {
        $base = $this->load((string) $this->config['default_lang']);
        $target = $this->load($lang);
        $total = count($base);
        $done = 0;
        foreach ($base as $key => $_) {
            if (isset($target[$key]) && trim((string) $target[$key]) !== '') {
                $done++;
            }
        }
        return [
            'lang' => $lang,
            'total' => $total,
            'translated' => $done,
            'percent' => $total === 0 ? 100 : (int) round($done / $total * 100),
        ];
    }

    private function lookup(string $lang, string $key): ?string
    {
        $all = $this->load($lang);
        return array_key_exists($key, $all) ? (string) $all[$key] : null;
    }

    /** @return array<string,string> */
    private function load(string $lang): array
    {
        if (isset($this->strings[$lang])) {
            return $this->strings[$lang];
        }

        $safe = preg_replace('/[^a-z]{2,8}/', '', $lang) ?? 'en';
        $file = $this->langDir . '/' . $safe . '.php';
        $strings = is_file($file) ? require $file : [];
        if (!is_array($strings)) {
            $strings = [];
        }

        // Flatten nested arrays to dotted keys so `admin.nav.users` works.
        $flat = [];
        $walk = static function (array $node, string $prefix) use (&$walk, &$flat): void {
            foreach ($node as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
                if (is_array($value)) {
                    $walk($value, $path);
                } else {
                    $flat[$path] = (string) $value;
                }
            }
        };
        $walk($strings, '');

        return $this->strings[$lang] = $flat;
    }
}
