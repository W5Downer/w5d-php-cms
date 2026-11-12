<?php
namespace W5D\Controllers;

use W5D\ACL;
use W5D\App;
use W5D\Audit;
use W5D\Auth;
use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Lang;
use W5D\Page;
use W5D\RateLimit;
use W5D\Rollup;
use W5D\Sampling;
use W5D\Scope;
use W5D\Variables;

/**
 * Owner portal at /{ADMIN_URL_PATH}/…
 * Every menu: requires a session (login), checks ACL for the menu, is scoped by country,
 * and lists at most 50 rows per page. Menus that need a data source not yet connected say so.
 */
final class Portal
{
    private const PER_PAGE = 50;

    public static function handle(array $req, string $path): array
    {
        $base = '/' . trim((string) Config::app('admin_path', 'panel-x9z'), '/');
        $path = '/' . trim($path, '/');
        if ($path === '/login') {
            return self::login($req);
        }
        if ($path === '/logout' && $req['method'] === 'POST') {
            if (!csrf_valid($req['post']['_csrf'] ?? null)) {
                return self::wrap(t('portal_title'), self::card(t('form_csrf')), $base, 403);
            }
            Auth::logout();
            return self::redirect($base . '/login');
        }

        $user = Auth::user();
        if ($user === null) {
            return self::redirect($base . '/login');
        }
        $menu = $path === '/' ? 'command' : trim($path, '/');
        $menu = str_replace('/', '-', $menu);
        if (!in_array($menu, ['command', 'countries', 'regions', 'recruitment', 'applicants', 'identity', 'macro-services',
            'academies', 'teams', 'weekly-work', 'projects', 'clients', 'quality', 'payroll', 'investors', 'communications',
            'support', 'website', 'variables', 'users', 'reports', 'compliance', 'audit', 'system'], true)) {
            return self::wrap(t('err404_title'), self::card(t('err404_body')), $base, 404);
        }
        if (!ACL::can($user['role_code'], 'menu.' . $menu)) {
            return self::wrap(t('err_title'), self::card(t('portal_forbidden')), $base, 403);
        }

        $country = self::selectedCountry($req, $user);
        $sidebar = self::sidebar($user, $menu, $base);
        $body = match ($menu) {
            'command' => self::command($country, $user),
            'quality' => self::quality($country, $user),
            'variables' => self::variables(),
            'applicants' => self::applicants($req, $country, $user),
            'macro-services' => self::services($country, $user),
            'audit' => self::audit($req, $country, $user),
            'countries' => self::countries($user),
            default => self::notLive(),
        };
        $title = t('menu_' . $menu);
        return self::wrap($title, '<div class="portal">' . $sidebar . '<div class="portal-main">'
            . '<div class="portal-head"><h1>' . e($title) . '</h1>' . self::countrySwitch($user, $country, $base . '/' . $menu) . '</div>'
            . $body . '</div></div>', $base, 200, true);
    }

    private static function login(array $req): array
    {
        $base = '/' . trim((string) Config::app('admin_path', 'panel-x9z'), '/');
        $error = null;
        if ($req['method'] === 'POST') {
            if (!csrf_valid($req['post']['_csrf'] ?? null)) {
                $error = t('form_csrf');
            } else {
                $ip = App::ip($req['server']);
                $result = Auth::attempt(substr((string) ($req['post']['username'] ?? ''), 0, 64),
                    (string) ($req['post']['password'] ?? ''), $ip);
                if ($result === true) {
                    return self::redirect($base . '/');
                }
                $error = $result === 'rate_limited' ? t('portal_login_rate') : t('portal_bad');
            }
        }
        if (!DB::available()) {
            $error = $error ?? t('portal_no_db');
        }
        $body = '<div class="login"><h1>' . e(t('portal_login')) . '</h1><p>' . e(t('portal_login_help')) . '</p>'
            . ($error ? '<div class="form-banner" role="alert">' . e($error) . '</div>' : '')
            . '<form method="post" action="' . e($base) . '/login">' . csrf_field()
            . '<div class="field"><label for="u">' . e(t('portal_user')) . '</label><input id="u" name="username" autocomplete="username" required maxlength="64"></div>'
            . '<div class="field"><label for="p">' . e(t('portal_pass')) . '</label><input id="p" type="password" name="password" autocomplete="current-password" required></div>'
            . '<p class="blk-actions"><button class="btn btn-peak" type="submit">' . e(t('portal_login')) . '</button></p></form></div>';
        return self::wrap(t('portal_login'), $body, $base, $error ? 401 : 200, true);
    }

    private static function command(?array $country, array $user): string
    {
        if ($country === null) {
            return self::card(t('portal_pick_country'));
        }
        $rows = DB::all("SELECT metric, value, day FROM rollups_daily r
            WHERE country_id = ? AND macro_slug = '' AND service_slug = '' AND day = (
              SELECT MAX(day) FROM rollups_daily x WHERE x.country_id = r.country_id AND x.metric = r.metric
                AND x.macro_slug = '' AND x.service_slug = '') ORDER BY metric LIMIT 50", [$country['id']]);
        if ($rows === []) {
            return self::card(t('portal_rollup_empty'));
        }
        $html = '<dl class="kpi-row">';
        foreach ($rows as $r) {
            $html .= '<div class="kpi"><dt>' . e($r['metric']) . '</dt><dd class="num">' . number_format((int) $r['value']) . '</dd>'
                . '<dd class="help">' . e(sprintf(t('portal_rollup_day'), $r['day'])) . '</dd></div>';
        }
        return $html . '</dl>';
    }

    private static function quality(?array $country, array $user): string
    {
        $rule = '<div class="notice"><p>' . e(t('portal_quality_rule')) . '</p></div>';
        $rate = $country ? Rollup::samplePassRate((int) $country['id']) : null;
        if ($rate === null) {
            return $rule . self::card(t('portal_sample_none'));
        }
        $pass = $rate['sampled'] > 0 ? round($rate['passed'] / $rate['sampled'] * 100, 1) : 0;
        return $rule . '<dl class="kpi-row">'
            . '<div class="kpi"><dt>' . e(t('portal_sampled')) . '</dt><dd class="num">' . number_format($rate['sampled']) . '</dd></div>'
            . '<div class="kpi"><dt>' . e(t('portal_pass_rate')) . '</dt><dd class="num">' . $pass . '%</dd></div>'
            . '<div class="kpi"><dt>' . e(t('portal_sample_rate')) . '</dt><dd class="num">' . Sampling::percent() . '%</dd></div>'
            . '</dl>';
    }

    private static function variables(): string
    {
        $rows = '';
        foreach (Variables::declarations() as $path => $def) {
            $kind = isset($def['derive']) ? t('portal_derived') : (!empty($def['fixed']) ? t('portal_fixed') : t('portal_declared'));
            $rows .= '<tr><td>' . e($path) . '</td><td>' . e(Variables::format($path)) . '</td><td>' . e($kind) . '</td></tr>';
        }
        return '<p>' . e(t('portal_read_only')) . '</p><div class="table-scroll"><table class="data"><thead><tr><th>'
            . e('Variable') . '</th><th>Value</th><th>' . e('Kind') . '</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
    }

    private static function applicants(array $req, ?array $country, array $user): string
    {
        if ($country === null) {
            return self::card(t('portal_pick_country'));
        }
        if (!Scope::canActOn($user, $country['id'])) {
            return self::card(t('portal_forbidden'));
        }
        $status = (string) ($req['get']['status'] ?? '');
        $statuses = Config::vocab('status');
        if ($status !== '' && !in_array($status, $statuses, true)) {
            $status = '';
        }
        $page = max(1, (int) ($req['get']['page'] ?? 1));
        $where = 'country_id = ?';
        $args = [$country['id']];
        if ($status !== '') {
            $where .= ' AND status = ?';
            $args[] = $status;
        }
        $total = (int) DB::value("SELECT COUNT(*) FROM applicants WHERE {$where}", $args);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $pages);
        $rows = DB::all("SELECT id, status, macro_slug, service_slug, degree_level, created_at FROM applicants
            WHERE {$where} ORDER BY id DESC LIMIT " . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE), $args);
        $html = '<form method="get" class="country-switch"><label>' . e(t('portal_filter_status')) . ' <select name="status">'
            . '<option value="">' . e(t('portal_any')) . '</option>';
        foreach ($statuses as $s) {
            $html .= '<option value="' . e($s) . '"' . ($s === $status ? ' selected' : '') . '>' . e($s) . '</option>';
        }
        $html .= '</select></label><button class="btn btn-ghost" type="submit">' . e(t('portal_apply')) . '</button></form>';
        $html .= '<p class="bar-note">' . e(sprintf(t('portal_rows'), count($rows))) . ' · ' . number_format($total) . '</p>';
        $html .= '<div class="table-scroll"><table class="data"><thead><tr><th>ID</th><th>Status</th><th>Macro</th><th>Service</th><th>Degree</th><th>Created</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . (int) $r['id'] . '</td><td>' . e($r['status']) . '</td><td>' . e($r['macro_slug']) . '</td><td>'
                . e($r['service_slug']) . '</td><td>' . e($r['degree_level']) . '</td><td>' . e($r['created_at']) . '</td></tr>';
        }
        $html .= '</tbody></table></div>' . self::pager($page, $pages, $req, 'applicants');
        return $html;
    }

    private static function services(?array $country, array $user): string
    {
        if ($country === null) {
            return self::card(t('portal_pick_country'));
        }
        $active = [];
        foreach (DB::all('SELECT service_slug, active, seats FROM services_country WHERE country_id = ?', [$country['id']]) as $r) {
            $active[$r['service_slug']] = $r;
        }
        $html = '<div class="table-scroll"><table class="data"><thead><tr><th>No.</th><th>Service</th><th>Macro</th><th>Active</th><th>Seats</th></tr></thead><tbody>';
        $n = 0;
        foreach (Content::all('services') as $slug => $s) {
            if ($n++ >= self::PER_PAGE) {
                break;
            }
            $a = $active[$slug] ?? ['active' => 0, 'seats' => 0];
            $html .= '<tr><td>' . str_pad((string) $s['number'], 3, '0', STR_PAD_LEFT) . '</td><td>' . e($s['title']['en']) . '</td><td>'
                . e($s['macro']) . '</td><td>' . ((int) $a['active'] ? 'yes' : 'no') . '</td><td>' . (int) $a['seats'] . '</td></tr>';
        }
        return $html . '</tbody></table></div><p class="bar-note">' . e(sprintf(t('portal_rows'), min(self::PER_PAGE, count(Content::all('services'))))) . '</p>';
    }

    private static function audit(array $req, ?array $country, array $user): string
    {
        if (!ACL::can($user['role_code'], 'menu.audit')) {
            return self::card(t('portal_forbidden'));
        }
        $where = '1 = 1';
        $args = [];
        if ($country !== null) {
            $where = 'country_id = ?';
            $args[] = $country['id'];
        } elseif (!ACL::allCountries($user['role_code'])) {
            return self::card(t('portal_no_country'));
        }
        $rows = DB::all("SELECT id, actor_id, action, subject, subject_id, created_at FROM audit_log WHERE {$where}
            ORDER BY id DESC LIMIT " . self::PER_PAGE, $args);
        $html = '<p class="bar-note">' . e(sprintf(t('portal_rows'), count($rows))) . '</p>'
            . '<div class="table-scroll"><table class="data"><thead><tr><th>ID</th><th>Actor</th><th>Action</th><th>Subject</th><th>When (UTC)</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . (int) $r['id'] . '</td><td>' . e((string) $r['actor_id']) . '</td><td>' . e($r['action']) . '</td><td>'
                . e(($r['subject'] ?? '') . ' ' . ($r['subject_id'] ?? '')) . '</td><td>' . e($r['created_at']) . '</td></tr>';
        }
        return $html . '</tbody></table></div>';
    }

    private static function countries(array $user): string
    {
        if (!ACL::allCountries($user['role_code'])) {
            return self::card(t('portal_forbidden'));
        }
        $rows = '';
        foreach (Country::codes() as $iso) {
            $c = Country::get($iso);
            $rows .= '<tr><td>' . e(strtoupper($iso)) . '</td><td>' . e(Country::name($iso, 'en')) . '</td><td>' . e($c['currency'] ?? '') . '</td><td>' . e($c['timezone'] ?? '') . '</td></tr>';
        }
        return '<div class="table-scroll"><table class="data"><thead><tr><th>ISO</th><th>Name</th><th>Currency</th><th>Timezone</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
    }

    private static function notLive(): string
    {
        return self::card(t('portal_not_live'));
    }

    /** Country selected via ?country=iso, limited to the user's scope. Null = all countries or none. */
    private static function selectedCountry(array $req, array $user): ?array
    {
        $iso = strtolower((string) ($req['get']['country'] ?? ''));
        $allowed = ACL::allCountries($user['role_code']) ? null : (string) ($user['country_iso'] ?? '');
        if ($iso === '' || !preg_match('/^[a-z]{2}$/', $iso)) {
            $iso = $allowed ?: Country::defaultIso();
        }
        if ($allowed !== null && $iso !== $allowed) {
            return null;
        }
        if (!in_array($iso, Country::codes(), true)) {
            return null;
        }
        $id = Scope::countryId($iso);
        return $id === null ? null : ['id' => $id, 'iso' => $iso];
    }

    private static function countrySwitch(array $user, ?array $country, string $action): string
    {
        $items = [];
        if (ACL::allCountries($user['role_code'])) {
            foreach (Country::codes() as $iso) {
                $items[] = '<option value="' . e($iso) . '"' . ($country && $country['iso'] === $iso ? ' selected' : '') . '>' . e(Country::name($iso)) . '</option>';
            }
        } elseif (!empty($user['country_iso'])) {
            $items[] = '<option selected value="' . e($user['country_iso']) . '">' . e(Country::name($user['country_iso'])) . '</option>';
        }
        if ($items === []) {
            return '';
        }
        return '<form class="country-switch" method="get" action="' . e($action) . '"><label for="cs">' . e(t('portal_country')) . '</label>'
            . '<select id="cs" name="country">' . implode('', $items) . '</select><button class="btn btn-ghost" type="submit">' . e(t('portal_apply')) . '</button></form>';
    }

    private static function sidebar(array $user, string $current, string $base): string
    {
        $html = '<nav class="portal-nav" aria-label="' . e(t('portal_title')) . '"><h2>' . e(t('portal_title')) . '</h2><ul class="portal-menu">';
        foreach (ACL::menusFor($user['role_code']) as $m) {
            $href = $base . '/' . ($m === 'command' ? '' : $m);
            $html .= '<li><a href="' . e($href) . '"' . ($m === $current ? ' aria-current="page"' : '') . '>' . e(t('menu_' . $m)) . '</a></li>';
        }
        return $html . '</ul><form method="post" action="' . e($base) . '/logout" class="portal-logout">' . csrf_field()
            . '<button type="submit">' . e(t('portal_logout')) . '</button></form></nav>';
    }

    private static function pager(int $page, int $pages, array $req, string $menu): string
    {
        $base = '/' . trim((string) Config::app('admin_path', 'panel-x9z'), '/') . '/' . $menu;
        $qs = $req['get'];
        $out = '<nav class="pager" aria-label="pages">';
        if ($page > 1) {
            $out .= '<a href="' . e($base . '?' . http_build_query(array_merge($qs, ['page' => $page - 1]))) . '">' . e(t('portal_prev')) . '</a>';
        }
        $out .= '<span>' . e(sprintf(t('portal_page'), $page, $pages)) . '</span>';
        if ($page < $pages) {
            $out .= '<a href="' . e($base . '?' . http_build_query(array_merge($qs, ['page' => $page + 1]))) . '">' . e(t('portal_next')) . '</a>';
        }
        return $out . '</nav>';
    }

    private static function card(string $text): string
    {
        return '<div class="notice"><p>' . e($text) . '</p></div>';
    }

    private static function wrap(string $title, string $body, string $base, int $status, bool $private = true): array
    {
        return Page::wrap($title, '', $body, $base . '/', $status, true, $private);
    }

    private static function redirect(string $to): array
    {
        return ['status' => 303, 'headers' => ['Location' => $to, 'Cache-Control' => 'no-store'], 'body' => ''];
    }
}
