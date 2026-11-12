<?php
namespace W5D;

/**
 * Fixed-window rate limiter backed by the rate_limits table (atomic upsert).
 * Buckets are keyed by purpose and identity, e.g. "register:203.0.113.5".
 */
final class RateLimit
{
    private const WINDOW = 3600;

    /** Returns true when the hit is allowed. */
    public static function hit(string $bucket, int $limit): bool
    {
        if ($limit <= 0) {
            return false;
        }
        $key = substr(hash('sha256', $bucket), 0, 64);
        $now = time();
        $start = $now - ($now % self::WINDOW);
        DB::run('INSERT INTO rate_limits (bucket, window_start, hits) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE
              hits = IF(window_start = VALUES(window_start), hits + 1, 1),
              window_start = VALUES(window_start)', [$key, $start]);
        $hits = (int) DB::value('SELECT hits FROM rate_limits WHERE bucket = ?', [$key]);
        return $hits <= $limit;
    }
}
