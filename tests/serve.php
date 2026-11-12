<?php
/**
 * Local development router for `php -S` (Apache applies .htaccess in production).
 * Serves existing files from assets/ and index.php for everything else; denies the sensitive folders.
 *   php -S 0.0.0.0:8080 tests/serve.php   (run from the repository root)
 */
$root = dirname(__DIR__);
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
if (preg_match('#^/(src|config|data|lang|db|bin|storage|docs|views|tests|\.git)(/|$)#', $path)
    || preg_match('#\.(env|sql|log|md|bak|ini|lock|orig|swp|dist)$#', $path)
    || str_contains($path, '/.')) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
$file = $root . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
