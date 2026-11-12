<?php
/**
 * Aggregates raw rows into rollups_daily. Run hourly, and every 15 minutes on Fridays.
 *   0 * * * *        php bin/rollup.php          (hourly)
 *   every 15 min, Fridays: php bin/rollup.php   (see crontab in docs/deployment.md)
 * Idempotent and lock-protected, so overlapping runs do not double-count.
 */
declare(strict_types=1);
define('W5D_ROOT', dirname(__DIR__));
require W5D_ROOT . '/src/bootstrap.php';

$day = $argv[1] ?? null;
$result = W5D\Rollup::run($day);
echo json_encode($result) . PHP_EOL;
exit($result['status'] === 'ok' || $result['status'] === 'skipped' ? 0 : 1);
