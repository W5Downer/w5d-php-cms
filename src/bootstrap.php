<?php
declare(strict_types=1);

/**
 * W5D Site Engine — bootstrap.
 *
 * Loads configuration, the variable registry, the language layer, the content
 * repository and the template engine. No Composer, no autoloader magic: the
 * file list is explicit and ordered.
 */

if (defined('W5D_BOOTED')) {
    return;
}
define('W5D_BOOTED', true);

$w5dRoot = dirname(__DIR__);

// Error reporting: strict in the CLI builders, quieter for the admin panel.
if (PHP_SAPI === 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', getenv('W5D_DEBUG') === '1' ? '1' : '0');
}

require_once $w5dRoot . '/src/Support/Text.php';
require_once $w5dRoot . '/src/Support/Vars.php';
require_once $w5dRoot . '/src/Support/Markup.php';
require_once $w5dRoot . '/src/Support/Json.php';
require_once $w5dRoot . '/src/Lang.php';
require_once $w5dRoot . '/src/Content.php';
require_once $w5dRoot . '/src/Route.php';
require_once $w5dRoot . '/src/View.php';
require_once $w5dRoot . '/src/Registry.php';
require_once $w5dRoot . '/src/Site.php';
require_once $w5dRoot . '/src/helpers.php';

$w5dConfig = require $w5dRoot . '/config/app.php';

date_default_timezone_set((string) ($w5dConfig['timezone'] ?? 'UTC'));

\W5D\Registry::boot($w5dConfig);

return $w5dConfig;
