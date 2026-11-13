<?php
namespace W5D;

use RuntimeException;

/**
 * The variable registry (rule 3: every number is a token).
 *
 *   {{ experts.target }}     declared value (config/variables.php)
 *   {{ accounts.total }}     derived expression, evaluated at render
 *   {{ counts.services }}    computed from data/content/ by Content::count()
 *
 * Unknown tokens throw. bin/lint.php uses the same resolver, so a bad token
 * fails the build instead of publishing a wrong number.
 */
final class Variables
{
    /** @var array<string,array> flattened declarations: path => def */
    private static ?array $defs = null;
    /** @var array<string,mixed> resolved values */
    private static array $resolved = [];
    /** @var array<string,true> cycle guard */
    private static array $stack = [];

    public const TOKEN = '/\{\{\s*([A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)*)\s*\}\}/';

    /** @return array<string,array> */
    public static function declarations(): array
    {
        if (self::$defs === null) {
            self::$defs = [];
            self::flatten(Config::variables(), '', self::$defs);
        }
        return self::$defs;
    }

    public static function has(string $path): bool
    {
        return str_starts_with($path, 'counts.') || str_starts_with($path, 'country.') || isset(self::declarations()[$path]);
    }

    public static function value(string $path): mixed
    {
        if (array_key_exists($path, self::$resolved)) {
            return self::$resolved[$path];
        }
        if (str_starts_with($path, 'counts.')) {
            return self::$resolved[$path] = Content::count(substr($path, 7));
        }
        if ($path === 'country.name') {
            return self::$resolved[$path] = Country::name(Country::defaultIso());
        }
        if ($path === 'country.currency') {
            return self::$resolved[$path] = (string) (Country::get(Country::defaultIso())['currency'] ?? '');
        }
        $defs = self::declarations();
        if (!isset($defs[$path])) {
            throw new RuntimeException("Unresolved variable: {$path}");
        }
        if (isset(self::$stack[$path])) {
            throw new RuntimeException("Cyclic variable: {$path}");
        }
        self::$stack[$path] = true;
        $def = $defs[$path];
        try {
            $raw = array_key_exists('derive', $def)
                ? self::evaluate((string) $def['derive'])
                : $def['value'];
        } finally {
            unset(self::$stack[$path]);
        }
        return self::$resolved[$path] = $raw;
    }

    /** Display form of a variable, using its declared format. */
    public static function format(string $path, ?string $lang = null): string
    {
        $defs = self::declarations();
        $fmt = $defs[$path]['fmt'] ?? (str_starts_with($path, 'counts.') ? 'int' : 'plain');
        $v = self::value($path);
        return match ($fmt) {
            'int' => number_format((float) $v, 0, '.', ','),
            'money2' => number_format((float) $v, 1, '.', ','),
            'pct' => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') . '%',
            default => (string) $v,
        };
    }

    /** Replace every {{ token }} in a string. Unresolved tokens throw. */
    public static function resolve(string $text): string
    {
        return preg_replace_callback(self::TOKEN, static fn (array $m): string => self::format($m[1]), $text) ?? $text;
    }

    /** @return string[] tokens used in a string (for lint) */
    public static function tokensIn(string $text): array
    {
        preg_match_all(self::TOKEN, $text, $m);
        return $m[1];
    }

    public static function reset(): void
    {
        self::$defs = null;
        self::$resolved = [];
        self::$stack = [];
    }

    private static function flatten(array $node, string $prefix, array &$out): void
    {
        foreach ($node as $key => $val) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($val) && (array_key_exists('value', $val) || array_key_exists('derive', $val))) {
                $out[$path] = $val;
            } elseif (is_array($val)) {
                self::flatten($val, $path, $out);
            }
        }
    }

    /**
     * Expression evaluator: numbers, identifiers, + - * / and parentheses.
     * Deliberately small: no function calls, no eval().
     */
    public static function evaluate(string $expr): float|int
    {
        $tokens = preg_split('/\s*([()+\-*\/])\s*|\s+/', trim($expr), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pos = 0;
        $parseExpr = null;
        $parseTerm = null;
        $parseFactor = function () use (&$tokens, &$pos, &$parseExpr, $expr) {
            $tok = $tokens[$pos++] ?? throw new RuntimeException("Bad expression: {$expr}");
            if ($tok === '(') {
                $v = $parseExpr();
                if (($tokens[$pos++] ?? '') !== ')') {
                    throw new RuntimeException("Missing ) in: {$expr}");
                }
                return $v;
            }
            if ($tok === '-') {
                return -$parseExpr();
            }
            if (is_numeric($tok)) {
                return $tok + 0;
            }
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $tok)) {
                $v = self::value($tok);
                if (!is_numeric($v)) {
                    throw new RuntimeException("Non-numeric variable in expression: {$tok}");
                }
                return $v + 0;
            }
            throw new RuntimeException("Unexpected token '{$tok}' in: {$expr}");
        };
        $parseTerm = function () use (&$tokens, &$pos, $parseFactor) {
            $v = $parseFactor();
            while (in_array($tokens[$pos] ?? '', ['*', '/'], true)) {
                $op = $tokens[$pos++];
                $rhs = $parseFactor();
                $v = $op === '*' ? $v * $rhs : ($rhs == 0 ? throw new RuntimeException('Division by zero') : $v / $rhs);
            }
            return $v;
        };
        $parseExpr = function () use (&$tokens, &$pos, $parseTerm) {
            $v = $parseTerm();
            while (in_array($tokens[$pos] ?? '', ['+', '-'], true)) {
                $op = $tokens[$pos++];
                $rhs = $parseTerm();
                $v = $op === '+' ? $v + $rhs : $v - $rhs;
            }
            return $v;
        };
        $result = $parseExpr();
        if ($pos !== count($tokens)) {
            throw new RuntimeException("Trailing tokens in: {$expr}");
        }
        return $result;
    }
}
