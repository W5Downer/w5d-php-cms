<?php
declare(strict_types=1);

/**
 * Shared partial: FOOTER
 * Licence block (required on every page), doctrine links, and the addresses that
 * have been supplied. Anything unsupplied is an open chip, never a guess.
 *
 * @var array  $doc
 * @var array  $nav
 * @var array  $site
 * @var string $lang
 */
$settings = (array) ($site['settings'] ?? []);
$footer = (array) ($site['footer'] ?? []);
$year = date('Y');
$open = (string) v('open_value');
$menu = $footer['menu'] ?? [];
?>
<footer class="sitefoot" role="contentinfo">
  <div class="wrap sitefoot__inner">
    <div class="sitefoot__brand">
      <p class="sitefoot__name"><?= e((string) ($settings['name_long'] ?? 'World 5 Dimensions')) ?></p>
      <p class="sitefoot__tag"><?= e((string) ($settings['tagline'] ?? '')) ?></p>
      <p class="sitefoot__built"><?= e(t('footer.built')) ?></p>
    </div>

    <?php foreach ($menu as $column): ?>
      <nav class="sitefoot__col" aria-label="<?= e(tr($column['label'] ?? '')) ?>">
        <p class="sitefoot__heading"><?= e(tr($column['label'] ?? '')) ?></p>
        <ul class="sitefoot__list">
          <?php foreach ((array) ($column['items'] ?? []) as $item): ?>
            <li>
              <?php if (isset($item['page'])): ?>
                <a href="<?= e(href('page', (string) $item['page'], $lang)) ?>"><?= e(tr($item['label'] ?? $item['page'])) ?></a>
              <?php else: ?>
                <a href="<?= e((string) ($item['href'] ?? '#')) ?>"<?= !empty($item['external']) ? ' rel="noopener noreferrer"' : '' ?>><?= e(tr($item['label'] ?? '')) ?></a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    <?php endforeach; ?>

    <div class="sitefoot__legal">
      <p class="sitefoot__heading"><?= e(t('footer.company_heading')) ?></p>
      <p class="sitefoot__addr"><?= e(t('footer.address')) ?>: <?= e((string) v('site.address')) ?></p>
      <p class="sitefoot__licence">
        <strong><?= e(t('footer.licence_heading')) ?>.</strong>
        <?= e(\W5D\Registry::vars()->resolve((string) v('site.license_footer'))) ?>
      </p>
    </div>
  </div>

  <div class="wrap sitefoot__base">
    <p class="sitefoot__copy"><?= e(t('footer.copyright', ['year' => (string) $year, 'name' => (string) $settings['name']])) ?></p>
    <p class="sitefoot__stamp">
      <?= e(t('footer.rendered')) ?>:
      <time datetime="<?= e(date('Y-m-d')) ?>"><?= e(fmt_date(date('Y-m-d'))) ?></time>
    </p>
  </div>
</footer>
