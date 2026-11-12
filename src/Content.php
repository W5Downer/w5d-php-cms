<?php
declare(strict_types=1);

namespace W5D;

use W5D\Support\Text;
use W5D\Support\Vars;

/**
 * Content — the JSON content repository.
 *
 * ONE PAGE = ONE FILE. Each page owns its data *and* its config, so two pages
 * can never contradict each other: page A's hero copy is in page A's file only.
 * The collection indexes (services, macro works, projects) read the individual
 * records and derive their tables, so a change to one record propagates without
 * anybody editing an index.
 *
 *   data/content/pages/{id}.json        ← a public page
 *   data/content/services/{slug}.json   ← one of the registered services
 *   data/content/macros/{slug}.json     ← one of the five macro works
 *   data/content/projects/{slug}.json   ← one of the register entries
 *   data/content/declarations/{slug}.json
 *   data/content/site.json              ← navigation, footer, global strings
 *
 * Every file is validated on load (`validate()`); `bin/lint.php` runs the same
 * checks over the whole tree and fails the build. Nothing is silently skipped:
 * an invalid file throws, so a bad edit can never reach the published site.
 */
final class Content
{
    /** @var array<string,array<string,mixed>> */
    private array $pages = [];

    /** @var array<string,array<string,mixed>> */
    private array $services = [];

    /** @var array<string,array<string,mixed>> */
    private array $macros = [];

    /** @var array<string,array<string,mixed>> */
    private array $projects = [];

    /** @var array<string,array<string,mixed>> */
    private array $declarations = [];

    /** @var array<string,array<string,mixed>> */
    private array $layers = [];

    /** @var array<string,mixed> */
    private array $site = [];

    /** @var list<string> */
    private array $errors = [];

    private Vars $vars;
    private array $config;

    public function __construct(private readonly string $dir, array $config, Vars $vars)
    {
        $this->config = $config;
        $this->vars = $vars;
        $this->load();
    }

    // ── Loading ────────────────────────────────────────────────────────────

    private function load(): void
    {
        $this->site = $this->readFile($this->dir . '/site.json', true) ?? [];
        $this->pages = $this->readCollection('pages');
        $this->services = $this->readCollection('services');
        $this->macros = $this->readCollection('macros');
        $this->projects = $this->readCollection('projects');
        $this->declarations = $this->readCollection('declarations');
        $this->layers = $this->readCollection('layers');
    }

    /** @return array<string,array<string,mixed>> keyed by id/slug */
    private function readCollection(string $kind): array
    {
        $out = [];
        $path = $this->dir . '/' . $kind;
        if (!is_dir($path)) {
            return $out;
        }

        $files = glob($path . '/*.json') ?: [];
        sort($files);

        foreach ($files as $file) {
            $data = $this->readFile($file);
            if ($data === null) {
                continue;
            }
            $id = (string) ($data['id'] ?? basename($file, '.json'));
            $data['_source'] = $kind . '/' . basename($file);
            $data['id'] = $id;
            $this->validate($kind, $data, $file);
            $out[$id] = $data;
        }

        return $out;
    }

    /** @return array<string,mixed>|null */
    private function readFile(string $file, bool $required = false): ?array
    {
        if (!is_file($file)) {
            if ($required) {
                $this->errors[] = "Missing required content file: {$file}";
            }
            return null;
        }

        $raw = file_get_contents($file);
        if ($raw === false) {
            $this->errors[] = "Cannot read {$file}";
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $this->errors[] = 'Invalid JSON in ' . $file . ': ' . json_last_error_msg();
            return null;
        }

        return $data;
    }

    /**
     * Structural validation. Only the invariants that would break rendering are
     * enforced here; missing prose is a lint warning, not a fatal error.
     */
    private function validate(string $kind, array $data, string $file): void
    {
        $label = basename($file);

        if (!isset($data['id']) || $data['id'] === '') {
            $this->errors[] = "{$label}: missing `id`";
        }

        $blocks = $data['blocks'] ?? null;
        if ($blocks !== null && !is_array($blocks)) {
            $this->errors[] = "{$label}: `blocks` must be a list";
            return;
        }

        // Validate declared types against the PHP registry, so a typo in JSON
        // can never publish an unknown status word or audience.
        $checks = [
            'audience' => 'audience',
            'status' => 'status',
            'layer' => 'layer',
            'evidence' => 'evidence',
            'placement' => 'placement',
            'horizon' => 'horizon',
            'service_status' => 'service_status',
            'kind' => 'page_kind',
            'nav_group' => 'nav_group',
            'ontology_layer' => 'ontology_layer',
            'meta_solution' => 'meta_solution',
        ];

        foreach ($checks as $field => $type) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                continue;
            }
            $options = $this->vars->options($type);
            if ($options !== [] && !array_key_exists($data[$field], $options)) {
                $this->errors[] = sprintf(
                    '%s: `%s` = "%s" is not one of: %s',
                    $label,
                    $field,
                    $data[$field],
                    implode(', ', array_keys($options))
                );
            }
        }

        if (isset($blocks) && is_array($blocks)) {
            $allowed = $this->vars->options('block');
            foreach ($blocks as $i => $block) {
                if (!is_array($block) || !isset($block['type'])) {
                    $this->errors[] = "{$label}: block #{$i} has no `type`";
                    continue;
                }
                if ($allowed !== [] && !array_key_exists((string) $block['type'], $allowed)) {
                    $this->errors[] = "{$label}: block #{$i} uses unknown type `{$block['type']}`";
                }
            }
        }

        // A collection record must carry a slug for URL generation.
        if (in_array($kind, ['services', 'macros', 'projects', 'declarations', 'layers'], true)) {
            if (empty($data['slug'])) {
                $this->errors[] = "{$label}: missing `slug`";
            }
        }
    }

    // ── Access ─────────────────────────────────────────────────────────────

    /** @return array<string,array<string,mixed>> */
    public function pages(): array
    {
        return $this->pages;
    }

    /** @return array<string,array<string,mixed>> */
    public function services(): array
    {
        return $this->services;
    }

    /** @return array<string,array<string,mixed>> */
    public function macros(): array
    {
        return $this->macros;
    }

    /** @return array<string,array<string,mixed>> */
    public function projects(): array
    {
        return $this->projects;
    }

    /** @return array<string,array<string,mixed>> */
    public function declarations(): array
    {
        return $this->declarations;
    }

    /**
     * The six-layer stacked ontology, keyed by layer key and ordered top-down
     * (L5 Projects first, L0 Academies last) so iteration matches the diagram.
     *
     * @return array<string,array<string,mixed>>
     */
    public function layers(): array
    {
        $order = ['projects', 'meta-solutions', 'macro-works', 'services', 'expert-teams', 'academies'];
        $out = [];
        foreach ($order as $key) {
            if (isset($this->layers[$key])) {
                $out[$key] = $this->layers[$key];
            }
        }
        // Any layer file with a key outside the canonical order still loads.
        foreach ($this->layers as $key => $layer) {
            if (!isset($out[$key])) {
                $out[$key] = $layer;
            }
        }
        return $out;
    }

    public function layer(string $key): ?array
    {
        return $this->layers[$key] ?? null;
    }

    public function page(string $id): ?array
    {
        return $this->pages[$id] ?? null;
    }

    public function service(string $slug): ?array
    {
        foreach ($this->services as $service) {
            if (($service['slug'] ?? '') === $slug) {
                return $service;
            }
        }
        return null;
    }

    public function macro(string $slug): ?array
    {
        return $this->macros[$slug] ?? null;
    }

    public function project(string $slug): ?array
    {
        return $this->projects[$slug] ?? null;
    }

    /** @return array<string,mixed> */
    public function site(): array
    {
        return $this->site;
    }

    /** Site-level setting: site.json → `settings.{key}` with fallback. */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->site['settings'][$key] ?? $default;
    }

    /** @return list<string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function assertValid(): void
    {
        if ($this->errors !== []) {
            throw new \RuntimeException("Content validation failed:\n  - " . implode("\n  - ", $this->errors));
        }
    }

    // ── Derived views ──────────────────────────────────────────────────────

    /**
     * Navigation built from site.json order, filtered to pages that exist.
     * Every group lists page ids; the page itself supplies its own title so the
     * menu can never disagree with the page heading.
     *
     * @return list<array{key:string,label:array<string,string>,items:list<array<string,mixed>>}>
     */
    public function navigation(string $lang): array
    {
        $groups = $this->site['nav'] ?? [];
        $out = [];

        foreach ($groups as $group) {
            if (!is_array($group) || empty($group['items'])) {
                continue;
            }
            $items = [];
            foreach ($group['items'] as $item) {
                if (is_string($item)) {
                    $item = ['page' => $item];
                }
                if (!is_array($item) || !isset($item['page'])) {
                    continue;
                }
                $id = (string) $item['page'];
                $page = $this->pages[$id] ?? null;
                if ($page === null) {
                    continue;
                }
                $items[] = [
                    'id' => $id,
                    'label' => $item['label'] ?? ($page['nav_label'] ?? $page['title'] ?? $id),
                    'description' => $item['description'] ?? ($page['summary'] ?? null),
                    'href' => Route::href('page', $id, $lang),
                    'current' => false,
                ];
            }
            if ($items === []) {
                continue;
            }
            $out[] = [
                'key' => (string) ($group['key'] ?? 'group'),
                'label' => $group['label'] ?? ['en' => ucfirst((string) ($group['key'] ?? 'Group'))],
                'items' => $items,
            ];
        }

        return $out;
    }

    /**
     * Services grouped by macro, in registry order. Used by the catalogue and
     * by each macro page, so both stay in sync automatically.
     *
     * @return array<string,list<array<string,mixed>>>
     */
    public function servicesByMacro(): array
    {
        $out = [];
        $services = $this->services;
        uasort($services, static fn (array $a, array $b): int => ((int) ($a['number'] ?? 0)) <=> ((int) ($b['number'] ?? 0)));

        foreach ($services as $slug => $service) {
            $macro = (string) ($service['macro'] ?? 'unassigned');
            $out[$macro][$slug] = $service;
        }

        // Keep the macro order from macros/*.json (order field).
        $ordered = [];
        $macros = $this->macros;
        uasort($macros, static fn (array $a, array $b): int => ((int) ($a['order'] ?? 99)) <=> ((int) ($b['order'] ?? 99)));
        foreach ($macros as $slug => $_) {
            if (isset($out[$slug])) {
                $ordered[$slug] = $out[$slug];
                unset($out[$slug]);
            }
        }
        return $ordered + $out;
    }

    /**
     * Register rows in a stable order: L2 programmes first, then L3 projects,
     * each alphabetically, so the register reads the same on every build.
     *
     * @return list<array<string,mixed>>
     */
    public function register(): array
    {
        $rows = array_values($this->projects);
        $layerRank = ['L2' => 0, 'L3' => 1, 'pillar' => 2];
        usort($rows, static function (array $a, array $b) use ($layerRank): int {
            $ra = $layerRank[(string) ($a['layer'] ?? 'L3')] ?? 9;
            $rb = $layerRank[(string) ($b['layer'] ?? 'L3')] ?? 9;
            if ($ra !== $rb) {
                return $ra <=> $rb;
            }
            return ((int) ($a['order'] ?? 999)) <=> ((int) ($b['order'] ?? 999));
        });
        return $rows;
    }

    /** Count of records by a status word, for the ecosystem summary. */
    public function statusCounts(): array
    {
        $counts = [];
        foreach ($this->projects as $project) {
            $status = (string) ($project['status'] ?? 'draft');
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        return $counts;
    }

    // ── Record creation / mutation (used by the admin panel) ───────────────

    /** Write a page file atomically and refresh the in-memory copy. */
    public function save(string $kind, string $id, array $data): void
    {
        $kind = in_array($kind, ['pages', 'services', 'macros', 'projects', 'declarations'], true) ? $kind : 'pages';
        $id = Text::slug($id);
        if ($id === '') {
            throw new \InvalidArgumentException('A record needs an id.');
        }

        $data['id'] = $id;
        unset($data['_source']);

        $dir = $this->dir . '/' . $kind;
        if (!is_dir($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create {$dir}");
        }

        $file = $dir . '/' . $id . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Cannot encode JSON: ' . json_last_error_msg());
        }

        $tmp = $file . '.tmp';
        if (file_put_contents($tmp, $json . "\n") === false) {
            throw new \RuntimeException("Cannot write {$tmp}");
        }
        if (!rename($tmp, $file)) {
            @unlink($tmp);
            throw new \RuntimeException("Cannot move {$tmp} into place");
        }

        $this->validate($kind, $data, $file);
        $data['_source'] = $kind . '/' . $id . '.json';
        match ($kind) {
            'services' => $this->services[$id] = $data,
            'macros' => $this->macros[$id] = $data,
            'projects' => $this->projects[$id] = $data,
            'declarations' => $this->declarations[$id] = $data,
            default => $this->pages[$id] = $data,
        };
    }

    public function delete(string $kind, string $id): bool
    {
        $kind = in_array($kind, ['pages', 'services', 'macros', 'projects', 'declarations'], true) ? $kind : 'pages';
        $id = Text::slug($id);
        $file = $this->dir . '/' . $kind . '/' . $id . '.json';
        if (!is_file($file)) {
            return false;
        }
        unlink($file);
        unset($this->{$kind}[$id]);
        return true;
    }

    // ── Rendering helpers ──────────────────────────────────────────────────

    /**
     * Localise any value: a plain string passes through; an array keyed by
     * language code resolves with fallback to the default language.
     */
    public static function tr(mixed $value, string $lang, ?string $fallback = 'en'): string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }
        if (!is_array($value)) {
            return '';
        }
        if (isset($value[$lang]) && is_scalar($value[$lang]) && trim((string) $value[$lang]) !== '') {
            return (string) $value[$lang];
        }
        if ($fallback !== null && isset($value[$fallback]) && is_scalar($value[$fallback])) {
            return (string) $value[$fallback];
        }
        // Last resort: first scalar value.
        foreach ($value as $candidate) {
            if (is_scalar($candidate) && trim((string) $candidate) !== '') {
                return (string) $candidate;
            }
        }
        return '';
    }

    /** True when a localised value genuinely exists in this language. */
    public static function hasLang(mixed $value, string $lang): bool
    {
        return is_array($value) && isset($value[$lang]) && is_scalar($value[$lang]) && trim((string) $value[$lang]) !== '';
    }
}
