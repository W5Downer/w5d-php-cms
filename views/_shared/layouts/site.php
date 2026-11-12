<?php
/**
 * Site layout (shared). Templates may override it at views/templates/{name}/layouts/site.php.
 * Variables: $doc $title $description $body $lang $dir $template $path $fellBack $noindex
 */
use W5D\Config;
use W5D\Variables;

$base = rtrim((string) Config::app('url', 'https://w5d.tech'), '/');
$canonical = $base . $path;
$nav = [
    ['/join/', 'nav_join'], ['/services/', 'nav_services'], ['/macro-works/', 'nav_macros'],
    ['/projects/', 'nav_projects'], ['/ontology/', 'nav_ontology'], ['/trust/statement/', 'nav_trust'],
    ['/about/', 'nav_about'], ['/contact/', 'nav_contact'],
];
$langs = Config::app('languages', ['en']);
$backParam = rawurlencode($path);
$license = Variables::resolve(t('license_footer'));
?>
<!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= e($dir) ?>" data-template="<?= e($template) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · W5D</title>
<?php if ($description !== ''): ?><meta name="description" content="<?= e(strip_tags($description)) ?>"><?php endif; ?>
<?php if ($noindex): ?><meta name="robots" content="noindex,nofollow"><?php else: ?><meta name="robots" content="index,follow"><link rel="canonical" href="<?= e($canonical) ?>"><?php endif; ?>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('_base/css/attention.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('_base/css/templates/' . $template . '.css')) ?>">
</head>
<body class="tpl-<?= e($template) ?>">
<a class="skip" href="#main"><?= e(t('skip')) ?></a>
<header class="site-header">
  <div class="wrap bar">
    <a class="brand" href="/" aria-label="W5D">W5D</a>
    <nav aria-label="<?= e(t('nav_menu')) ?>">
      <ul class="menu">
<?php foreach ($nav as [$href, $key]): ?>
        <li><a href="<?= e($href) ?>"><?= e(t($key)) ?></a></li>
<?php endforeach; ?>
      </ul>
    </nav>
    <ul class="langs" aria-label="<?= e(t('lang_switch')) ?>">
<?php foreach ($langs as $code): ?>
      <li><a href="/language/<?= e($code) ?>?back=<?= $backParam ?>" hreflang="<?= e($code) ?>"<?= $code === $lang ? ' aria-current="true"' : '' ?>><?= e(strtoupper($code)) ?></a></li>
<?php endforeach; ?>
    </ul>
    <a class="btn btn-focus btn-sm" href="/app/register/"><?= e(t('nav_register')) ?></a>
  </div>
</header>
<main id="main" tabindex="-1">
<?php if (!empty($fellBack)): ?>
  <p class="fallback" role="note"><?= e(t('fallback_notice')) ?></p>
<?php endif; ?>
<?= $body ?>
</main>
<footer class="site-footer">
  <div class="wrap">
    <p class="license"><?= e($license) ?></p>
    <p class="foot-links">
      <a href="/about/"><?= e(t('footer_company')) ?></a> ·
      <a href="/trust/statement/"><?= e(t('footer_trust')) ?></a> ·
      <a href="/trust/statistics/"><?= e(t('footer_legal')) ?></a> ·
      <a href="/status/"><?= e(t('nav_trust')) ?></a> ·
      <a href="/<?= e(Config::app('admin_path', 'panel-x9z')) ?>/login"><?= e(t('footer_portal')) ?></a>
    </p>
  </div>
</footer>
<script src="<?= e(asset('_base/js/site.js')) ?>" defer></script>
</body>
</html>
