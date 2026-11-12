# The variable registry

Every number, ratio, price and threshold on the site is defined **once**, in
`config/variables.php`. Nothing is typed into a template or a JSON page.

## Why

The owner asked that "any number or any type … is a variable to be defined in PHP files, so the
owner can update them." Changing `130` to `128` in one file updates every page, chart, table and
count on the next build.

## Referring to a variable

In page JSON or prose:

```
{{ counts.services.value }}
```

In PHP:

```php
v('counts.services');            // formatted for display
vlabel('counts.services');        // the human label
vnote('counts.services');         // provenance / caveat
\W5D\Registry::vars()->value('counts.services');  // raw scalar
```

## Entry shape

```php
'services' => [
    'value'  => 130,          // the figure
    'format' => 'int',        // int|decimal|money|percent|compact|date|text|bool
    'unit'   => 'experts',    // optional suffix
    'label'  => 'Registered services',
    'note'   => '…',          // caveat, rendered where the figure is explained
    'source' => 'data/services.md',
],
```

## Groups

| Group | Holds |
|---|---|
| `counts` | every count: dimensions, macros, services, experts, projects, declarations … |
| `types` | **type vocabularies** (see below) |
| `caps` | capacity ratios: manager caps, experts per service, managers per service |
| `money` | licence rate, salary, parts, payroll |
| `time` | weekly cadence, timezone, last verification date |
| `doctrine` | thresholds: RAG bands, sampling, SLAs, contrast, acyclic flag |
| `site` | names, address, tagline, licence sentence |
| `open_value` | the shared "to be confirmed" phrase |

## Type vocabularies

A type is a fixed list of allowed values with human labels. Dropdowns in the admin panel, badge
colours and load-time validation all derive from these lists.

```php
'status' => [
    'type' => true,
    'label' => 'Status word',
    'default' => 'draft',
    'options' => [ 'concept' => 'Concept', 'planned' => 'Planned', /* … */ 'live' => 'Live' ],
    'ladder' => [ 'concept' => 1, /* … */ 'live' => 9 ],
],
```

Shipped vocabularies: `audience`, `member`, `status`, `evidence`, `layer`, `placement`, `horizon`,
`service_status`, `page_kind`, `nav_group`, `block`, `link_kind`, `contact_reason`,
**`ontology_layer`**, **`meta_solution`**, **`expert_team`**.

Validation happens in `Content::validate()`: a record declaring an unknown value is rejected.

## Ontology variables (§4)

| Token | Value | Meaning |
|---|---|---|
| `counts.ontology_layers` | 6 | layers in the stack |
| `counts.expert_teams_total` | 130 | one team per service (design) |
| `counts.meta_solutions` | 6 | cross-cutting instruments |
| `caps.managers_per_service` | 100 | dossier convention |
| `caps.manager_experts` | 12 | README hard cap |
| `caps.manager_experts_soft` | 10 | soft target ≈ dossier's "approximately ten" |
| `doctrine.quality_sample` | 5% | **dossier recommendation** |
| `doctrine.sampling_readme_per_service` | 20 reports | README leader schedule |
| `doctrine.sampling_readme_percent` | 2% | what the README rule implies |
| `doctrine.dyon_enforcement_verified` | false | proposed, not verified |
| `doctrine.acyclic_only_time_unrolled` | true | acyclicity caveat |
| `doctrine.live_means_employment` | false | "Live" ≠ employment |

The two sampling figures are **deliberately kept apart** so no page can present 5% as a site rule.

## Overriding without editing the registry

`config/variables.local.php` (if present) is deep-merged over the registry at boot. It is the file
the admin panel writes to, so owner edits never touch the versioned source.

## Formatting

`Formatter::apply()` handles `int`, `decimal`, `compact` (1,000 → 1k), `money`, `percent`, `date`,
`date-short`, `month`, `year` and `bool`, with English, French and Arabic separators and month names.
