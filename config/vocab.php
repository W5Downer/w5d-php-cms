<?php
/**
 * config/vocab.php — the only place vocabularies are defined (A2).
 * Each vocabulary is defined exactly once. bin/lint.php verifies:
 *   - 'block' equals the files in views/_blocks/*.php,
 *   - 'role' equals 'audience' (one identity list, documented in docs/templates.md),
 *   - 'meta_solution' equals data/content/layers/meta-solutions.json.
 */
return [
    // Who reads content and who logs in. Eight identities; 'manager' was missing from the earlier seven-name list.
    'audience' => ['owner', 'admin', 'manager', 'leader', 'member', 'investor', 'customer', 'user'],
    'role' => ['owner', 'admin', 'manager', 'leader', 'member', 'investor', 'customer', 'user'],

    // Applicant pipeline stages (applicants.status).
    'status' => ['registered', 'verified', 'screened', 'assigned', 'l1', 'l2', 'l3', 'l4', 'certified'],

    // Evidence level of a claim (E0 = written description only).
    'evidence' => ['E0', 'E1', 'E2', 'E3'],

    // Public block types. Must equal the files in views/_blocks/ (lint).
    'block' => [
        'hero', 'prose', 'cards', 'kpis', 'bars', 'cta', 'definitions', 'notice', 'steps', 'faq',
        'table', 'service-catalog', 'eligibility', 'gov-map', 'pipeline', 'sticky-cta', 'progress',
        'country-switch', 'stack', 'carousel', 'quote', 'form',
    ],

    'ontology_layer' => ['L0', 'L1', 'L2', 'L3', 'L4', 'L5'],

    // Reading tiers and focus levels (README design system).
    'tier' => ['E', 'I', 'X'],
    'focus' => [1, 2, 3, 4, 5],

    // Macro slugs in display order.
    'macro' => ['cultures', 'built', 'sciences', 'governance', 'technology'],
];
