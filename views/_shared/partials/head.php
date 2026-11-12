<?php
declare(strict_types=1);

/**
 * Shared partial: HEAD
 * Everything inside <head> except the title, which the layout owns.
 *
 * CSP note: the static build ships a Content-Security-Policy that allows only
 * the site's own origin. That is why the theme preference is applied by an
 * inline script with a nonce-free hash-free attribute: this one line is
 * permitted through the `script-src 'unsafe-inline'` exception documented in
 * docs/security.md, or replaced by the external js file when CSP is strict.
 *
 * @var array  $doc
 * @var array  $site
 * @var string $lang
 */
$config = \W5D\Registry::config();
$template = \W5D\Registry::view()->template();
$preview = (bool) ($config['preview'] ?? false);
$vars = \W5D\Registry::vars();
$langs = (array) $config['langs'];
$default = (string) $config['default_lang'];
$title = trim($doc['title'] . ' · ' . $site['settings']['name'] ?? 'W5D');
$description = (string) ($doc['description'] ?? '') !== '' ? (string) $doc['description'] : (string) ($doc['summary'] ?? '');
$canonical = \W5D\Route::absolute(href($doc['route_kind'] ?? 'page', $doc['id'], $lang));
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e(\W5D\Support\Text::summary($description, 165)) ?>">
<meta name="author" content="<?= e((string) ($site['settings']['founder'] ?? 'W5D')) ?>">
<meta name="application-name" content="<?= e((string) ($site['settings']['name'] ?? 'W5D')) ?>">
<meta name="theme-color" content="#070711" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#fbfaf7" media="(prefers-color-scheme: light)">
<meta name="color-scheme" content="dark light">
<meta name="format-detection" content="telephone=no">

<?php if ($preview): ?>
  <meta name="robots" content="noindex, nofollow">
<?php else: ?>
  <meta name="robots" content="index, follow, max-image-preview:large">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <link rel="alternate" hreflang="<?= e($lang) ?>" href="<?= e($canonical) ?>">
  <?php foreach ($langs as $code): ?>
    <link rel="alternate" hreflang="<?= e($code) ?>" href="<?= e(\W5D\Route::absolute(href($doc['route_kind'] ?? 'page', $doc['id'], (string) $code))) ?>">
  <?php endforeach; ?>
  <link rel="alternate" hreflang="x-default" href="<?= e(\W5D\Route::absolute(href($doc['route_kind'] ?? 'page', $doc['id'], $default))) ?>">
<?php endif; ?>

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e((string) ($site['settings']['name_long'] ?? 'World 5 Dimensions')) ?>">
<meta property="og:title" content="<?= e($doc['title']) ?>">
<meta property="og:description" content="<?= e(\W5D\Support\Text::summary($description, 200)) ?>">
<meta property="og:locale" content="<?= e(\W5D\Registry::lang()->ogLocale($lang)) ?>">
<?php if (!$preview): ?>
  <meta property="og:url" content="<?= e($canonical) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($doc['title']) ?>">
<meta name="twitter:description" content="<?= e(\W5D\Support\Text::summary($description, 200)) ?>">

<link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
<link rel="manifest" href="<?= e(url('/site.webmanifest')) ?>">

<link rel="preload" href="<?= e(\W5D\Route::asset('_base/fonts/inter-latin.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(\W5D\Route::asset('_base/css/base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/template.css')) ?>">
<?php if (\W5D\Registry::lang()->isRtl($lang)): ?>
  <link rel="stylesheet" href="<?= e(\W5D\Route::asset('_base/css/rtl.css')) ?>">
<?php endif; ?>

<script>
  /* Apply the stored theme before first paint so there is no flash. */
  (function () {
    try {
      var t = localStorage.getItem('w5d-theme') || 'auto';
      if (t !== 'auto') document.documentElement.setAttribute('data-theme', t);
    } catch (e) {}
  })();
</script>
