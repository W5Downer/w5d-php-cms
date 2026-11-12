<?php
declare(strict_types=1);

namespace W5D\Support;

final class Text
{
    /** ASCII slug: "Cultures / Humanities" → "cultures-humanities". */
    public static function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n', 'ß' => 'ss',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    /** "heritage-digitisation" → "Heritage digitisation". */
    public static function humanise(string $value): string
    {
        $out = str_replace(['-', '_'], ' ', $value);
        return ucfirst(trim($out));
    }

    /** First sentence of a paragraph, trimmed to $max characters. */
    public static function summary(?string $text, int $max = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text) ?? '');
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }
        return rtrim($cut, " \t\n\r\0\x0B.,;:") . '…';
    }

    /** Escaped output for HTML text and attribute contexts. */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** A path-safe id from arbitrary text, used for block anchors. */
    public static function anchor(string $value): string
    {
        $slug = self::slug($value);
        return $slug === '' ? 'section' : $slug;
    }
}
