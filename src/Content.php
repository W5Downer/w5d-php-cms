<?php
namespace W5D;

use RuntimeException;

/**
 * Reads the JSON content tree under data/content/.
 * Content is the source of truth; MySQL mirrors it only for joins (bin/sync-content.php).
 */
final class Content
{
    /** @var array<string,array<string,array>> type => slug => record */
    private static array $cache = [];

    private const TYPES = ['services', 'macros', 'projects', 'layers', 'pages'];

    public static function root(): string
    {
        return W5D_ROOT . '/data/content';
    }

    /** @return array<string,array> */
    public static function all(string $type): array
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new RuntimeException("Unknown content type: {$type}");
        }
        if (!isset(self::$cache[$type])) {
            $records = [];
            $dir = self::root() . '/' . $type;
            foreach (glob($dir . '/*.json') ?: [] as $file) {
                $rec = self::read($file);
                $slug = (string) ($rec['slug'] ?? $rec['id'] ?? basename($file, '.json'));
                $records[$slug] = $rec;
            }
            ksort($records);
            if ($type === 'services') {
                // The service number is declared once, in macros/*.json membership, and injected here.
                foreach (self::all('macros') as $macro) {
                    foreach ($macro['services'] ?? [] as $ref) {
                        if (isset($records[$ref['slug']])) {
                            $records[$ref['slug']]['number'] = (int) $ref['number'];
                        }
                    }
                }
            }
            self::$cache[$type] = $records;
        }
        return self::$cache[$type];
    }

    public static function get(string $type, string $slug): ?array
    {
        return self::all($type)[$slug] ?? null;
    }

    /** Single JSON file (site.json, pages/{id}.json handled by get()). */
    public static function file(string $relative): array
    {
        return self::read(self::root() . '/' . $relative);
    }

    public static function count(string $what): int
    {
        return match ($what) {
            'services' => count(self::all('services')),
            'macros' => count(self::all('macros')),
            'projects' => count(self::all('projects')),
            'layers' => count(self::all('layers')),
            'services_per_macro' => (int) round(count(self::all('services')) / max(1, count(self::all('macros')))),
            'meta_solutions' => count(self::file('layers/meta-solutions.json')['items'] ?? []),
            'pages' => count(self::all('pages')),
            default => throw new RuntimeException("Unknown count: {$what}"),
        };
    }

    public static function reset(): void
    {
        self::$cache = [];
    }

    public static function read(string $file): array
    {
        if (!is_file($file)) {
            throw new RuntimeException("Missing content file: {$file}");
        }
        $data = json_decode((string) file_get_contents($file), true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException("Content file is not an object: {$file}");
        }
        return $data;
    }
}
