<?php
namespace W5D;

/**
 * Aggregates raw rows into rollups_daily. Idempotent: rows are upserted on the unique key
 * (country, day, macro, service, metric), so a re-run or an overlapping cron run cannot
 * double-count. A named job lock (job_locks table, see Sql::acquireLock) stops two runs from
 * computing at the same time on either driver.
 */
final class Rollup
{
    public static function run(?string $day = null): array
    {
        $day ??= gmdate('Y-m-d');
        $lock = 'w5d_rollup';
        $owner = Sql::acquireLock($lock);
        if ($owner === null) {
            return ['status' => 'skipped', 'reason' => 'another rollup is running'];
        }
        try {
            $written = 0;
            foreach (DB::all('SELECT id FROM countries') as $c) {
                $cid = (int) $c['id'];
                $written += self::upsert($cid, $day, '', '', 'applicants_registered',
                    (int) DB::value('SELECT COUNT(*) FROM applicants WHERE country_id = ? AND ' . Sql::dateOf('created_at') . ' = ?', [$cid, $day]));
                $written += self::upsert($cid, $day, '', '', 'applicants_total',
                    (int) DB::value('SELECT COUNT(*) FROM applicants WHERE country_id = ?', [$cid]));
                $written += self::upsert($cid, $day, '', '', 'applicants_certified',
                    (int) DB::value("SELECT COUNT(*) FROM applicants WHERE country_id = ? AND status = 'certified'", [$cid]));
                $written += self::upsert($cid, $day, '', '', 'weekly_reports_submitted',
                    (int) DB::value("SELECT COUNT(*) FROM weekly_reports WHERE country_id = ? AND status = 'submitted'", [$cid]));
                $written += self::upsert($cid, $day, '', '', 'sampled_reports',
                    (int) DB::value('SELECT COUNT(*) FROM weekly_reports WHERE country_id = ? AND sampled = 1', [$cid]));
                $written += self::upsert($cid, $day, '', '', 'sampled_pass',
                    (int) DB::value("SELECT COUNT(*) FROM weekly_reports WHERE country_id = ? AND sample_result = 'sampled_pass'", [$cid]));
                $written += self::upsert($cid, $day, '', '', 'sampled_fail',
                    (int) DB::value("SELECT COUNT(*) FROM weekly_reports WHERE country_id = ? AND sample_result = 'sampled_fail'", [$cid]));
                foreach (DB::all('SELECT macro_slug, COUNT(*) AS n FROM applicants WHERE country_id = ? GROUP BY macro_slug', [$cid]) as $row) {
                    $written += self::upsert($cid, $day, (string) $row['macro_slug'], '', 'applicants_total', (int) $row['n']);
                }
            }
            return ['status' => 'ok', 'day' => $day, 'rows_written' => $written];
        } finally {
            Sql::releaseLock($lock, $owner);
        }
    }

    /** Pass rate of the fixed sample, from rollups only. Null when no sampled reports exist. */
    public static function samplePassRate(int $countryId): ?array
    {
        $row = DB::one("SELECT
              (SELECT value FROM rollups_daily WHERE country_id = ? AND metric = 'sampled_reports' AND macro_slug = '' AND service_slug = '' ORDER BY day DESC LIMIT 1) AS sampled,
              (SELECT value FROM rollups_daily WHERE country_id = ? AND metric = 'sampled_pass' AND macro_slug = '' AND service_slug = '' ORDER BY day DESC LIMIT 1) AS passed,
              (SELECT value FROM rollups_daily WHERE country_id = ? AND metric = 'sampled_fail' AND macro_slug = '' AND service_slug = '' ORDER BY day DESC LIMIT 1) AS failed", [$countryId, $countryId, $countryId]);
        $sampled = (int) ($row['sampled'] ?? 0);
        if ($sampled === 0) {
            return null;
        }
        return ['sampled' => $sampled, 'passed' => (int) ($row['passed'] ?? 0), 'failed' => (int) ($row['failed'] ?? 0)];
    }

    private static function upsert(int $country, string $day, string $macro, string $service, string $metric, int $value): int
    {
        DB::run(Sql::upsert('rollups_daily',
            ['country_id', 'day', 'macro_slug', 'service_slug', 'metric', 'value'],
            ['country_id', 'day', 'macro_slug', 'service_slug', 'metric'],
            ['value', 'computed_at']),
            [$country, $day, $macro, $service, $metric, $value]);
        return 1;
    }
}
