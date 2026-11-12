<?php
declare(strict_types=1);

/**
 * Shared layout: APP
 * The document shell for every non-home page. Written once, rendered three
 * times (en/fr/ar) and reused by every installed template: a template only
 * overrides the partials whose look it changes.
 *
 * @var array  $doc
 * @var array  $blocks
 * @var array  $toc
 * @var array  $site
 * @var array  $nav
 * @var string $lang
 */
$config = \W5D\Registry::config();
$dir = \W5D\Registry::lang()->direction($lang);
$bodyClass = 'page page--' . ($doc['kind'] ?? 'page') . ' tpl-' . \W5D\Registry::view()->template();
?>
<!DOCTYPE html>
<html lang="<?= e(\W5D\Registry::lang()->htmlCode($lang)) ?>" dir="<?= e($dir) ?>" class="no-js">
<head>
  <?php echo \W5D\Registry::view()->render('partials.head', ['doc' => $doc, 'site' => $site, 'lang' => $lang]); ?>
</head>
<body class="<?= e($bodyClass) ?>">
  <?php echo \W5D\Registry::view()->render('partials.header', ['doc' => $doc, 'site' => $site, 'nav' => $nav, 'lang' => $lang]); ?>

  <?php echo \W5D\Registry::view()->render('partials.subnav', ['doc' => $doc, 'toc' => $toc, 'lang' => $lang]); ?>

  <main id="main" class="main" tabindex="-1">
    <?php if (!empty($doc['status']) || !empty($doc['layer'])): ?>
      <?php echo \W5D\Registry::view()->render('partials.metabar', ['doc' => $doc, 'lang' => $lang]); ?>
    <?php endif; ?>

    <?php foreach ($blocks as $block): ?>
      <?= render_block($block, $doc, $lang) ?>
    <?php endforeach; ?>
  </main>

  <?php echo \W5D\Registry::view()->render('partials.footer', ['doc' => $doc, 'site' => $site, 'nav' => $nav, 'lang' => $lang]); ?>

  <script src="<?= e(\W5D\Route::asset('_base/js/site.js')) ?>" defer></script>
</body>
</html>
