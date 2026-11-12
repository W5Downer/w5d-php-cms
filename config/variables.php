<?php
/**
 * config/variables.php — the single registry of numbers and facts (rule 1: one fact, one place).
 *
 *   'value'  raw value, declared once here
 *   'derive' expression over other variables (+ - * /, parentheses), evaluated at render
 *   'fmt'    int | money2 | pct | plain   (display format; int uses thousands separators)
 *   'fixed'  true = not editable from the owner UI
 *
 * counts.* are not declared here: they are computed from data/content/ by W5D\Content::count().
 * Owner-local overrides go in config/variables.local.php (git-ignored).
 * Tokens: {{ path.to.value }} in any content string. Unresolved tokens fail bin/lint.php.
 */
return [
    'experts' => [
        // Design ceiling: a capacity equation, not a headcount (see /trust/statement/).
        'target' => ['value' => 130000, 'fmt' => 'int'],
        'per_service' => ['value' => 1000, 'fmt' => 'int'],
        // Must equal experts.target. bin/lint.php fails if they diverge.
        'design_check' => ['derive' => 'counts.macros * counts.services_per_macro * experts.per_service', 'fmt' => 'int'],
        // Verified, certified experts recorded on the platform today. Declared zero; not a KPI until audited.
        'current' => ['value' => 0, 'fmt' => 'int'],
    ],
    'leaders' => [
        // One service leader per service.
        'target' => ['derive' => 'counts.services', 'fmt' => 'int'],
    ],
    'managers' => [
        'per_service' => ['value' => 100, 'fmt' => 'int'],
        'target' => ['derive' => 'leaders.target * managers.per_service', 'fmt' => 'int'],
    ],
    'caps' => [
        'experts_per_manager_hard' => ['value' => 12, 'fmt' => 'int'],
        'manager_experts_soft' => ['value' => 10, 'fmt' => 'int'],
    ],
    'accounts' => [
        // Accounts if every role is a distinct person (report §3.1: 143,131).
        'total' => ['derive' => 'experts.target + managers.target + leaders.target + 1', 'fmt' => 'int'],
    ],
    'reports' => [
        'per_year' => ['derive' => 'experts.target * 52', 'fmt' => 'int'],
    ],
    'money' => [
        // README planning figure: 600 (local currency) per certified post per month. Not a wage recommendation.
        'salary_monthly' => ['value' => 600, 'fmt' => 'int'],
        'payroll_monthly' => ['derive' => 'experts.target * money.salary_monthly', 'fmt' => 'int'],
        // Report §5.6 illustrative fully loaded delivery cost per paid-active expert-month.
        'illustrative_delivery_cost' => ['value' => 3000, 'fmt' => 'int'],
    ],
    'doctrine' => [
        // Fixed independent sample of submitted weekly reports. Not editable from the UI.
        'quality_sample' => ['value' => 5, 'fmt' => 'pct', 'fixed' => true],
        'sample_min_reports' => ['value' => 1, 'fmt' => 'int', 'fixed' => true],
    ],
    'license' => [
        'rate_per_hour' => ['value' => 610.5, 'fmt' => 'money2'],
        'promo_year' => ['value' => 2027, 'fmt' => 'plain'],
        'promo_end' => ['value' => '2026-12-31', 'fmt' => 'plain'],
    ],
    'footprint' => [
        // Declared in the README; not independently audited. Shown as a declaration, not a KPI.
        'developers_tn' => ['value' => 26, 'fmt' => 'int'],
    ],
    'time' => [
        'registry_verified' => ['value' => '2026-11-12', 'fmt' => 'plain'],
    ],
];
