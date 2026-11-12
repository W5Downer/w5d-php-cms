<?php
/**
 * Single front controller for the public site, registration and owner portal.
 * Lives at the repository root (no public/ folder). Apache rewrites every
 * non-file request here (see .htaccess).
 */
declare(strict_types=1);

define('W5D_ROOT', __DIR__);

require __DIR__ . '/src/bootstrap.php';

W5D\App::run();
