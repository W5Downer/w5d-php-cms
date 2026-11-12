# Page data: the JSON content model

Every page, service, macro, project, declaration and ontology layer is **one JSON file**. There is
no shared "page data" document, which is why two pages can never contradict each other.

## Where files live

```
data/content/
  site.json              nav groups, footer columns, global settings
  pages/{id}.json        one file per page         → /{path}/
  services/{slug}.json   130 services              → /services/{slug}/
  macros/{slug}.json     5 macro works             → /macro-works/{slug}/
  projects/{slug}.json   25 register entries       → /ecosystem/{slug}/
  declarations/{slug}.json  7 founder's declarations → /declarations/{slug}/
  layers/{key}.json      6 ontology layers         → /ontology/{key}/
```

## Record fields (top level)

| Field | Type | Notes |
|---|---|---|
| `id` | string | must match the filename |
| `slug` | string | required for collection records; drives the URL |
| `path` | string | pages only; explicit route, else `/{id}/` |
| `kind` | enum `page_kind` | `page`, `index`, `detail`, `legal`, `register`, `root` |
| `nav_group` | enum `nav_group` | `company`, `dimensions`, `ecosystem`, `services`, `studio`, `trust`, `utility` |
| `audience` | enum `audience` | `public`, `press`, `candidate`, `partner`, `client`, `institution`, `member`, `staff` |
| `status` | enum `status` | one of the nine ladder words |
| `evidence` | enum `evidence` | `E0`…`E3` |
| `layer` | enum `layer` | `L2`, `L3`, `pillar` (register layer — *not* the ontology layer) |
| `ontology_layer` | enum `ontology_layer` | `academies`, `expert-teams`, `services`, `macro-works`, `meta-solutions`, `projects` |
| `meta_solution` | enum `meta_solution` | which cross-cutting instrument a project instantiates |
| `services_consumed` | string[] | service slugs a project consumes (§4.5) |
| `title` / `nav_label` / `summary` / `description` | `{en,fr,ar}` | localised strings |
| `blocks` | array | the page body, in order |

**Validation:** every enum value is checked against `config/variables.php` on load. An unknown value
is an error — the build fails rather than publishing a typo.

## Blocks

A block is `{ "type": ..., "id": ..., "title": ..., "data": { ... } }`. `type` must be a key in
`types.block`. Available types:

`hero`, `prose`, `cards`, `table`, `kpis`, `steps`, `timeline`, `definitions`, `register`, `index`,
`notice`, `quote`, `bars`, `cta`, `form`, `roster`, `contact-desks`, `service-catalog`, `stack`,
`carousel`, `rich`.

Every block has a renderer at `views/_blocks/{type}.php`, and any template may override it at
`views/{template}/blocks/{type}.php`.

## Localisation inside blocks

Any string may be a map instead of a plain string:

```json
"title": { "en": "Services", "fr": "Services", "ar": "الخدمات" }
```

The renderer picks the active language and falls back to English. UI chrome (buttons, labels,
aria text) is **not** in JSON — it lives in `lang/{code}.php`.

## Variables inside strings

```json
"lead": "We registered {{ counts.services.value }} services across {{ counts.macros.value }} macros."
```

Tokens resolve from `config/variables.php` at render time. See `docs/variables.md`.

## Inline markup allowed in prose

`**bold**`, `*italic*`, `` `code` ``, `[label](target)`, `[[https://…]]`.

A link target may be a route key — `page:about`, `service:publishing`, `macro:cultures`,
`project:kernid`, `declaration:fifth-dimension`, `layer:projects` — or a plain URL. A route key that
does not exist renders as a *pending* link, never a silent 404.

## Worked example

```json
{
  "id": "translation-hubs",
  "slug": "translation-hubs",
  "number": 6,
  "macro": "cultures",
  "placement": "real",
  "status": "building",
  "audience": "client",
  "nav_group": "services",
  "kind": "detail",
  "title": { "en": "Translation & Localisation Hubs" },
  "summary": { "en": "Arabic–French–English professional translation." },
  "blocks": [
    { "type": "hero", "id": "top", "data": { "grand": { "en": "Translation & Localisation Hubs" } } },
    { "type": "prose", "title": { "en": "What it delivers" }, "data": { "body": [ { "en": "…" } ] } }
  ]
}
```

## Adding a page

1. Create `data/content/pages/my-page.json`.
2. Run `php bin/build.php` — it appears at `/my-page/` in all three languages.
3. Add `"my-page"` to a `nav` group in `data/content/site.json` if it should be in the menu.
