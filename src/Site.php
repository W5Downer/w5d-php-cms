<?php
declare(strict_types=1);

namespace W5D;

use W5D\Support\Text;

/**
 * Site — turns one content record into one rendered HTML document.
 *
 * A "document" is described by a small array of fields (id, kind, title, blocks,
 * meta) that the page JSON, a collection record, or a derived index can all
 * produce. Site builds that document once per language, then hands it to the
 * active template's layout.
 *
 * Because the document is template-agnostic, the same page JSON renders in
 * every installed template: the template decides *how* the block types look,
 * never *what* the page says.
 */
final class Site
{
    public function __construct(
        private readonly string $root,
        private readonly array $config,
    ) {
    }

    // ── Documents ──────────────────────────────────────────────────────────

    /**
     * Build the document for a page id.
     * @return array<string,mixed>|null
     */
    public function documentForPage(string $id, string $lang): ?array
    {
        $content = Registry::content();
        $page = $content->page($id);
        if ($page === null) {
            return null;
        }

        $doc = $this->baseDocument($page, $lang);
        $doc['kind'] = (string) ($page['kind'] ?? 'page');
        $doc['route_kind'] = 'page';
        $doc['blocks'] = $this->prepareBlocks((array) ($page['blocks'] ?? []), $lang, $page);

        return $doc;
    }

    public function documentForMacro(string $slug, string $lang): ?array
    {
        $macro = Registry::content()->macro($slug);
        if ($macro === null) {
            return null;
        }
        $doc = $this->baseDocument($macro, $lang);
        $doc['kind'] = 'detail';
        $doc['route_kind'] = 'macro';
        $doc['blocks'] = $this->prepareBlocks((array) ($macro['blocks'] ?? []), $lang, $macro);
        return $doc;
    }

    public function documentForService(string $slug, string $lang): ?array
    {
        $service = Registry::content()->service($slug);
        if ($service === null) {
            return null;
        }
        $doc = $this->baseDocument($service, $lang);
        $doc['kind'] = 'detail';
        $doc['route_kind'] = 'service';
        $doc['blocks'] = $this->prepareBlocks((array) ($service['blocks'] ?? []), $lang, $service);
        return $doc;
    }

    public function documentForProject(string $slug, string $lang): ?array
    {
        $project = Registry::content()->project($slug);
        if ($project === null) {
            return null;
        }
        $doc = $this->baseDocument($project, $lang);
        $doc['kind'] = 'detail';
        $doc['route_kind'] = 'project';
        $doc['blocks'] = $this->prepareBlocks((array) ($project['blocks'] ?? []), $lang, $project);
        return $doc;
    }

    public function documentForDeclaration(string $slug, string $lang): ?array
    {
        $declaration = Registry::content()->declarations()[$slug] ?? null;
        if ($declaration === null) {
            return null;
        }
        $doc = $this->baseDocument($declaration, $lang);
        $doc['kind'] = 'detail';
        $doc['route_kind'] = 'declaration';
        $doc['blocks'] = $this->prepareBlocks((array) ($declaration['blocks'] ?? []), $lang, $declaration);
        return $doc;
    }

    public function documentForLayer(string $key, string $lang): ?array
    {
        $layer = Registry::content()->layer($key);
        if ($layer === null) {
            return null;
        }
        $doc = $this->baseDocument($layer, $lang);
        $doc['kind'] = 'detail';
        $doc['route_kind'] = 'layer';
        $doc['blocks'] = $this->prepareBlocks((array) ($layer['blocks'] ?? []), $lang, $layer);
        return $doc;
    }

    /**
     * Shared fields every document carries.
     * @return array<string,mixed>
     */
    private function baseDocument(array $record, string $lang): array
    {
        $content = Registry::content();
        $default = (string) $this->config['default_lang'];

        $title = Content::tr($record['title'] ?? $record['label'] ?? '', $lang, $default);
        $summary = Content::tr($record['summary'] ?? $record['short'] ?? '', $lang, $default);

        $id = (string) ($record['id'] ?? '');
        $routeKind = $record['_route'] ?? null;

        return [
            'id' => $id,
            'slug' => (string) ($record['slug'] ?? $id),
            'title' => $title,
            'nav_label' => Content::tr($record['nav_label'] ?? $record['title'] ?? '', $lang, $default),
            'eyebrow' => Content::tr($record['eyebrow'] ?? '', $lang, $default),
            'summary' => $summary,
            'description' => Content::tr($record['description'] ?? $record['summary'] ?? '', $lang, $default),
            'lang' => $lang,
            'audience' => (string) ($record['audience'] ?? 'public'),
            'nav_group' => (string) ($record['nav_group'] ?? 'utility'),
            'record' => $record,
            'status' => $record['status'] ?? null,
            'evidence' => $record['evidence'] ?? null,
            'layer' => $record['layer'] ?? null,
            'order' => $record['order'] ?? 0,
            'toc' => [],
            'updated' => $record['updated'] ?? null,
            'settings' => $record['settings'] ?? [],
            'template' => $record['template'] ?? ($this->config['active_template'] ?? 'ledger'),
        ];
    }

    /**
     * Prepare blocks: localise their strings, resolve variable tokens, and
     * record headings for the on-page table of contents.
     *
     * A block is passed to the renderer as:
     *   ['type' => 'prose', 'id' => 'anchor', 'data' => […] ]
     */
    private function prepareBlocks(array $blocks, string $lang, array $record): array
    {
        $prepared = [];
        $toc = [];
        $index = 0;

        foreach ($blocks as $block) {
            if (!is_array($block) || empty($block['type'])) {
                continue;
            }
            $type = (string) $block['type'];
            $index++;

            $anchor = isset($block['id'])
                ? Text::anchor((string) $block['id'])
                : Text::anchor(Content::tr($block['title'] ?? $type, $lang) . '-' . $index);

            // Only heading-bearing blocks take part in the table of contents.
            $headingBlocks = ['prose', 'cards', 'table', 'steps', 'definitions', 'register', 'roster', 'service-catalog', 'contact-desks', 'bars', 'timeline'];
            $title = Content::tr($block['title'] ?? '', $lang, (string) $this->config['default_lang']);
            if (in_array($type, $headingBlocks, true) && $title !== '') {
                $toc[] = ['id' => $anchor, 'label' => $this->resolveTokens($title, $lang)];
            }

            $prepared[] = [
                'type' => $type,
                'id' => $anchor,
                'title' => $title === '' ? '' : $this->resolveTokens($title, $lang),
                'data' => $this->localiseBlock($block, $lang, $record),
            ];
        }

        return ['items' => $prepared, 'toc' => $toc];
    }

    /** Deep-localise a block's values and resolve {{ var }} tokens in strings. */
    private function localiseBlock(array $block, string $lang, array $record): array
    {
        $block['_lang'] = $lang;
        $block['_record'] = $record;
        return $this->walk($block, $lang);
    }

    private function walk(mixed $value, string $lang): mixed
    {
        if (is_string($value)) {
            // Resolve tokens, then localise only if the string is a lang map.
            return $this->resolveTokens($value, $lang);
        }
        if (!is_array($value)) {
            return $value;
        }

        // A localisation map: {"en": "…", "fr": "…"} — detected by language keys.
        if ($this->looksLocalised($value)) {
            return $this->resolveTokens(Content::tr($value, $lang, (string) $this->config['default_lang']), $lang);
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = $this->walk($item, $lang);
        }
        return $out;
    }

    private function looksLocalised(array $value): bool
    {
        $langs = (array) ($this->config['langs'] ?? ['en']);
        $keys = array_keys($value);
        if ($keys === []) {
            return false;
        }
        foreach ($keys as $key) {
            if (!in_array($key, $langs, true)) {
                return false;
            }
        }
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                return false;
            }
        }
        return true;
    }

    public function resolveTokens(string $text, string $lang): string
    {
        return Registry::vars()->withLang($lang)->resolve($text);
    }

    // ── Rendering ──────────────────────────────────────────────────────────

    /**
     * Render a document to HTML using the given language + template.
     */
    public function render(array $doc, string $lang, ?string $template = null): string
    {
        $view = Registry::view();
        if ($template !== null) {
            $view->setTemplate($template);
        }

        // Reset sections per render.
        $GLOBALS['__w5d_sections'] = [];

        $doc['lang'] = $lang;
        $doc['dir'] = Registry::lang()->direction($lang);

        $blocks = $doc['blocks']['items'] ?? [];
        $toc = $doc['blocks']['toc'] ?? [];

        $payload = [
            'doc' => $doc,
            'lang' => $lang,
            'dir' => Registry::lang()->direction($lang),
            'blocks' => $blocks,
            'toc' => $toc,
            'site' => Registry::content()->site(),
            'nav' => Registry::content()->navigation($lang),
        ];

        // The layout is selected by the document kind, so a template can give
        // the home page a different shell without any page JSON change.
        $layout = ($doc['kind'] ?? 'page') === 'root' ? 'layouts.home' : 'layouts.app';
        if (!$view->exists($layout)) {
            $layout = 'layouts.app';
        }

        return $view->render($layout, $payload);
    }

    /** Render just the block sequence (used by the admin live preview). */
    public function renderBlocks(array $doc, string $lang): string
    {
        $html = '';
        foreach ($doc['blocks']['items'] ?? [] as $block) {
            $html .= Registry::view()->block($block['type'], [
                'block' => $block,
                'doc' => $doc,
                'lang' => $lang,
            ]);
        }
        return $html;
    }

    // ── Derived collections ────────────────────────────────────────────────

    /**
     * Build the ecosystem index document from the register, so the index can
     * never disagree with the individual project pages.
     */
    public function ecosystemIndexDocument(string $lang): array
    {
        $page = Registry::content()->page('ecosystem') ?? [];
        $doc = $this->baseDocument($page + ['id' => 'ecosystem', 'title' => ['en' => 'Ecosystem']], $lang);
        $doc['kind'] = 'index';
        $doc['route_kind'] = 'page';

        $register = [];
        foreach (Registry::content()->register() as $project) {
            $slug = (string) ($project['slug'] ?? '');
            $register[] = [
                'name' => Content::tr($project['title'] ?? '', $lang),
                'subtitle' => Content::tr($project['subtitle'] ?? '', $lang),
                'layer' => (string) ($project['layer'] ?? 'L3'),
                'status' => (string) ($project['status'] ?? 'draft'),
                'evidence' => (string) ($project['evidence'] ?? 'E1'),
                'verified' => (string) ($project['verified'] ?? vraw('time.registry_verified')),
                'exists' => Content::tr($project['exists'] ?? '', $lang),
                'href' => href('project', $slug, $lang),
                'stage' => stage_percent((string) ($project['status'] ?? 'draft')),
                'horizon' => (string) ($project['horizon'] ?? ''),
                'group' => Content::tr($project['group'] ?? '', $lang),
            ];
        }

        $blocks = (array) ($page['blocks'] ?? []);
        // Append the generated register unless the page already declares one.
        $hasRegister = false;
        foreach ($blocks as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'register') {
                $hasRegister = true;
            }
        }
        if (!$hasRegister) {
            $blocks[] = ['type' => 'register', 'title' => ['en' => 'The register', 'fr' => 'Le registre', 'ar' => 'السجل'], 'source' => 'register'];
        }

        $doc['blocks'] = $this->prepareBlocks($blocks, $lang, $page);
        $doc['register'] = $register;
        return $doc;
    }

    /** The services catalogue document, grouped by macro. */
    public function servicesIndexDocument(string $lang): array
    {
        $page = Registry::content()->page('services') ?? [];
        $doc = $this->baseDocument($page + ['id' => 'services', 'title' => ['en' => 'Services']], $lang);
        $doc['kind'] = 'index';
        $doc['route_kind'] = 'page';

        $catalog = [];
        foreach (Registry::content()->servicesByMacro() as $macroSlug => $services) {
            $macro = Registry::content()->macro($macroSlug);
            $catalog[$macroSlug] = [
                'slug' => $macroSlug,
                'title' => Content::tr($macro['title'] ?? $macroSlug, $lang),
                'placement' => (string) ($macro['placement'] ?? 'real'),
                'href' => href('macro', $macroSlug, $lang),
                'services' => array_map(static function (array $service) use ($lang): array {
                    return [
                        'number' => (int) ($service['number'] ?? 0),
                        'number_label' => str_pad((string) ($service['number'] ?? 0), 3, '0', STR_PAD_LEFT),
                        'title' => Content::tr($service['title'] ?? '', $lang),
                        'slug' => (string) ($service['slug'] ?? ''),
                        'short' => Content::tr($service['short'] ?? '', $lang),
                        'placement' => (string) ($service['placement'] ?? 'real'),
                        'href' => href('service', (string) ($service['slug'] ?? ''), $lang),
                    ];
                }, $services),
            ];
        }

        $blocks = (array) ($page['blocks'] ?? []);
        $blocks[] = ['type' => 'service-catalog', 'title' => ['en' => 'The 130 registered services']];
        $doc['blocks'] = $this->prepareBlocks($blocks, $lang, $page);
        $doc['catalog'] = $catalog;
        return $doc;
    }

    /** Declarations index. */
    public function declarationsIndexDocument(string $lang): array
    {
        $page = Registry::content()->page('declarations') ?? [];
        $doc = $this->baseDocument($page + ['id' => 'declarations', 'title' => ['en' => 'Declarations']], $lang);
        $doc['kind'] = 'index';
        $doc['route_kind'] = 'page';

        $items = [];
        foreach (Registry::content()->declarations() as $slug => $declaration) {
            $items[] = [
                'title' => Content::tr($declaration['title'] ?? '', $lang),
                'summary' => Content::tr($declaration['summary'] ?? '', $lang),
                'date' => (string) ($declaration['date'] ?? ''),
                'version' => (string) ($declaration['version'] ?? ''),
                'href' => href('declaration', $slug, $lang),
                'grand_title' => Content::tr($declaration['grand_title'] ?? '', $lang),
            ];
        }

        $blocks = (array) ($page['blocks'] ?? []);
        $blocks[] = ['type' => 'cards', 'title' => ['en' => 'The seven texts'], 'source' => 'declarations'];
        $doc['blocks'] = $this->prepareBlocks($blocks, $lang, $page);
        $doc['declarations'] = $items;
        return $doc;
    }
}
