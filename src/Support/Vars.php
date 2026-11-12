<?php
declare(strict_types=1);

namespace W5D\Support;

/**
 * Vars — the variable registry.
 *
 * Loads config/variables.php, deep-merges config/variables.local.php over it
 * (written by the admin panel, never by hand), and resolves the {{ token }}
 * placeholders used inside page JSON.
 *
 * A token is a dotted path into the registry. Three shapes are supported:
 *
 *   counts.services          → the formatted figure ("130")
 *   counts.services.label    → its label ("Registered services")
 *   counts.services.note     → its note (may itself contain tokens)
 *
 * `var.` may prefix any path for readability. Unknown tokens render as an
 * em dash and are collected in $missing so the build can fail loudly instead of
 * publishing a broken sentence.
 */
final class Vars
{
    /** @var array<string,mixed> */
    private array $data;

    /** @var array<string,true> Tokens that could not be resolved during this request. */
    private array $missing = [];

    /** @var string */
    private string $lang;

    public function __construct(array $data, string $lang = 'en')
    {
        $this->data = $data;
        $this->lang = $lang;
    }

    public static function load(string $configDir, string $lang = 'en'): self
    {
        $base = require $configDir . '/variables.php';
        if (!is_array($base)) {
            throw new \RuntimeException('config/variables.php must return an array.');
        }

        $localFile = $configDir . '/variables.local.php';
        if (is_file($localFile)) {
            $overrides = require $localFile;
            if (is_array($overrides)) {
                $base = self::merge($base, $overrides);
            }
        }

        return new self($base, $lang);
    }

    /** Recursive merge: scalar in $over replaces the value of the same key. */
    public static function merge(array $base, array $over): array
    {
        foreach ($over as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::merge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    public function withLang(string $lang): self
    {
        $clone = clone $this;
        $clone->lang = $lang;
        return $clone;
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        return $this->data;
    }

    public function raw(string $path): mixed
    {
        $node = $this->data;
        foreach (explode('.', trim($path, '.')) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return null;
            }
            $node = $node[$segment];
        }
        return $node;
    }

    /** A whole group, e.g. `counts` or `types.status.options`. */
    public function group(string $path): array
    {
        $node = $this->raw($path);
        return is_array($node) ? $node : [];
    }

    /** The bare value of a variable entry (unformatted). */
    public function value(string $path): int|float|string|bool|null
    {
        $node = $this->raw($path);
        if (is_array($node)) {
            return $node['value'] ?? null;
        }
        return is_scalar($node) ? $node : null;
    }

    /** A formatted figure ready for output, e.g. "130" or "6.76M". */
    public function format(string $path): string
    {
        $entry = $this->raw($path);
        if (!is_array($entry)) {
            return is_scalar($entry) ? (string) $entry : $this->fail($path);
        }

        $value = $entry['value'] ?? null;
        if ($value === null) {
            return $this->fail($path);
        }

        // A text entry may itself contain tokens (e.g. the licence footer).
        if (($entry['format'] ?? 'text') === 'text') {
            return $this->resolve((string) $value);
        }

        return Formatter::apply($value, (string) ($entry['format'] ?? 'text'), $entry, $this->lang);
    }

    /** The label of a variable entry, falling back to a humanised path. */
    public function label(string $path): string
    {
        $entry = $this->raw($path);
        $label = is_array($entry) ? ($entry['label'] ?? null) : null;
        if (is_string($label) && $label !== '') {
            return $label;
        }
        $parts = explode('.', $path);
        return ucfirst(str_replace(['_', '-'], ' ', (string) end($parts)));
    }

    public function note(string $path): ?string
    {
        $entry = $this->raw($path);
        $note = is_array($entry) ? ($entry['note'] ?? null) : null;
        return is_string($note) && $note !== '' ? $this->resolve($note) : null;
    }

    public function source(string $path): ?string
    {
        $entry = $this->raw($path);
        $source = is_array($entry) ? ($entry['source'] ?? null) : null;
        return is_string($source) && $source !== '' ? $source : null;
    }

    /** Every entry in a group as [path => formatted]. Used by KPI strips. */
    public function flatten(string $group): array
    {
        $out = [];
        foreach ($this->group($group) as $key => $entry) {
            if (is_array($entry) && array_key_exists('value', $entry) && empty($entry['type'])) {
                $out[$group . '.' . $key] = [
                    'path' => $group . '.' . $key,
                    'value' => $this->format($group . '.' . $key),
                    'label' => $this->label($group . '.' . $key),
                    'note' => $this->note($group . '.' . $key),
                    'source' => $this->source($group . '.' . $key),
                ];
            }
        }
        return $out;
    }

    /** A type vocabulary: [key => label]. Labels are localised when possible. */
    public function options(string $type): array
    {
        $node = $this->raw('types.' . $type . '.options');
        if (!is_array($node)) {
            return [];
        }
        return $node;
    }

    public function isType(string $type): bool
    {
        $node = $this->raw('types.' . $type);
        return is_array($node) && !empty($node['type']);
    }

    public function typeDefault(string $type): ?string
    {
        $node = $this->raw('types.' . $type . '.default');
        return is_string($node) ? $node : null;
    }

    /** Position on the nine-step ladder for a status key (1–9). */
    public function statusStage(string $status): int
    {
        $ladder = $this->raw('types.status.ladder');
        return is_array($ladder) && isset($ladder[$status]) ? (int) $ladder[$status] : 0;
    }

    /** Replace every {{ token }} in a string. */
    public function resolve(string $text): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_.\-]+)\s*\}\}/',
            function (array $m): string {
                return $this->format($m[1]);
            },
            $text
        );
    }

    /** @return list<string> */
    public function missing(): array
    {
        return array_keys($this->missing);
    }

    private function fail(string $path): string
    {
        $this->missing[$path] = true;
        return '—';
    }
}

/**
 * Formatter — locale-aware number and date output without the intl extension.
 */
final class Formatter
{
    private const MONTHS = [
        'en' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'fr' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'ar' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    ];

    private const SHORT_MONTHS = [
        'en' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'fr' => ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'],
        'ar' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
    ];

    public static function apply(int|float|string|bool $value, string $format, array $entry = [], string $lang = 'en'): string
    {
        $unit = isset($entry['unit']) && $entry['unit'] !== '' ? (string) $entry['unit'] : '';

        $out = match ($format) {
            'int' => self::group((string) (int) $value, $lang),
            'decimal' => self::decimal((float) $value, $lang, self::decimals($entry)),
            'compact' => self::compact((float) $value, $lang),
            'money' => self::group(self::trimDecimal((float) $value), $lang),
            'percent' => self::decimal((float) $value, $lang, self::decimals($entry)) . '%',
            'date' => self::date((string) $value, $lang),
            'date-short' => self::date((string) $value, $lang, true),
            'month' => self::month((string) $value, $lang),
            'year' => self::year((string) $value),
            'bool' => ((bool) $value) ? 'Yes' : 'No',
            default => (string) $value,
        };

        if ($unit !== '') {
            // Currency codes and durations sit close; word units get a space.
            $tight = in_array($unit, ['TND', '%', ':1', 'h'], true) === false && preg_match('/^\p{L}+$/u', $unit) === 1;
            $out .= ($tight ? ' ' : ' ') . $unit;
        }

        return $out;
    }

    private static function decimals(array $entry): int
    {
        return isset($entry['decimals']) ? (int) $entry['decimals'] : 1;
    }

    private static function group(string $digits, string $lang): string
    {
        $negative = str_starts_with($digits, '-');
        $digits = ltrim($digits, '-');
        $sep = $lang === 'fr' ? "\u{202F}" : ',';
        $out = '';
        $count = 0;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $out = $digits[$i] . $out;
            $count++;
            if ($count % 3 === 0 && $i > 0) {
                $out = $sep . $out;
            }
        }
        return ($negative ? '-' : '') . $out;
    }

    private static function trimDecimal(float $value): string
    {
        $out = number_format($value, 2, '.', '');
        $out = rtrim(rtrim($out, '0'), '.');
        return $out === '' ? '0' : $out;
    }

    private static function decimal(float $value, string $lang, int $decimals): string
    {
        $sign = $value < 0 ? '-' : '';
        $abs = abs($value);
        $fixed = number_format($abs, $decimals, '.', '');
        [$whole, $frac] = array_pad(explode('.', $fixed, 2), 2, '');
        $dot = $lang === 'fr' ? ',' : '.';
        return $sign . self::group($whole, $lang) . ($frac !== '' ? $dot . $frac : '');
    }

    private static function compact(float $value, string $lang): string
    {
        $sign = $value < 0 ? '-' : '';
        $abs = abs($value);

        foreach ([[1e9, 'B'], [1e6, 'M'], [1e3, 'K']] as [$size, $suffix]) {
            if ($abs >= $size) {
                $scaled = $abs / $size;
                $decimals = $scaled >= 100 ? 0 : ($scaled >= 10 ? 1 : 2);
                $text = rtrim(rtrim(number_format($scaled, $decimals, '.', ''), '0'), '.');
                if ($lang === 'fr') {
                    $text = str_replace('.', ',', $text);
                }
                return $sign . $text . $suffix;
            }
        }

        return $sign . self::group((string) (int) round($abs), $lang);
    }

    private static function year(string $value): string
    {
        return substr($value, 0, 4);
    }

    private static function month(string $value, string $lang): string
    {
        $months = self::MONTHS[$lang] ?? self::MONTHS['en'];
        $index = (int) $value;
        return $months[max(0, min(11, $index - 1))] ?? '';
    }

    public static function date(string $value, string $lang = 'en', bool $short = false): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $value, $m)) {
            return $value; // "to be confirmed" and other free text pass through
        }
        $year = (int) $m[1];
        $month = (int) $m[2];
        $day = (int) $m[3];
        $months = $short ? (self::SHORT_MONTHS[$lang] ?? self::SHORT_MONTHS['en']) : (self::MONTHS[$lang] ?? self::MONTHS['en']);
        $name = $months[$month - 1] ?? '';
        $monthFull = self::MONTHS[$lang][$month - 1] ?? '';

        return match ($lang) {
            'fr' => $short ? sprintf('%d %s %d', $day, $name, $year) : sprintf('%d %s %d', $day, $monthFull, $year),
            'ar' => sprintf('%d %s %d', $day, $name, $year),
            default => $short ? sprintf('%s %d, %d', $name, $day, $year) : sprintf('%d %s %d', $day, $monthFull, $year),
        };
    }
}
