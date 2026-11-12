<?php
declare(strict_types=1);

/**
 * Shared partial: LANGUAGE SWITCHER
 * Cookie-free by design: every language has a real URL, so the switcher is a
 * set of ordinary links. The `data-route` attribute lets site.js swap only the
 * language prefix on the *current* page, including after a hash navigation, with
 * no JavaScript requirement — the links work on their own.
 *
 * @var array  $doc
 * @var string $lang
 */
$config = \W5D\Registry::config();
$langs = (array) $config['langs'];
$routeKind = (string) ($doc['route_kind'] ?? 'page');
$id = (string) ($doc['id'] ?? '');
?>
<details class="langswitch" data-langswitch>
  <summary class="langswitch__button" aria-label="<?= e(t('nav.language')) ?>">
    <span class="langswitch__code" aria-hidden="true"><?= e(strtoupper($lang)) ?></span>
    <span class="langswitch__label"><?= e(\W5D\Registry::lang()->nativeName($lang)) ?></span>
  </summary>
  <div class="langswitch__panel">
    <p class="langswitch__hint"><?= e(t('nav.language_hint')) ?></p>
    <ul class="langswitch__list">
      <?php foreach ($langs as $code): ?>
        <li>
          <a class="langswitch__link<?= $code === $lang ? ' is-current' : '' ?>"
             href="<?= e(href($routeKind, $id, (string) $code)) ?>"
             hreflang="<?= e((string) $code) ?>"
             lang="<?= e((string) $code) ?>"
             data-route="<?= e($routeKind . ':' . $id) ?>"
             data-lang="<?= e((string) $code) ?>"
             <?= $code === $lang ? 'aria-current="true"' : '' ?>>
            <span class="langswitch__name"><?= e(\W5D\Registry::lang()->nativeName((string) $code)) ?></span>
            <span class="langswitch__meta"><?= e(strtoupper((string) $code)) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</details>
