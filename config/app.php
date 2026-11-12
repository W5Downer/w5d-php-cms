<?php
/**
 * Application settings. Secrets and environment-specific values live in .env
 * (denied by .htaccess). Nothing country-specific is written here: read it from
 * data/countries/{iso}.json through W5D\Country.
 */
return [
    'name' => 'W5D',
    'url' => W5D\Env::get('APP_URL', 'https://w5d.tech'),
    'domain' => W5D\Env::get('APP_DOMAIN', 'w5d.tech'),

    // Templates: blocks resolve views/templates/{name}/blocks/{type}.php -> views/_blocks/{type}.php
    // CSS: assets/_base/css/templates/{name}.css (additive only).
    'template' => W5D\Env::get('W5D_TEMPLATE', 'ledger'),
    'templates' => ['ledger', 'atlas', 'folio'],

    // Languages: cookie w5d_lang -> Accept-Language -> default. No ?lang= URLs.
    'languages' => ['en', 'fr', 'ar'],
    'default_language' => 'en',
    'rtl_languages' => ['ar'],

    // Owner portal mount point (obfuscated; set in .env).
    'admin_path' => trim(W5D\Env::get('ADMIN_URL_PATH', 'panel-x9z'), '/'),

    // Secret used for HMAC of CIN values and IP buckets. Must be set in .env.
    'app_key' => W5D\Env::get('APP_KEY', ''),
    'debug' => W5D\Env::bool('APP_DEBUG', false),

    // Interest registration. Open by default; it collects no CIN and no diploma.
    // CIN collection stays off until counsel clears the privacy gate (see docs/design.md).
    'registration_open' => W5D\Env::bool('REGISTRATION_OPEN', true),
    'collect_cin' => W5D\Env::bool('REGISTRATION_COLLECT_CIN', false),
    'rate_limit_register_per_hour' => (int) W5D\Env::get('RATE_LIMIT_REGISTER_PER_HOUR', '5'),
    'rate_limit_login_per_hour' => (int) W5D\Env::get('RATE_LIMIT_LOGIN_PER_HOUR', '10'),
    'rate_limit_contact_per_hour' => 5,

    // The default country is an ISO code. All country facts come from data/countries/{iso}.json.
    'default_country' => 'tn',

    // Stub adapters: no SMS or e-mail is sent. Replace with an approved processor.
    'sms_adapter' => 'stub',
    'mail_adapter' => 'stub',
];
