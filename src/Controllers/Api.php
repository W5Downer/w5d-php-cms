<?php
namespace W5D\Controllers;

use W5D\Config;
use W5D\Content;
use W5D\Country;
use W5D\DB;
use W5D\Router;
use W5D\Rollup;

/** JSON and XML endpoints. /api/kpis.json reads rollups_daily only (never weekly_reports). */
final class Api
{
    public static function kpis(): array
    {
        $iso = Country::defaultIso();
        $body = ['country' => $iso, 'source' => 'rollups_daily', 'generated_at' => gmdate('c'), 'metrics' => [], 'status' => 'ok'];
        try {
            $cid = \W5D\Scope::countryId($iso);
            if ($cid !== null) {
                $rows = DB::all('SELECT r.metric, r.value, r.day FROM rollups_daily r
                    JOIN (SELECT metric, MAX(day) AS d FROM rollups_daily
                          WHERE country_id = ? AND macro_slug = \'\' AND service_slug = \'\' GROUP BY metric) m
                      ON m.metric = r.metric AND m.d = r.day
                    WHERE r.country_id = ? AND r.macro_slug = \'\' AND r.service_slug = \'\'', [$cid, $cid]);
                foreach ($rows as $r) {
                    $body['metrics'][$r['metric']] = ['value' => (int) $r['value'], 'day' => $r['day']];
                }
            }
            $body['sample'] = ['percent' => \W5D\Sampling::percent(), 'fixed' => true];
        } catch (\Throwable) {
            $body['status'] = 'database_unavailable';
        }
        $body['declared'] = [
            'experts_current' => (int) \W5D\Variables::value('experts.current'),
            'design_ceiling' => (int) \W5D\Variables::value('experts.target'),
            'note' => 'The design ceiling is a capacity equation, not a headcount.',
        ];
        return self::json($body);
    }

    public static function sitemap(): array
    {
        $base = rtrim((string) Config::app('url', 'https://w5d.tech'), '/');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach (Router::publicPaths() as $p) {
            if ($p === '/404') {
                continue;
            }
            $xml .= '  <url><loc>' . htmlspecialchars($base . $p, ENT_XML1) . '</loc></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";
        return ['status' => 200, 'headers' => ['Content-Type' => 'application/xml; charset=utf-8', 'Cache-Control' => 'public, max-age=3600'], 'body' => $xml];
    }

    public static function robots(): array
    {
        $base = rtrim((string) Config::app('url', 'https://w5d.tech'), '/');
        $admin = Config::app('admin_path', 'panel-x9z');
        $txt = "User-agent: *\nDisallow: /{$admin}/\nDisallow: /app/\nDisallow: /api/\n\nSitemap: {$base}/sitemap.xml\n";
        return ['status' => 200, 'headers' => ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'public, max-age=3600'], 'body' => $txt];
    }

    public static function json(array $data, int $status = 200): array
    {
        return ['status' => $status, 'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=60'],
            'body' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)];
    }
}
