<?php
declare(strict_types=1);

namespace W5D\Support;

/**
 * Json — read/write helpers with clear failure modes.
 *
 * The admin panel writes JSON by hand (no form library), so this class is the
 * single place that decides how a file is serialised: pretty-printed, unescaped
 * slashes and Unicode (so Arabic stays readable in the file), keys in the order
 * they were set, with a trailing newline.
 */
final class Json
{
    public static function read(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }
        $raw = file_get_contents($file);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    public static function readOrFail(string $file): array
    {
        $data = self::read($file);
        if ($data === null) {
            throw new \RuntimeException("Cannot read JSON file: {$file}");
        }
        return $data;
    }

    /** Atomic write: temp file in the same directory, then rename. */
    public static function write(string $file, array $data): void
    {
        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create directory: {$dir}");
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Cannot encode JSON: ' . json_last_error_msg());
        }

        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmp, $json . "\n") === false) {
            throw new \RuntimeException("Cannot write {$tmp}");
        }
        if (!rename($tmp, $file)) {
            @unlink($tmp);
            throw new \RuntimeException("Cannot replace {$file}");
        }
    }

    /** Decode a JSON string typed into a textarea; returns null when invalid. */
    public static function parse(string $text): ?array
    {
        $data = json_decode($text, true);
        return is_array($data) ? $data : null;
    }

    public static function pretty(string $text): string
    {
        $data = self::parse($text);
        if ($data === null) {
            return $text;
        }
        return (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
