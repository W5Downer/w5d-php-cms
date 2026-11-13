<?php
namespace W5D;

use Throwable;

/**
 * The only place where SQL differs between SQLite (default) and MySQL/MariaDB.
 * Callers ask for an operation; this class returns the SQL for the active driver.
 * Keep every MySQL-only token (ON DUPLICATE KEY, INSERT IGNORE, GET_LOCK, FOR UPDATE,
 * UTC_TIMESTAMP, DATE_ADD) inside this file. lint.php enforces that.
 */
final class Sql
{
    /**
     * Insert-or-update on a unique key. $cols lists the insert columns in order; the caller
     * binds one value per column. $keys is the unique key (conflict target). $update lists the
     * columns to overwrite from the new row. An empty $update means "keep the existing row".
     */
    public static function upsert(string $table, array $cols, array $keys, array $update): string
    {
        $head = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(', ', $cols), self::placeholders(count($cols)));
        if (DB::isSqlite()) {
            if ($update === []) {
                return $head . sprintf(' ON CONFLICT (%s) DO NOTHING', implode(', ', $keys));
            }
            $set = implode(', ', array_map(static fn (string $c): string => "{$c} = excluded.{$c}", $update));
            return $head . sprintf(' ON CONFLICT (%s) DO UPDATE SET %s', implode(', ', $keys), $set);
        }
        if ($update === []) {
            return $head . sprintf(' ON DUPLICATE KEY UPDATE %s = %s', $keys[0], $keys[0]);
        }
        $set = implode(', ', array_map(static fn (string $c): string => "{$c} = VALUES({$c})", $update));
        return $head . ' ON DUPLICATE KEY UPDATE ' . $set;
    }

    /**
     * Fixed-window counter: insert a bucket with hits = 1, or on conflict reset it when the window
     * moved on, otherwise add one hit. Bind (bucket, window_start). The rate_limits table is keyed by bucket.
     */
    public static function counterUpsert(): string
    {
        if (DB::isSqlite()) {
            return 'INSERT INTO rate_limits (bucket, window_start, hits) VALUES (?, ?, 1)
                ON CONFLICT (bucket) DO UPDATE SET
                  hits = CASE WHEN rate_limits.window_start = excluded.window_start THEN rate_limits.hits + 1 ELSE 1 END,
                  window_start = excluded.window_start';
        }
        return 'INSERT INTO rate_limits (bucket, window_start, hits) VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE
              hits = IF(window_start = VALUES(window_start), hits + 1, 1),
              window_start = VALUES(window_start)';
    }

    /** Insert that silently skips a row that violates a unique key. */
    public static function insertIgnore(string $table, array $cols): string
    {
        $prefix = DB::isSqlite() ? 'INSERT OR IGNORE' : 'INSERT IGNORE';
        return sprintf('%s INTO %s (%s) VALUES (%s)', $prefix, $table, implode(', ', $cols), self::placeholders(count($cols)));
    }

    /**
     * Suffix that locks the selected rows until commit. SQLite has no row locks; registration
     * runs inside BEGIN IMMEDIATE, which already serialises writers, so the suffix is empty there.
     */
    public static function forUpdate(): string
    {
        return DB::isSqlite() ? '' : ' FOR UPDATE';
    }

    /** SQL expression for "now (UTC) plus N days", as a datetime string. */
    public static function daysFromNow(int $days): string
    {
        $days = max(0, $days);
        if (DB::isSqlite()) {
            return "datetime('now', '+{$days} days')";
        }
        return "DATE_ADD(UTC_TIMESTAMP(), INTERVAL {$days} DAY)";
    }

    /** SQL expression for the calendar date (YYYY-MM-DD) of a stored UTC datetime column. */
    public static function dateOf(string $column): string
    {
        return DB::isSqlite() ? "date({$column})" : "DATE({$column})";
    }

    /** True when the exception is a unique-key violation on either driver. */
    public static function isDuplicate(Throwable $e): bool
    {
        for ($cur = $e; $cur !== null; $cur = $cur->getPrevious()) {
            $msg = $cur->getMessage();
            if (str_contains($msg, 'Duplicate entry') || str_contains($msg, '1062')
                || str_contains($msg, 'UNIQUE constraint failed')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Named, expiring job lock stored in job_locks (works on both drivers).
     * Returns an owner token when the lock was taken, null when another run holds it.
     * An expired lock (crashed run) is reclaimed after $ttl seconds.
     */
    public static function acquireLock(string $name, int $ttl = 900): ?string
    {
        $now = time();
        DB::run('DELETE FROM job_locks WHERE name = ? AND expires_at < ?', [$name, $now]);
        $owner = bin2hex(random_bytes(8));
        $taken = DB::run(self::insertIgnore('job_locks', ['name', 'owner', 'expires_at']), [$name, $owner, $now + $ttl]);
        return $taken === 1 ? $owner : null;
    }

    public static function releaseLock(string $name, string $owner): void
    {
        DB::run('DELETE FROM job_locks WHERE name = ? AND owner = ?', [$name, $owner]);
    }

    private static function placeholders(int $n): string
    {
        return implode(', ', array_fill(0, $n, '?'));
    }
}
