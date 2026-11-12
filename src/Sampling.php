<?php
namespace W5D;

/**
 * The fixed 5% independent sample of submitted weekly reports (README "Quality & sampling").
 *
 * Selection is deterministic and reproducible: a report is sampled when
 *   HMAC-SHA256(seed, "country|week|report") mod 10000  <  rate × 10000
 * where seed is a server-held secret (W5D_SAMPLE_SEED) and rate is 5%. The rate is a
 * constant (config/variables.php: doctrine.quality_sample, fixed = true); there is no setter.
 */
final class Sampling
{
    public const RATE_PERCENT = 5;

    public static function selects(int $countryId, int $weekId, int $reportId): bool
    {
        $seed = Env::get('W5D_SAMPLE_SEED', Config::app('app_key', ''));
        if ($seed === '') {
            throw new \RuntimeException('W5D_SAMPLE_SEED (or APP_KEY) must be set to draw the sample');
        }
        $digest = hash_hmac('sha256', "{$countryId}|{$weekId}|{$reportId}", $seed);
        $bucket = hexdec(substr($digest, 0, 8)) % 10000;
        return $bucket < self::RATE_PERCENT * 100;
    }

    public static function percent(): int
    {
        return self::RATE_PERCENT;
    }
}
