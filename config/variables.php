<?php
declare(strict_types=1);

/**
 * W5D Site Engine — VARIABLE REGISTRY.
 *
 * Every number and every type vocabulary used anywhere on the site is defined
 * here, once. Templates, page JSON files and the admin panel all read from this
 * registry; nothing is hard-coded in a view or a JSON file.
 *
 * ── How page JSON refers to a figure ────────────────────────────────────────
 *   "We registered {{ var.counts.services.value }} services."
 * The token is resolved at render time from this file. Change 130 to 128 here
 * and every page, chart, table and count updates on the next build.
 *
 * ── Shape of an entry ───────────────────────────────────────────────────────
 *   value   scalar (int|float|string|bool) — the figure itself
 *   format  int | decimal | money | percent | compact | text | date  (optional)
 *   unit    short suffix shown after the number, e.g. "TND", "h"  (optional)
 *   label   human label shown in the admin panel and on labels (optional)
 *   note    provenance / caveat, rendered where the figure is explained
 *   source  where the figure comes from, shown on the status register
 *   type    marks the entry as a TYPE VOCABULARY (list of allowed values)
 *
 * Types are what "the audience type, the member type, …" resolves to. A type
 * has `options` (key => label) and optionally `default`. Dropdowns in the admin
 * panel, badge colours and validation all derive from these lists.
 *
 * Owner edits this file only. Do not put content prose here.
 */

return [

    // ═══════════════════════════════════════════════════════════════════════
    // 1. COUNTS — every figure that appears on the site
    // ═══════════════════════════════════════════════════════════════════════
    'counts' => [
        'dimensions'      => ['value' => 5, 'format' => 'int', 'label' => 'Dimensions', 'note' => 'Three of space, one of information/structure, one of possibility/modality.'],
        'macros'          => ['value' => 5, 'format' => 'int', 'label' => 'Macro works', 'note' => 'Cultures, Built Environment, Sciences, Governance, Technology.'],
        'services'        => ['value' => 130, 'format' => 'int', 'label' => 'Registered services', 'source' => 'data/services.md'],
        'services_per_macro' => ['value' => 26, 'format' => 'int', 'label' => 'Services per macro work'],
        'service_capacity'  => ['value' => 1000, 'format' => 'compact', 'label' => 'Designed capacity per service', 'unit' => 'experts', 'note' => 'Design ceiling, not current headcount.'],

        // ── §4 six-layer ontology ───────────────────────────────────────────
        'ontology_layers' => ['value' => 6, 'format' => 'int', 'label' => 'Layers in the stacked ontology', 'note' => 'Academies (L0) → Expert Teams (L1) → Services (L2) → Macro Works (L3) → Meta Solutions (L4) → Projects (L5).'],
        'expert_teams_total' => ['value' => 130, 'format' => 'int', 'label' => 'Expert teams at design ceiling', 'note' => 'One delivery team per service. A design equation, not a headcount.'],
        'meta_solutions'  => ['value' => 6, 'format' => 'int', 'label' => 'Meta solutions (cross-cutting instruments)', 'note' => 'Dyon, weekly work engine, split DB/rollup, sampling, KernID, SelfAI. Not a sixth macro.'],
        'experts_target'  => ['value' => 130000, 'format' => 'compact', 'label' => 'Expert employees at design ceiling'],
        'experts_current' => ['value' => 0, 'format' => 'int', 'label' => 'Expert employees today', 'note' => 'No expert employee is onboarded at the time of this build.'],
        'managers_target' => ['value' => 13000, 'format' => 'compact', 'label' => 'Team managers at design ceiling'],
        'leaders_target'  => ['value' => 130, 'format' => 'int', 'label' => 'Service leaders at design ceiling'],
        'owners'          => ['value' => 1, 'format' => 'int', 'label' => 'Owner'],
        'accounts_total'  => ['value' => 143131, 'format' => 'compact', 'label' => 'Total accounts at design ceiling', 'note' => '1 owner + 130 leaders + 13,000 managers + 130,000 experts.'],
        'developers_current' => ['value' => 26, 'format' => 'int', 'label' => 'Developers delivering today', 'unit' => 'Tunisia', 'note' => 'Real delivery capacity today, distinct from the design ceiling.'],
        'departments'     => ['value' => 16, 'format' => 'int', 'label' => 'Departments in the 2030B registry'],
        'pillars'         => ['value' => 9, 'format' => 'int', 'label' => 'MetaX pillars'],
        'harms'           => ['value' => 25, 'format' => 'int', 'label' => 'Structural harms (HX-01…HX-25)'],
        'harm_strata'     => ['value' => 6, 'format' => 'int', 'label' => 'Harm strata'],
        'declarations'    => ['value' => 7, 'format' => 'int', 'label' => "Founder's declarations"],
        'principles_2030b' => ['value' => 7, 'format' => 'int', 'label' => '2030B principles', 'note' => 'An earlier draft said eleven; the declaration lists seven.'],
        'principles_site' => ['value' => 4, 'format' => 'int', 'label' => 'Working principles of this site', 'note' => 'Sidq, falsifiability, declared limits, correctability.'],
        'projects'        => ['value' => 25, 'format' => 'int', 'label' => 'Programmes and projects in the register'],
        'projects_l2'     => ['value' => 5, 'format' => 'int', 'label' => 'L2 programmes'],
        'projects_l3'     => ['value' => 20, 'format' => 'int', 'label' => 'L3 projects and pillars'],
        'status_words'    => ['value' => 9, 'format' => 'int', 'label' => 'Status words on the status ladder'],
        'evidence_levels' => ['value' => 4, 'format' => 'int', 'label' => 'Evidence levels (E0–E3)'],
        'weeks_per_year'  => ['value' => 52, 'format' => 'int', 'label' => 'Reporting weeks per year'],
        'reports_per_year' => ['value' => 6760000, 'format' => 'compact', 'label' => 'Weekly report rows per year at design ceiling', 'note' => '130,000 experts × 52 weeks.'],
        'roadmap_first_year' => ['value' => 2025, 'format' => 'int', 'label' => 'First published roadmap year'],
        'roadmap_last_year'  => ['value' => 2030, 'format' => 'int', 'label' => 'Target year for the full ecosystem'],
        'open_items'      => ['value' => 69, 'format' => 'int', 'label' => 'Facts not yet supplied', 'note' => 'Shown as grey chips. None is guessed.'],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 2. TYPES — vocabularies that other systems validate against
    // ═══════════════════════════════════════════════════════════════════════
    'types' => [

        // Who a page or a message is addressed to.
        'audience' => [
            'type' => true,
            'label' => 'Audience type',
            'default' => 'public',
            'options' => [
                'public'    => 'Public visitor',
                'press'     => 'Journalist',
                'candidate' => 'Candidate or expert employee',
                'partner'   => 'Partner or investor',
                'client'    => 'Client or customer',
                'institution' => 'Public institution',
                'member'    => 'Signed-in member',
                'staff'     => 'W5D staff',
            ],
        ],

        // Account classification used by the CMS and the org model.
        'member' => [
            'type' => true,
            'label' => 'Member type',
            'default' => 'expert',
            'options' => [
                'owner'    => 'Owner',
                'admin'    => 'Administrator',
                'leader'   => 'Service leader',
                'manager'  => 'Team manager',
                'expert'   => 'Expert employee',
                'investor' => 'Investor',
                'customer' => 'Customer',
                'visitor'  => 'Registered visitor',
            ],
        ],

        // Nine status words. Order is the status ladder, lowest to highest.
        'status' => [
            'type' => true,
            'label' => 'Status word',
            'default' => 'draft',
            'options' => [
                'concept'          => 'Concept',
                'planned'          => 'Planned',
                'gated'            => 'Gated',
                'research-preview' => 'Research preview',
                'specification'    => 'Specification',
                'draft'            => 'Draft',
                'building'         => 'Building',
                'operational'      => 'Operational',
                'live'             => 'Live',
            ],
            'ladder' => [
                'concept' => 1, 'planned' => 2, 'gated' => 3, 'research-preview' => 4,
                'specification' => 5, 'draft' => 6, 'building' => 7, 'operational' => 8, 'live' => 9,
            ],
        ],

        'evidence' => [
            'type' => true,
            'label' => 'Evidence level',
            'default' => 'E1',
            'options' => [
                'E0' => 'No evidence shown',
                'E1' => 'Declared and specified',
                'E2' => 'Publicly observable',
                'E3' => 'Independently checked',
            ],
            'description' => [
                'E0' => 'The claim is stated, nothing checkable is shown yet.',
                'E1' => 'Stated by the founder or written as a specification; not independently checked.',
                'E2' => 'A public artefact exists and was observed on the verification date.',
                'E3' => 'Checked by a party outside W5D, with the report published.',
            ],
        ],

        // Register layer: an L2 programme contains L3 projects; "pillar" is the
        // MetaX cross-cutting layer used for MacroLifeTech.
        'layer' => [
            'type' => true,
            'label' => 'Register layer',
            'default' => 'L3',
            'options' => [
                'L2'     => 'L2 programme',
                'L3'     => 'L3 project',
                'pillar' => 'Pillar',
            ],
        ],

        // Real / virtual placement, used by services and macro works.
        'placement' => [
            'type' => true,
            'label' => 'Placement',
            'default' => 'real',
            'options' => [
                'real'          => 'Real 1.0',
                'between'       => 'Between 1.0',
                'virtual-core'  => 'Real 0.5 / Virtual 0.5',
            ],
        ],

        // Nine-step status ladder percentage, derived from `types.status.ladder`.
        'horizon' => [
            'type' => true,
            'label' => 'Horizon',
            'default' => 'macro',
            'options' => [
                'meta'   => 'Meta',
                'macro'  => 'Macro',
                'beyond' => 'Beyond',
            ],
        ],

        'service_status' => [
            'type' => true,
            'label' => 'Service publication status',
            'default' => 'draft',
            'options' => [
                'published' => 'Published',
                'draft'     => 'Draft',
                'archived'  => 'Archived',
            ],
        ],

        'page_kind' => [
            'type' => true,
            'label' => 'Page kind',
            'default' => 'page',
            'options' => [
                'page'     => 'Standard page',
                'index'    => 'Collection index',
                'detail'   => 'Detail page',
                'legal'    => 'Legal / policy',
                'register' => 'Register or table',
                'root'     => 'Home page',
            ],
        ],

        'nav_group' => [
            'type' => true,
            'label' => 'Navigation group',
            'default' => 'company',
            'options' => [
                'company'  => 'Company',
                'dimensions' => 'Dimensions',
                'ecosystem'=> 'Ecosystem',
                'services' => 'Services',
                'studio'   => 'Studio',
                'trust'    => 'Trust',
                'utility'  => 'Utility',
            ],
        ],

        // Blocks are validated against this list; each key must have a renderer
        // in views/_blocks/{key}.php and every template may override it.
        'block' => [
            'type' => true,
            'label' => 'Content block',
            'default' => 'prose',
            'options' => [
                'hero'      => 'Hero',
                'prose'     => 'Prose section',
                'cards'     => 'Card grid',
                'table'     => 'Table',
                'kpis'      => 'KPI strip',
                'steps'     => 'Numbered steps',
                'timeline'  => 'Timeline',
                'definitions' => 'Definition list',
                'register'  => 'Status register',
                'index'     => 'Collection index',
                'notice'    => 'Notice / declared limits',
                'quote'     => 'Quotation',
                'carousel'  => 'Carousel',
                'bars'      => 'Bar comparison',
                'cta'       => 'Call to action',
                'form'      => 'Form (disabled until configured)',
                'rich'      => 'Free HTML (escaped unless trusted)',
                'roster'    => 'Roster of people',
                'contact-desks' => 'Contact desks',
                'service-catalog' => 'Service catalogue',
                'stack'     => 'Six-layer stack diagram',
                'sparkline' => 'Inline trend',
            ],
        ],

        'link_kind' => [
            'type' => true,
            'label' => 'Link kind',
            'default' => 'internal',
            'options' => [
                'internal' => 'Internal page',
                'external' => 'External URL',
                'mailto'   => 'Email address',
                'download' => 'File download',
                'pending'  => 'Not supplied yet',
            ],
        ],

        'contact_reason' => [
            'type' => true,
            'label' => 'Contact reason',
            'default' => 'other',
            'options' => [
                'build'      => 'Build with W5D',
                'press'      => 'Press',
                'careers'    => 'Careers',
                'correction' => 'Correction',
                'security'   => 'Security',
                'other'      => 'Other',
            ],
        ],

        // ═══════════════════════════════════════════════════════════════════
        // §4 — the six-layer stacked ontology.
        //
        // Projects instantiate Meta Solutions; Meta Solutions operate on Macro
        // Works; Macro Works contain Services; Services are delivered by Expert
        // Teams; Academies form the competence that Expert Teams require.
        //
        // The layer keys are the canonical numbers used in the stack diagram and
        // in `data/content/layers/*.json`. Order is top (L5) to bottom (L0) so
        // the array reads like the diagram.
        // ═══════════════════════════════════════════════════════════════════
        'ontology_layer' => [
            'type' => true,
            'label' => 'Ontology layer',
            'default' => 'service',
            'options' => [
                'projects'       => 'L5 · Projects',
                'meta-solutions' => 'L4 · Meta Solutions',
                'macro-works'    => 'L3 · Macro Works',
                'services'       => 'L2 · Services',
                'expert-teams'   => 'L1 · Expert Teams',
                'academies'      => 'L0 · Academies',
            ],
            'ladder' => [
                'academies' => 0, 'expert-teams' => 1, 'services' => 2,
                'macro-works' => 3, 'meta-solutions' => 4, 'projects' => 5,
            ],
            'description' => [
                'academies'      => 'Forms the assessed competence that teams require.',
                'expert-teams'   => 'Delivers a service: one manager plus about ten experts.',
                'services'       => 'The atomic, contractible unit of commissioned work.',
                'macro-works'    => 'The domain of accountability that contains 26 services.',
                'meta-solutions' => 'A reusable instrument any service can invoke. Not a sixth macro.',
                'projects'       => 'A time-bound instantiation that consumes services.',
            ],
        ],

        // The six cross-cutting instruments. Deliberately NOT a sixth macro:
        // they sit above the macro partition so they never become a domain.
        'meta_solution' => [
            'type' => true,
            'label' => 'Meta solution',
            'default' => 'dyon',
            'options' => [
                'dyon'            => 'Dyon — identity, competency and acceptance records',
                'weekly-engine'   => 'Weekly work engine — Monday directive to Saturday rollup',
                'split-rollup'    => 'Split SQLite databases and rollup-first dashboards',
                'sampling'        => 'Sampling and assurance',
                'kernid'          => 'KernID — identity and credential meta-layer (concept)',
                'selfai'          => 'SelfAI — self-training AI agent meta-layer (concept)',
                'ontology'        => 'The five-macro / 130-service ontology itself',
                'academy-layer'   => 'Academy and credentialing meta-layer',
                'publication'     => 'Publication and dissemination meta-layer',
                'static-delivery' => 'Static-first delivery meta-layer',
                'knowledge-ar'    => 'Arabic-language knowledge and capability meta-layer',
                'long-horizon'    => 'Long-horizon strategic frame',
            ],
        ],

        // What an expert team is, structurally. Used by the ontology pages and
        // the admin org view; not a content taxonomy.
        'expert_team' => [
            'type' => true,
            'label' => 'Expert team scope',
            'default' => 'service-level',
            'options' => [
                'service-level' => 'Service-level delivery unit (manager + ~10 experts)',
                'cluster'       => 'Cluster (optional coordination layer)',
                'cross-service' => 'Cross-service (not part of the model)',
            ],
            'description' => [
                'service-level' => 'The Expert Team is not a cross-service entity: it is the accountable producer of one service.',
                'cluster'       => 'Clusters are an optional coordination layer, recommended as operationally mandatory at full scale.',
                'cross-service' => 'Explicitly excluded: a team that spans services breaks the one-service-one-team rule.',
            ],
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 3. CAPACITY & RATIOS — the organisational arithmetic
    // ═══════════════════════════════════════════════════════════════════════
    'caps' => [
        'leader_managers'  => ['value' => 100, 'format' => 'int', 'label' => 'Managers per service leader'],
        'manager_experts'  => ['value' => 12, 'format' => 'int', 'label' => 'Experts per manager (hard cap)'],
        'manager_experts_soft' => ['value' => 10, 'format' => 'int', 'label' => 'Experts per manager (soft target)'],
        'service_experts'  => ['value' => 1000, 'format' => 'int', 'label' => 'Experts per service'],
        'cluster_leads'    => ['value' => 10, 'format' => 'int', 'label' => 'Cluster leads per leader'],
        'roles'            => ['value' => 8, 'format' => 'int', 'label' => 'Account roles'],
        'permissions'      => ['value' => 35, 'format' => 'int', 'label' => 'Permissions in the ACL'],
        'admin_menu_items' => ['value' => 24, 'format' => 'int', 'label' => 'Admin menu items'],
        'scorecard_weights' => ['value' => 4, 'format' => 'int', 'label' => 'Scorecard factors', 'note' => 'Output, quality, timeliness, blockers.'],

        // §4.2 — the Expert Team staffing convention. `managers_per_service` is
        // the outside-manager figure the dossier states (100 per service); the
        // README's own cap is `manager_experts` = 12 with a soft target of 10.
        'managers_per_service' => ['value' => 100, 'format' => 'int', 'label' => 'Managers per service (dossier convention)'],
        'experts_per_manager_dossier' => ['value' => 10, 'format' => 'int', 'label' => 'Experts per manager (dossier approximation)', 'note' => 'The dossier says "approximately ten"; the README hard cap is 12.'],
        'team_floor'       => ['value' => 1, 'format' => 'int', 'label' => 'Managers in a minimum viable team', 'note' => 'A service without an assigned team is a design, not a delivery.'],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 4. MONEY — the business model, stated as figures
    // ═══════════════════════════════════════════════════════════════════════
    'money' => [
        'license_rate'    => ['value' => 610.5, 'format' => 'money', 'unit' => 'TND/h', 'label' => 'Licence rate per hour of work'],
        'offer_deadline'  => ['value' => '2027-01-01', 'format' => 'date', 'label' => 'End of the promotional offer (exclusive)'],
        'salary_monthly'  => ['value' => 600, 'format' => 'money', 'unit' => 'TND', 'label' => 'Monthly salary of a certified post'],
        'part_daily'      => ['value' => 10, 'format' => 'money', 'unit' => 'TND', 'label' => 'Price of one part per day'],
        'parts_month'     => ['value' => 30, 'format' => 'int', 'label' => 'Days counted per monthly part'],
        'part_monthly'    => ['value' => 300, 'format' => 'money', 'unit' => 'TND', 'label' => 'One part for a 30-day month', 'note' => '{part_daily} × {parts_month}.'],
        'payroll_monthly' => ['value' => 78000000, 'format' => 'compact', 'unit' => 'TND', 'label' => 'Payroll at design ceiling', 'note' => '130,000 posts × 600 TND. A modelled figure, not a commitment.'],
        'parts_per_post'  => ['value' => 2, 'format' => 'int', 'label' => 'Parts needed per certified post', 'note' => 'The open business-model decision; stored as a variable so the Payroll view can state it.'],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 5. TIME — cadence of the weekly work engine
    // ═══════════════════════════════════════════════════════════════════════
    'time' => [
        'timezone'        => ['value' => 'Africa/Tunis', 'format' => 'text', 'label' => 'Server timezone'],
        'directives_due'  => ['value' => 'Mon 09:00', 'format' => 'text', 'label' => 'Weekly directives published'],
        'reports_due'     => ['value' => 'Fri 12:00', 'format' => 'text', 'label' => 'Expert reports due'],
        'approvals_due'   => ['value' => 'Fri 16:00', 'format' => 'text', 'label' => 'Manager approvals due'],
        'signoff_due'     => ['value' => 'Fri 18:00', 'format' => 'text', 'label' => 'Leader sign-off and lock'],
        'rollup_at'       => ['value' => 'Sat 02:00', 'format' => 'text', 'label' => 'Rollups finalised'],
        'registry_verified' => ['value' => '2026-10-10', 'format' => 'date', 'label' => 'Register last verified'],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 6. DOCTRINE — thresholds and bounds stated as variables
    // ═══════════════════════════════════════════════════════════════════════
    'doctrine' => [
        'rag_red_below'   => ['value' => 70, 'format' => 'percent', 'label' => 'Score below which a week is red'],
        'rag_amber_below' => ['value' => 90, 'format' => 'percent', 'label' => 'Score below which a week is amber'],

        // §4.7 — two different sampling figures coexist and must never be
        // presented as one rule. The dossier proposes 5%; the README's leader
        // schedule specifies 20 sampled reports per service, which is 2% of a
        // 1,000-expert team. Both are stored so a page can state the difference.
        'quality_sample'  => ['value' => 5, 'format' => 'percent', 'label' => 'Reports sampled for quality (dossier recommendation)', 'note' => 'A dossier recommendation, not a README rule.'],
        'sampling_readme_per_service' => ['value' => 20, 'format' => 'int', 'unit' => 'reports', 'label' => 'Sampled reports per service (README leader schedule)'],
        'sampling_readme_percent' => ['value' => 2, 'format' => 'percent', 'label' => 'Sampled share implied by the README rule', 'note' => '20 reports out of 1,000 experts per service.'],

        'blocker_sla_manager' => ['value' => 24, 'format' => 'int', 'unit' => 'h', 'label' => 'Manager response time on a blocker'],
        'blocker_sla_leader'  => ['value' => 48, 'format' => 'int', 'unit' => 'h', 'label' => 'Leader response time on a blocker'],
        'correction_sla'  => ['value' => 14, 'format' => 'int', 'unit' => 'days', 'label' => 'Target time to answer a filed correction'],
        'min_contrast'    => ['value' => 4.5, 'format' => 'decimal', 'unit' => ':1', 'label' => 'Minimum text contrast ratio'],
        'max_csp_origins' => ['value' => 0, 'format' => 'int', 'label' => 'Third-party origins allowed in the CSP'],

        // §4.7 — the qualifications that keep the ontology credible rather than
        // aspirational. Stored as booleans so a page can render the caveat from
        // one source instead of restating it in prose.
        'acyclic_only_time_unrolled' => ['value' => true, 'format' => 'bool', 'label' => 'Dependency graph is acyclic only when time-unrolled'],
        'dyon_enforcement_verified'  => ['value' => false, 'format' => 'bool', 'label' => 'Dyon certification enforcement independently verified', 'note' => 'Proposed, not verified. The README publishes no proof that activation is blocked without certification.'],
        'live_means_employment'      => ['value' => false, 'format' => 'bool', 'label' => '"Live" means 1,000 experts are employed', 'note' => '"Live" means operational as a programme or a site, not a financed hiring promise.'],
    ],

    // ═══════════════════════════════════════════════════════════════════════
    // 7. SITE IDENTITY — names, addresses, licence wording
    // ═══════════════════════════════════════════════════════════════════════
    'site' => [
        'name'        => ['value' => 'W5D', 'format' => 'text', 'label' => 'Short name'],
        'name_long'   => ['value' => 'World 5 Dimensions', 'format' => 'text', 'label' => 'Full name'],
        'tagline'     => ['value' => 'meta, macro and beyond technology', 'format' => 'text', 'label' => 'Tagline'],
        'founder'     => ['value' => 'Maher', 'format' => 'text', 'label' => 'Founder'],
        'address'     => ['value' => '8 rue Elkhawarezmi, Z.I. Saint Gobain, 2014 Mégrine, Tunisia', 'format' => 'text', 'label' => 'Registered address'],
        'domain'      => ['value' => 'w5d.tech', 'format' => 'text', 'label' => 'Launch domain'],
        'license_footer' => ['value' => 'W5D Admin Dashboard licensed at {money.license_rate} TND/hour of work. Promotional offer valid before {money.offer_deadline_year}.', 'format' => 'text', 'label' => 'Footer licence sentence'],
        'current_phase'  => ['value' => 'Preview', 'format' => 'text', 'label' => 'Current build phase'],
    ],

    // Every "to be confirmed" chip on the site counts from here. Keeping the
    // phrase in one place means the register, the legal notice and the contact
    // page cannot drift apart.
    'open_value' => ['value' => 'to be confirmed', 'format' => 'text', 'label' => 'Phrase shown for a figure that has not been supplied'],
];
