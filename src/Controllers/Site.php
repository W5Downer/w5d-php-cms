<?php
namespace W5D\Controllers;

use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Lang;
use W5D\Page;
use W5D\Router;
use W5D\Variables;

/**
 * Public site controllers. Content JSON for static pages; generated documents for
 * collection pages (macro, service, project, layer, governorate) so 130 services and
 * 25 projects are never hand-copied (single source of truth).
 */
final class Site
{
    public static function page(string $id): array
    {
        $doc = Content::all('pages')[$id] ?? null;
        if ($doc === null) {
            return \W5D\App::error(404, null);
        }
        $doc['path'] = $doc['path'] ?? Router::canonicalUrl(array_search($id, Router::PAGES, true) ?: '/' . $id);
        if ($id === 'home') {
            $doc = self::home($doc);
        }
        if ($id === 'countries-tunisia') {
            $doc['blocks'][] = self::govBlock();
        }
        $doc = self::fill($doc);
        return Page::render(self::withLimits($doc));
    }

    /** Fill generated lists (macros, projects, layers, statistics) into named blocks. Nothing is typed twice. */
    private static function fill(array $doc): array
    {
        $macros = array_map(static fn ($m) => ['title' => $m['title'], 'text' => $m['domain_question'] ?? $m['problem'],
            'href' => "/macro-works/{$m['slug']}/", 'gradient' => $m['slug']], array_values(Content::all('macros')));
        $projects = array_map(static fn ($p) => ['title' => $p['title'], 'text' => $p['summary'], 'href' => "/projects/{$p['slug']}/"],
            array_values(Content::all('projects')));
        $layers = [];
        foreach (Content::all('layers') as $l) {
            $layers[] = ['layer' => $l['layer'], 'layer_label' => $l['layer'] . ' · ' . loc($l['title']), 'title' => $l['title'],
                'question' => $l['question'], 'href' => "layer:{$l['id']}"];
        }
        usort($layers, static fn ($a, $b) => strcmp($b['layer'], $a['layer']));
        $stats = Content::file('statistics/advanced-education-unemployment.json');
        $rows = [];
        foreach ($stats['rows'] as $r) {
            $rows[] = [['en' => $r['country']], ['en' => (string) $r['year']], ['en' => number_format((float) $r['rate'], 3, '.', '')]];
        }
        foreach ($doc['blocks'] as &$b) {
            switch ($b['id'] ?? '') {
                case 'home-fit': case 'home-macros': $b['data']['items'] = $macros; break;
                case 'home-projects': $b['data']['items'] = array_slice($projects, 0, 6); break;
                case 'home-stack': case 'ont-stack': $b['data']['items'] = $layers; break;
                case 'stat-table': $b['data']['rows'] = $rows; break;
            }
        }
        unset($b);
        return $doc;
    }

    public static function index(string $id): array
    {
        return match ($id) {
            'macro-index' => Page::render(self::macroIndex()),
            'services-index' => Page::render(self::servicesIndex()),
            'projects-index' => Page::render(self::projectsIndex()),
            default => \W5D\App::error(404, null),
        };
    }

    public static function macro(string $slug): array
    {
        $m = Content::get('macros', $slug);
        $members = [];
        foreach ($m['services'] as $ref) {
            $members[] = self::serviceCard(Content::get('services', $ref['slug']));
        }
        $name = tr_loc($m['title']);
        $doc = [
            'id' => 'macro-' . $slug, 'path' => "/macro-works/{$slug}/", 'title' => $m['title'],
            'description' => $m['problem'], 'blocks' => [
                ['type' => 'hero', 'id' => 'mw-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => $slug,
                    'title' => $m['title'], 'data' => ['eyebrow' => ['en' => 'Macro work · ' . $m['placement']],
                    'lead' => $m['domain_question'] ?? $m['problem'], 'lead_only' => true],
                    'cta' => ['label' => ['en' => 'Find my service here'], 'href' => '#mw-catalog', 'class' => 'btn-peak']],
                ['type' => 'prose', 'id' => 'mw-why-tn', 'tier' => 'E', 'focus' => 3, 'title' => ['en' => 'Why this macro work'],
                    'data' => ['paragraphs' => [$m['problem'], $m['premium_offer']]]],
                ['type' => 'kpis', 'id' => 'mw-kpis', 'tier' => 'I', 'focus' => 2, 'title' => ['en' => 'Scale of the macro work'],
                    'data' => ['items' => [
                        ['label' => ['en' => 'Services in this macro work'], 'value' => '{{ counts.services_per_macro }}'],
                        ['label' => ['en' => 'Services in the catalogue'], 'value' => '{{ counts.services }}'],
                        ['label' => ['en' => 'Experts per service (design ceiling)'], 'value' => '{{ experts.per_service }}'],
                    ]]],
                ['type' => 'definitions', 'id' => 'mw-units', 'tier' => 'X', 'focus' => 1, 'title' => ['en' => 'Output unit and boundary'],
                    'data' => ['items' => [
                        ['term' => ['en' => 'Typical expert fields'], 'def' => $m['typical_fields']],
                        ['term' => ['en' => 'Characteristic output unit'], 'def' => $m['output_unit']],
                        ['term' => ['en' => 'Recurring boundary'], 'def' => $m['recurring_boundary']],
                        ['term' => ['en' => 'Delivery and economics'], 'def' => $m['delivery_economics']],
                    ]]],
                ['type' => 'service-catalog', 'id' => 'mw-catalog', 'tier' => 'I', 'focus' => 4, 'title' => ['en' => 'Services in this macro work'],
                    'data' => ['items' => $members]],
                ['type' => 'cta', 'id' => 'mw-mid-cta', 'tier' => 'E', 'focus' => 5, 'gradient' => $slug,
                    'title' => ['en' => 'Register interest in ' . $name['en']],
                    'data' => ['text' => ['en' => 'Registration records interest in a path. It is not a job, a certificate or an assessment result.']],
                    'cta' => ['label' => ['en' => 'Register in ' . $name['en']], 'href' => '/app/register/?macro=' . $slug, 'class' => 'btn-peak']],
                ['type' => 'notice', 'id' => 'mw-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'Services are proposals until a buyer, price, legal scope and cash are established. Read the full statement.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
                ['type' => 'faq', 'id' => 'mw-faq', 'tier' => 'E', 'focus' => 2, 'title' => ['en' => 'Questions'],
                    'data' => ['items' => [
                        ['q' => ['en' => 'Does choosing this macro work guarantee work?'], 'a' => ['en' => 'No. It records interest and a preferred field. Work depends on funded demand and a passed assessment.']],
                        ['q' => ['en' => 'Can I apply to more than one service?'], 'a' => ['en' => 'Yes, but you choose one primary service at registration.']],
                    ]]],
            ],
        ];
        return Page::render($doc);
    }

    public static function service(string $slug): array
    {
        $s = Content::get('services', $slug);
        $m = Content::get('macros', $s['macro']);
        $related = [];
        foreach ($m['services'] as $ref) {
            if ($ref['slug'] !== $slug && count($related) < 5) {
                $related[] = self::serviceCard(Content::get('services', $ref['slug']));
            }
        }
        $projects = [];
        foreach (Content::all('projects') as $p) {
            if (in_array($slug, $p['services_consumed'] ?? [], true)) {
                $projects[] = ['title' => $p['title'], 'text' => $p['summary'], 'href' => "/projects/{$p['slug']}/"];
            }
        }
        $doc = [
            'id' => 'service-' . $slug, 'path' => "/services/{$slug}/", 'title' => $s['title'],
            'description' => $s['short'], 'blocks' => [
                ['type' => 'hero', 'id' => 'svc-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => $s['macro'],
                    'title' => $s['title'], 'data' => ['eyebrow' => ['en' => 'Service ' . str_pad((string) $s['number'], 3, '0', STR_PAD_LEFT) . ' · ' . $m['title']['en']],
                    'lead' => $s['short']],
                    'cta' => ['label' => ['en' => 'Apply to this service'], 'href' => "/app/register/?service={$slug}", 'class' => 'btn-peak']],
                ['type' => 'prose', 'id' => 'svc-problem', 'tier' => 'E', 'focus' => 3, 'title' => ['en' => 'The problem'],
                    'data' => ['paragraphs' => [$s['problem'], $s['long']]]],
                ['type' => 'eligibility', 'id' => 'svc-fit', 'tier' => 'E', 'focus' => 4, 'gradient' => 'focus',
                    'title' => ['en' => 'Is this service for me?'], 'data' => ['service' => $slug]],
                ['type' => 'steps', 'id' => 'svc-path', 'tier' => 'I', 'focus' => 3, 'title' => ['en' => 'The certification path'],
                    'data' => ['items' => [
                        ['title' => ['en' => 'C1 · Supervised task'], 'text' => ['en' => 'A foundational task under supervision, with evidence recorded.']],
                        ['title' => ['en' => 'C2 · Repeatable unit'], 'text' => ['en' => 'A repeatable output unit reviewed against a rubric.']],
                        ['title' => ['en' => 'C3 · Independent delivery'], 'text' => ['en' => 'Independent delivery within the authorised scope.']],
                        ['title' => ['en' => 'C4 · Assessor or lead'], 'text' => ['en' => 'Calibrated assessment or lead responsibility.']],
                    ]]],
                ['type' => 'cards', 'id' => 'svc-tasks', 'tier' => 'X', 'focus' => 2, 'title' => ['en' => 'Certification tasks'],
                    'data' => ['items' => [['title' => ['en' => 'Task set not yet published'],
                        'text' => ['en' => 'No task set for this service is published yet. Competencies, evidence rules and the assessor are set per service before any assessment is run.']]]]],
                ['type' => 'definitions', 'id' => 'svc-week', 'tier' => 'I', 'focus' => 2, 'title' => ['en' => 'What a week looks like'],
                    'data' => ['items' => [
                        ['term' => ['en' => 'Directive'], 'def' => ['en' => 'Set on Monday by the service leader, from a funded client need.']],
                        ['term' => ['en' => 'Report'], 'def' => ['en' => 'Submitted on Friday, against the accepted output unit.']],
                        ['term' => ['en' => 'Independent sample'], 'def' => ['en' => '{{ doctrine.quality_sample }} of submitted reports are re-reviewed independently.']],
                    ]]],
                ['type' => 'bars', 'id' => 'svc-seats', 'tier' => 'I', 'focus' => 3, 'title' => ['en' => 'Seats and capacity'],
                    'data' => ['items' => [
                        ['label' => ['en' => 'Active in {{ country.name }}'], 'value' => 0, 'max' => 1, 'unit' => ['en' => 'of 1 declared']],
                    ], 'note' => ['en' => 'Design ceiling: {{ experts.per_service }} experts per service, a capacity convention and not a headcount. Independent sample: {{ doctrine.quality_sample }}. Seats open only when the service is activated.']]],
                ['type' => 'cta', 'id' => 'svc-mid-cta', 'tier' => 'E', 'focus' => 5, 'gradient' => $s['macro'],
                    'title' => ['en' => 'Reserve my place in the first cohort'],
                    'data' => ['text' => ['en' => 'A reserved place is a registration, not an assessment or a contract.']],
                    'cta' => ['label' => ['en' => 'Reserve my place'], 'href' => "/app/register/?service={$slug}", 'class' => 'btn-peak']],
                ['type' => 'definitions', 'id' => 'svc-delivery', 'tier' => 'X', 'focus' => 1, 'title' => ['en' => 'Delivery contract (proposed)'],
                    'data' => ['items' => [
                        ['term' => ['en' => 'Likely buyer'], 'def' => $s['likely_buyer']],
                        ['term' => ['en' => 'Acceptance and boundary'], 'def' => $s['acceptance_boundary']],
                        ['term' => ['en' => 'Evidence level'], 'def' => ['en' => 'E0 · proposed. Not yet evidenced by a delivered client outcome.']],
                    ]]],
                ['type' => 'notice', 'id' => 'svc-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'No work, income or certificate is promised. Regulated activity needs qualified sign-off; see the statement.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
                ['type' => 'cards', 'id' => 'svc-projects', 'tier' => 'I', 'focus' => 2, 'title' => ['en' => 'Projects that may need this service'],
                    'data' => ['items' => $projects ?: [['title' => ['en' => 'No project register entry names this service yet'], 'text' => ['en' => 'Projects are listed only where a register entry consumes the service.']]]]],
                ['type' => 'cards', 'id' => 'svc-related', 'tier' => 'I', 'focus' => 2, 'title' => ['en' => 'Related services in this macro work'],
                    'data' => ['items' => $related]],
                ['type' => 'faq', 'id' => 'svc-faq', 'tier' => 'E', 'focus' => 2, 'title' => ['en' => 'Questions'],
                    'data' => ['items' => [
                        ['q' => ['en' => 'Do I need a degree?'], 'a' => ['en' => 'Degrees and professional registration are required only where the law of the role requires them. Prior experience is considered.']],
                        ['q' => ['en' => 'Is there a fee?'], 'a' => ['en' => 'No. There is no application fee and no investment requirement.']],
                    ]]],
                ['type' => 'cta', 'id' => 'svc-final', 'tier' => 'E', 'focus' => 5, 'gradient' => $s['macro'],
                    'title' => ['en' => 'Apply now'], 'data' => ['text' => ['en' => 'Takes about four minutes. You can withdraw at any time.']],
                    'cta' => ['label' => ['en' => 'Apply now'], 'href' => "/app/register/?service={$slug}", 'class' => 'btn-peak']],
            ],
        ];
        return Page::render(self::withLimits($doc));
    }

    public static function project(string $slug): array
    {
        $p = Content::get('projects', $slug);
        $consumed = [];
        foreach ($p['services_consumed'] ?? [] as $svc) {
            $rec = Content::get('services', $svc);
            if ($rec) {
                $consumed[] = ['title' => $rec['title'], 'text' => $rec['short'], 'href' => "/services/{$svc}/"];
            }
        }
        $doc = [
            'id' => 'project-' . $slug, 'path' => "/projects/{$slug}/", 'title' => $p['title'],
            'description' => $p['summary'], 'blocks' => [
                ['type' => 'hero', 'id' => 'prj-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => 'focus',
                    'title' => $p['title'], 'data' => ['eyebrow' => ['en' => 'Project register'], 'lead' => $p['summary']],
                    'cta' => ['label' => ['en' => 'Contribute to this project'], 'href' => '/app/register/', 'class' => 'btn-peak']],
                ['type' => 'prose', 'id' => 'prj-outcome', 'tier' => 'E', 'focus' => 3, 'title' => ['en' => 'What this project is'],
                    'data' => ['paragraphs' => [$p['summary']['en'] ?? $p['summary']]]],
                ['type' => 'definitions', 'id' => 'prj-meta', 'tier' => 'X', 'focus' => 1, 'title' => ['en' => 'Register record'],
                    'data' => ['items' => [
                        ['term' => ['en' => 'Recorded status'], 'def' => $p['status']],
                        ['term' => ['en' => 'Evidence level'], 'def' => $p['evidence']],
                        ['term' => ['en' => 'Meta solution'], 'def' => $p['meta_solution'] ?? 'none recorded'],
                    ]]],
                ['type' => 'cards', 'id' => 'prj-services', 'tier' => 'I', 'focus' => 3, 'title' => ['en' => 'Services this project would consume'],
                    'data' => ['items' => $consumed ?: [['title' => ['en' => 'No services recorded'], 'text' => ['en' => 'This entry consumes no catalogue service.']]]]],
                ['type' => 'notice', 'id' => 'prj-status', 'tier' => 'X', 'focus' => 2, 'title' => ['en' => 'Evidence status'],
                    'data' => ['text' => ['en' => 'Recorded status and evidence level are shown as held in the register. They have not been independently verified.']]],
                ['type' => 'cta', 'id' => 'prj-split-cta', 'tier' => 'E', 'focus' => 5, 'gradient' => 'focus',
                    'title' => ['en' => 'Three ways to take part'],
                    'data' => ['text' => ['en' => 'Join as an expert, become a client, or ask about investment. None is a promise of work or return.']],
                    'cta' => ['label' => ['en' => 'Join as an expert'], 'href' => '/app/register/', 'class' => 'btn-peak']],
                ['type' => 'notice', 'id' => 'prj-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'A register entry is not a funded business. The status above is as recorded and has not been independently verified.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
            ],
        ];
        return Page::render(self::withLimits($doc));
    }

    public static function layer(string $slug): array
    {
        $l = Content::get('layers', $slug);
        $doc = [
            'id' => 'layer-' . $slug, 'path' => "/ontology/{$slug}/", 'title' => $l['title'],
            'description' => $l['summary'], 'blocks' => [
                ['type' => 'hero', 'id' => 'ont-hero', 'tier' => 'X', 'focus' => 5, 'gradient' => 'calm',
                    'title' => $l['title'], 'data' => ['eyebrow' => ['en' => 'Ontology · layer ' . $l['layer']], 'lead' => $l['question']]],
                ['type' => 'prose', 'id' => 'ont-body', 'tier' => 'X', 'focus' => 3, 'title' => ['en' => 'Definition'],
                    'data' => ['paragraphs' => [$l['summary']]]],
                ['type' => 'cta', 'id' => 'ont-cta', 'tier' => 'E', 'focus' => 4, 'title' => ['en' => 'Back to the ontology'],
                    'cta' => ['label' => ['en' => 'Read the ontology'], 'href' => '/ontology/', 'class' => 'btn-focus']],
            ],
        ];
        if ($slug === 'meta-solutions') {
            $doc['blocks'][] = ['type' => 'table', 'id' => 'ont-meta', 'tier' => 'X', 'focus' => 2, 'title' => ['en' => 'Instruments and their evidence status'],
                'data' => ['head' => [['en' => 'Instrument'], ['en' => 'Status'], ['en' => 'What it is']],
                    'rows' => array_map(static fn ($i) => [$i['title'], ['en' => $i['status']], $i['text']], $l['items'])]];
        }
        return Page::render(self::withLimits($doc));
    }

    public static function governorate(string $slug): array
    {
        $iso = Country::defaultIso();
        $r = Country::region($iso, $slug);
        $name = tr_loc($r['name']);
        $count = 0;
        try {
            $count = (int) DB::value('SELECT COUNT(*) FROM applicants a
                JOIN countries c ON c.id = a.country_id
                JOIN regions r ON r.id = a.region_id
                WHERE c.iso = ? AND r.slug = ?', [$iso, $slug]);
        } catch (\Throwable) {
            $count = 0;
        }
        $doc = [
            'id' => 'gov-' . $slug, 'path' => '/' . $iso . "/{$slug}/", 'title' => $r['name'],
            'description' => ['en' => 'Register from ' . $name['en'] . ' and choose a service.'], 'blocks' => [
                ['type' => 'hero', 'id' => 'gov-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => 'focus',
                    'title' => $r['name'], 'data' => ['eyebrow' => ['en' => 'Governorate · {{ country.name }}'],
                    'lead' => ['en' => 'Services are chosen by fit and funded demand, not by quota. Register from here to be considered for an assessment.']],
                    'cta' => ['label' => ['en' => 'Register from ' . $name['en']], 'href' => '/app/register/?region=' . $slug, 'class' => 'btn-peak']],
                ['type' => 'kpis', 'id' => 'gov-kpis', 'tier' => 'I', 'focus' => 2, 'title' => ['en' => 'Registered in this governorate'],
                    'data' => ['items' => [['label' => ['en' => 'Registered interest (platform count)'], 'value' => (string) $count]]]],
                ['type' => 'notice', 'id' => 'gov-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'No regional quota exists. A count here is interest, not an assessed or employed person.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
            ],
        ];
        return Page::render(self::withLimits($doc));
    }

    private static function home(array $doc): array
    {
        $blocks = [];
        foreach ($doc['blocks'] as $b) {
            $blocks[] = $b;
            if (($b['id'] ?? '') === 'home-mid-cta') {
                $blocks[] = self::govBlock();
            }
        }
        $doc['blocks'] = $blocks;
        return $doc;
    }

    private static function govBlock(): array
    {
        $regions = [];
        foreach (Country::regions(Country::defaultIso()) as $r) {
            $regions[] = ['slug' => $r['slug'], 'name' => $r['name'], 'col' => $r['col'], 'row' => $r['row']];
        }
        return ['type' => 'gov-map', 'id' => 'home-gov-map', 'tier' => 'E', 'focus' => 3,
            'title' => ['en' => 'Find your governorate'],
            'data' => ['regions' => $regions, 'note' => ['en' => 'Schematic tile map, not to scale. Each tile links to that governorate.']],
            'cta' => ['label' => ['en' => 'Register from my governorate'], 'href' => '/app/register/', 'class' => 'btn-focus']];
    }

    private static function macroIndex(): array
    {
        $cards = [];
        foreach (Content::all('macros') as $m) {
            $cards[] = ['title' => $m['title'], 'text' => $m['problem'], 'href' => "/macro-works/{$m['slug']}/"];
        }
        return ['id' => 'macro-index', 'path' => '/macro-works/', 'title' => ['en' => 'Macro works'],
            'description' => ['en' => 'Five macro works group the service catalogue.'], 'blocks' => [
                ['type' => 'hero', 'id' => 'mwi-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => 'calm',
                    'title' => ['en' => 'Five macro works'], 'data' => ['lead' => ['en' => 'Every service belongs to exactly one macro work.']]],
                ['type' => 'cards', 'id' => 'mwi-cards', 'tier' => 'I', 'focus' => 3, 'title' => ['en' => 'Choose a macro work'], 'data' => ['items' => $cards]],
                ['type' => 'notice', 'id' => 'mwi-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'Macro works organise the catalogue. They are not departments, licences or guaranteed demand.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
            ]];
    }

    private static function servicesIndex(): array
    {
        $blocks = [['type' => 'hero', 'id' => 'svci-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => 'calm',
            'title' => ['en' => '{{ counts.services }} services'],
            'data' => ['lead' => ['en' => 'Each service is a proposal until its buyer, price, legal scope and cash are established.']]]];
        foreach (Content::all('macros') as $m) {
            $items = [];
            foreach ($m['services'] as $ref) {
                $items[] = self::serviceCard(Content::get('services', $ref['slug']));
            }
            $blocks[] = ['type' => 'service-catalog', 'id' => 'svci-' . $m['slug'], 'tier' => 'I', 'focus' => 3,
                'gradient' => $m['slug'], 'title' => $m['title'], 'data' => ['items' => $items]];
        }
        $blocks[] = ['type' => 'notice', 'id' => 'svci-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
            'data' => ['text' => ['en' => 'Listing a service is not an offer of work. Read the statement for what each service does not promise.']],
            'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']];
        return ['id' => 'services-index', 'path' => '/services/', 'title' => ['en' => 'Services'],
            'description' => ['en' => 'The service catalogue, grouped by macro work.'], 'blocks' => $blocks];
    }

    private static function projectsIndex(): array
    {
        $cards = [];
        foreach (Content::all('projects') as $p) {
            $cards[] = ['title' => $p['title'], 'text' => $p['summary'], 'href' => "/projects/{$p['slug']}/"];
        }
        return ['id' => 'projects-index', 'path' => '/projects/', 'title' => ['en' => 'Project register'],
            'description' => ['en' => 'The project register, with recorded status and evidence level.'], 'blocks' => [
                ['type' => 'hero', 'id' => 'prji-hero', 'tier' => 'E', 'focus' => 5, 'gradient' => 'focus',
                    'title' => ['en' => '{{ counts.projects }} projects in the register'],
                    'data' => ['lead' => ['en' => 'A register is not a list of funded businesses. Each entry shows what is recorded and at what evidence level.']]],
                ['type' => 'cards', 'id' => 'prji-cards', 'tier' => 'I', 'focus' => 3, 'title' => ['en' => 'All register entries'], 'data' => ['items' => $cards]],
                ['type' => 'notice', 'id' => 'prji-limits', 'tier' => 'I', 'focus' => 1, 'title' => ['en' => 'Declared limits'],
                    'data' => ['text' => ['en' => 'Statuses are as recorded and have not been independently verified.']],
                    'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']],
            ]];
    }

    /** Card for a service, with its macro gradient. */
    private static function serviceCard(array $s): array
    {
        return ['title' => $s['title'], 'text' => $s['short'], 'href' => "/services/{$s['slug']}/",
            'meta' => ['en' => 'No. ' . str_pad((string) $s['number'], 3, '0', STR_PAD_LEFT) . ' · ' . $s['placement']]];
    }

    /** Every public page without its own notice gets the generic statement notice (B5). */
    private static function withLimits(array $doc): array
    {
        foreach ($doc['blocks'] ?? [] as $b) {
            if (($b['type'] ?? '') === 'notice') {
                return $doc;
            }
        }
        $doc['blocks'][] = ['type' => 'notice', 'id' => 'page-limits', 'tier' => 'I', 'focus' => 1,
            'title' => ['en' => 'Declared limits'],
            'data' => ['text' => ['en' => 'W5D does not promise employment, income or certification. Capacity figures are design equations, not headcounts. Current certified experts: {{ experts.current }}.']],
            'cta' => ['label' => ['en' => 'Read the statement'], 'href' => '/trust/statement/', 'class' => 'btn-link']];
        return $doc;
    }
}
