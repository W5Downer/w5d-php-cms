<?php
declare(strict_types=1);

/**
 * Shared layout: HOME
 * The home page keeps the same shell but adds a full-bleed "figure band" after
 * the hero, built from the variables registry so the home page cannot state a
 * number the rest of the site contradicts.
 *
 * @var array  $doc
 * @var array  $blocks
 * @var array  $toc
 * @var array  $site
 * @var array  $nav
 * @var string $lang
 */
$dir = \W5D\Registry::lang()->direction($lang);
$bodyClass = 'page page--home tpl-' . \W5D\Registry::view()->template();
?>
<!DOCTYPE html>
<html lang="<?= e(\W5D\Registry::lang()->htmlCode($lang)) ?>" dir="<?= e($dir) ?>" class="no-js">
<head>
  <?php echo \W5D\Registry::view()->render('partials.head', ['doc' => $doc, 'site' => $site, 'lang' => $lang]); ?>
</head>
<body class="<?= e($bodyClass) ?>">
  <?php echo \W5D\Registry::view()->render('partials.header', ['doc' => $doc, 'site' => $site, 'nav' => $nav, 'lang' => $lang]); ?>

  <main id="main" class="main main--home" tabindex="-1">
    <?php foreach ($blocks as $i => $block): ?>
      <?= render_block($block, $doc, $lang) ?>
      <?php if ($i === 0): ?>
        <?php echo \W5D\Registry::view()->render('partials.band', ['doc' => $doc, 'lang' => $lang]); ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </main>

  <?php echo \W5D\Registry::view()->render('partials.footer', ['doc' => $doc, 'site' => $site, 'nav' => $nav, 'lang' => $lang]); ?>

  <script src="<?= e(\W5D\Route::asset('_base/js/site.js')) ?>" defer></script>
</body>
</html>
