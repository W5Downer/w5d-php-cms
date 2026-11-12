<?php
declare(strict_types=1);

/**
 * Shared block: SERVICE-CATALOG
 * All registered services grouped by macro work, generated from
 * data/content/services/*.json. Filters are handled by assets/_base/js/site.js.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$catalog = (array) ($doc['catalog'] ?? []);
$total = 0;
foreach ($catalog as $group) {
    $total += count((array) ($group['services'] ?? []));
}
?>
<section class="block block--catalog" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($block['data']['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $block['data']['lead'], $lang) ?></p>
    <?php endif; ?>

    <form class="catalog__filter" data-catalog-filter>
      <label class="field field--search">
        <span class="field__label"><?= e(t('ui.search')) ?></span>
        <input type="search" class="input" data-catalog-search placeholder="<?= e(t('ui.search')) ?>…" autocomplete="off">
      </label>
      <p class="catalog__count" data-catalog-count aria-live="polite">
        <?= e(t('ui.showing')) ?> <strong><?= e((string) $total) ?></strong> <?= e(t('ui.of')) ?> <?= e((string) $total) ?>
      </p>
    </form>

    <?php foreach ($catalog as $macroSlug => $group): ?>
      <section class="catalog__group" data-catalog-group>
        <header class="catalog__head">
          <h3 class="catalog__title">
            <a href="<?= e((string) $group['href']) ?>"><?= e((string) $group['title']) ?></a>
          </h3>
          <p class="catalog__meta">
            <span class="badge badge--placement"><?= e(type_label('placement', (string) $group['placement'])) ?></span>
            <span class="catalog__countchip"><?= e((string) count((array) $group['services'])) ?> <?= e(t('ui.services')) ?></span>
          </p>
        </header>

        <ul class="catalog__list">
          <?php foreach ($group['services'] as $service): ?>
            <li class="catalog__item" data-catalog-item data-search="<?= e(mb_strtolower($service['title'] . ' ' . $service['short'] . ' ' . $service['number_label'])) ?>">
              <a class="catalog__link" href="<?= e((string) $service['href']) ?>">
                <span class="catalog__num"><?= e((string) $service['number_label']) ?></span>
                <span class="catalog__body">
                  <span class="catalog__name"><?= e((string) $service['title']) ?></span>
                  <span class="catalog__short"><?= e((string) $service['short']) ?></span>
                </span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>

    <p class="catalog__empty" data-catalog-empty hidden><?= e(t('ui.no_results')) ?></p>
  </div>
</section>
