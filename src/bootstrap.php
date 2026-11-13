<?php
/**
 * Bootstrap shared by the front controller, bin/ scripts and tests.
 * Load order matters: Env before config, autoloader before any W5D class.
 */
declare(strict_types=1);

if (!defined('W5D_ROOT')) {
    define('W5D_ROOT', dirname(__DIR__));
}
if (!defined('W5D_ASSET_VERSION')) {
    define('W5D_ASSET_VERSION', '2026-11-12a');
}

require_once W5D_ROOT . '/src/Env.php';
W5D\Env::load(W5D_ROOT . '/.env');

spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'W5D\\', 4) !== 0) {
        return;
    }
    $file = W5D_ROOT . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

require_once W5D_ROOT . '/src/helpers.php';
