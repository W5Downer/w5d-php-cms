<?php
declare(strict_types=1);

/**
 * Shared partial: SUBNAV
 * A sticky on-this-page rail, rendered only when a page has three or more
 * heading-bearing blocks. Purely in-page links: it works with scripts disabled.
 *
 * @var array  $doc
 * @var array  $toc
 * @var string $lang
 */
$items = (array) ($toc ?? []);
?>
<?php if (count((array) $items) >= 3): ?>
  <div class="subnav" data-subnav>
    <div class="wrap subnav__inner">
      <p class="subnav__label"><?= e(t('ui.on_this_page')) ?></p>
      <nav class="subnav__nav" aria-label="<?= e(t('ui.on_this_page')) ?>">
        <ul class="subnav__list">
          <?php foreach ($items as $item): ?>
            <li><a href="#<?= e((string) $item['id']) ?>"><?= e((string) $item['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </div>
<?php endif; ?>
