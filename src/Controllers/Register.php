<?php
namespace W5D\Controllers;

use W5D\App;
use W5D\Audit;
use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Env;
use W5D\Lang;
use W5D\Page;
use W5D\RateLimit;
use W5D\Scope;
use W5D\View;

/**
 * /app/register — the four-step interest flow (README C2), posted as one form:
 *   1. eligibility (degree, field fit, availability)
 *   2. account     (name, e-mail, governorate, consent)
 *   3. identity    (CIN only when REGISTRATION_COLLECT_CIN=1; otherwise a notice explains why)
 *   4. service     (one service; stored on the applicant row)
 *
 * Order of checks: site open -> CSRF -> honeypot -> rate limit (5 per IP per hour) -> validation
 * -> transactional write. Duplicate e-mail or CIN returns HTTP 409 and writes nothing.
 */
final class Register
{
    private const DEGREES = ['licence', 'maitrise', 'ingenieur', 'none'];
    private const YESNO = ['yes', 'no'];

    public static function handle(array $req): array
    {
        $ip = App::ip($req['server']);
        $isPost = $req['method'] === 'POST';
        $values = $isPost
            ? array_map(static fn ($v) => is_string($v) ? trim($v) : $v, $req['post'])
            : self::prefill($req['get']);

        if (!Config::app('registration_open', true)) {
            return self::page($values, [], 503, ['error', t('reg_closed')]);
        }
        if (!$isPost) {
            return self::page($values, [], 200, null);
        }
        if (!csrf_valid($req['post']['_csrf'] ?? null)) {
            return self::page($values, [], 403, ['error', t('form_csrf')]);
        }
        if (($values['website'] ?? '') !== '') {
            // Honeypot filled by a bot: answer as if successful, store nothing.
            return self::page([], [], 200, ['ok', t('reg_ok')]);
        }
        if (!RateLimit::hit('register:' . $ip, (int) Config::app('rate_limit_register_per_hour', 5))) {
            return self::page($values, [], 429, ['error', t('reg_rate')]);
        }
        $errors = self::validate($values);
        if ($errors !== []) {
            return self::page($values, $errors, 422, ['error', t('form_error')]);
        }
        try {
            $result = self::store($values, $ip);
        } catch (\RuntimeException $e) {
            App::log('register failed: ' . $e->getMessage());
            return self::page($values, [], 503, ['error', t('reg_db')]);
        }
        if ($result === 'duplicate') {
            return self::page($values, ['email' => t('reg_duplicate')], 409, ['error', t('reg_duplicate')]);
        }
        return self::page([], [], 200, ['ok', t('reg_ok')]);
    }

    /** @return array<string,string> field => message */
    public static function validate(array $v): array
    {
        $e = [];
        if (!in_array($v['degree'] ?? '', self::DEGREES, true)) {
            $e['degree'] = t('reg_bad_degree');
        }
        if (($v['fit'] ?? '') !== 'yes') {
            $e['fit'] = t('reg_not_eligible');
        }
        if (($v['available'] ?? '') !== 'yes') {
            $e['available'] = t('reg_not_eligible');
        }
        $name = (string) ($v['name'] ?? '');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
            $e['name'] = t('reg_bad_name');
        }
        $email = (string) ($v['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $e['email'] = t('reg_bad_email');
        }
        if (!in_array($v['region'] ?? '', array_keys(Country::regions(Country::defaultIso())), true)) {
            $e['region'] = t('reg_invalid_region');
        }
        if (!isset(Content::all('services')[(string) ($v['service'] ?? '')])) {
            $e['service'] = t('reg_invalid_service');
        }
        if (Config::app('collect_cin', false) && !preg_match('/^\d{8}$/', (string) ($v['cin'] ?? ''))) {
            $e['cin'] = t('reg_bad_cin');
        }
        if (($v['consent'] ?? '') !== 'yes') {
            $e['consent'] = t('reg_required');
        }
        return $e;
    }

    /**
     * Writes the applicant, consent rows and an audit row in one transaction.
     * Returns 'ok' or 'duplicate'. Throws RuntimeException when the write cannot complete.
     */
    public static function store(array $v, string $ip): string
    {
        $key = Env::get('APP_KEY');
        if ($key === '') {
            throw new \RuntimeException('APP_KEY is not set');
        }
        $iso = Country::defaultIso();
        $cid = Scope::countryId($iso);
        if ($cid === null) {
            throw new \RuntimeException('country not installed');
        }
        $regionId = DB::value('SELECT r.id FROM regions r JOIN countries c ON c.id = r.country_id WHERE c.iso = ? AND r.slug = ?', [$iso, $v['region']]);
        $macro = Content::get('services', (string) $v['service'])['macro'] ?? '';
        $emailHmac = hash_hmac('sha256', strtolower(trim((string) $v['email'])), $key);
        $cinHmac = Config::app('collect_cin', false) ? hash_hmac('sha256', (string) $v['cin'], $key) : null;

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            if ($cinHmac !== null && DB::value('SELECT id FROM applicants WHERE country_id = ? AND cin_hmac = ? FOR UPDATE', [$cid, $cinHmac])) {
                $pdo->rollBack();
                return 'duplicate';
            }
            if (DB::value('SELECT id FROM applicants WHERE country_id = ? AND email_hmac = ? FOR UPDATE', [$cid, $emailHmac])) {
                $pdo->rollBack();
                return 'duplicate';
            }
            $id = DB::insert('INSERT INTO applicants
                (country_id, region_id, macro_slug, service_slug, status, full_name, email, email_hmac,
                 degree_level, available_from, cin_hmac, lang, utm_source)
                VALUES (?, ?, ?, ?, \'registered\', ?, ?, ?, ?, NULL, ?, ?, NULL)', [
                $cid, $regionId ?: null, $macro, $v['service'], substr((string) $v['name'], 0, 120),
                substr((string) $v['email'], 0, 190), $emailHmac, $v['degree'], $cinHmac, Lang::current(),
            ]);
            foreach (['registration', 'contact'] as $purpose) {
                DB::run('INSERT INTO consents (country_id, applicant_id, purpose, notice_version, granted)
                    VALUES (?, ?, ?, \'registration-v1\', 1)', [$cid, $id, $purpose]);
            }
            Audit::write(null, $cid, 'applicant.registered', 'applicant', (string) $id, 'service=' . $v['service'], $ip);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // A concurrent duplicate that slipped past the SELECT is caught by the unique index (1062).
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')) {
                return 'duplicate';
            }
            throw new \RuntimeException('register write failed', 0, $e);
        }
        return 'ok';
    }

    private static function prefill(array $get): array
    {
        $out = [];
        $services = Content::all('services');
        if (isset($get['service']) && isset($services[(string) $get['service']])) {
            $out['service'] = (string) $get['service'];
        }
        if (isset($get['macro']) && ($m = Content::get('macros', (string) $get['macro']))) {
            $out['service'] ??= $m['services'][0]['slug'] ?? '';
        }
        if (isset($get['region'])) {
            $out['region'] = (string) $get['region'];
        }
        if (isset($get['degree']) && in_array($get['degree'], self::DEGREES, true)) {
            $out['degree'] = (string) $get['degree'];
        }
        foreach (['fit', 'available'] as $k) {
            if (isset($get[$k]) && in_array($get[$k], self::YESNO, true)) {
                $out[$k] = (string) $get[$k];
            }
        }
        return $out;
    }

    private static function page(array $values, array $errors, int $status, ?array $banner): array
    {
        $regionOpts = [];
        foreach (Country::regions(Country::defaultIso()) as $r) {
            $regionOpts[] = ['value' => $r['slug'], 'label' => $r['name']];
        }
        $serviceOpts = [];
        foreach (Content::all('services') as $slug => $s) {
            $serviceOpts[] = ['value' => $slug, 'label' => ['en' => $s['title']['en'] . ' (' . str_pad((string) $s['number'], 3, '0', STR_PAD_LEFT) . ')']];
        }
        $yesno = [['value' => 'yes', 'label' => t_all('yes')], ['value' => 'no', 'label' => t_all('no')]];
        $fields = [
            ['name' => 'degree', 'label' => t_all('reg_degree'), 'type' => 'select', 'required' => true, 'options' => [
                ['value' => 'licence', 'label' => t_all('elig_degree_licence')],
                ['value' => 'maitrise', 'label' => t_all('elig_degree_maitrise')],
                ['value' => 'ingenieur', 'label' => t_all('elig_degree_ingenieur')],
                ['value' => 'none', 'label' => t_all('elig_degree_none')],
            ]],
            ['name' => 'fit', 'label' => t_all('reg_fit'), 'type' => 'select', 'required' => true, 'options' => $yesno],
            ['name' => 'available', 'label' => t_all('reg_available'), 'type' => 'select', 'required' => true, 'options' => $yesno],
            ['name' => 'name', 'label' => t_all('reg_name'), 'required' => true, 'max' => 120],
            ['name' => 'email', 'label' => t_all('reg_email'), 'type' => 'email', 'required' => true],
            ['name' => 'region', 'label' => t_all('reg_region'), 'type' => 'select', 'required' => true,
                'help' => t_all('reg_region_help'), 'options' => $regionOpts],
            ['name' => 'service', 'label' => t_all('reg_service'), 'type' => 'select', 'required' => true, 'options' => $serviceOpts],
        ];
        if (Config::app('collect_cin', false)) {
            $fields[] = ['name' => 'cin', 'label' => t_all('reg_cin'), 'required' => true, 'max' => 8, 'help' => t_all('reg_cin_help')];
        }
        $fields[] = ['name' => 'consent', 'label' => t_all('reg_consent'), 'type' => 'checkbox', 'required' => true];
        $fields[] = ['name' => 'website', 'hidden' => true];

        $body = '<h1>' . e(t('reg_title')) . '</h1><p class="lead">' . e(t('reg_step_intro')) . '</p>';
        if (!Config::app('collect_cin', false)) {
            $body .= '<div class="notice" role="note"><p>' . e(t('reg_no_cin')) . '</p></div>';
        }
        $body .= '<div class="notice" role="note"><p>' . e(t('reg_stub_note')) . '</p></div>';
        if ($banner) {
            $body .= '<div class="' . ($banner[0] === 'ok' ? 'notice' : 'form-banner') . '" role="status">' . e($banner[1]) . '</div>';
        }
        if ($banner && $banner[0] === 'ok') {
            return Page::wrap(t('reg_title'), '', $body, '/app/register/', 200, true);
        }
        $form = [
            'type' => 'form', 'id' => 'register', 'tier' => 'E', 'focus' => 5,
            'title' => t_all('reg_title'),
            'data' => ['action' => '/app/register/', 'submit' => t_all('reg_submit'), 'fields' => $fields],
            'errors' => $errors,
            'values' => $values,
        ];
        $body .= View::block($form, ['id' => 'register', 'path' => '/app/register/']);
        return Page::wrap(t('reg_title'), '', $body, '/app/register/', $status, true);
    }
}
