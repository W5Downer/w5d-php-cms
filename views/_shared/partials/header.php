<?php
declare(strict_types=1);

/**
 * Shared partial: HEADER
 * Brand, primary navigation and the language switcher.
 *
 * The nav is generated from site.json → nav → groups → page ids, and each link's
 * label comes from the page's own JSON. A page therefore cannot appear in the
 * menu under a heading that disagrees with the page itself.
 *
 * @var array  $doc
 * @var array  $nav
 * @var array  $site
 * @var string $lang
 */
$config = \W5D\Registry::config();
$preview = (bool) ($config['preview'] ?? false);
$currentId = (string) ($doc['id'] ?? '');
?>
<a class="skip" href="#main"><?= e(t('a11y.skip_to_main')) ?></a>

<?php if ($preview): ?>
  <p class="previewbar" role="status">
    <span class="previewbar__dot" aria-hidden="true"></span>
    <?= e(t('footer.preview_note')) ?>
  </p>
<?php endif; ?>

<header class="sitehead" data-sitehead>
  <div class="wrap sitehead__inner">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e((string) ($site['settings']['name_long'] ?? 'W5D')) ?>">
      <span class="brand__mark" aria-hidden="true">
        <svg viewBox="0 0 32 32" width="30" height="30" focusable="false">
          <path d="M4 6h5l3 12 3-12h4l3 12 3-12h5l-6 20h-4l-3-11-3 11H10z" fill="currentColor"/>
        </svg>
      </span>
      <span class="brand__text">
        <span class="brand__name"><?= e((string) ($site['settings']['name'] ?? 'W5D')) ?></span>
        <span class="brand__tag"><?= e((string) ($site['settings']['tagline'] ?? '')) ?></span>
      </span>
    </a>

    <button class="navtoggle" type="button" aria-expanded="false" aria-controls="sitenav" data-navtoggle>
      <span class="navtoggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="navtoggle__text"><?= e(t('nav.menu')) ?></span>
    </button>

    <nav class="sitenav" id="sitenav" aria-label="<?= e(t('nav.primary')) ?>" data-sitenav>
      <ul class="sitenav__list">
        <?php foreach ($nav as $group): ?>
          <li class="sitenav__group">
            <span class="sitenav__grouplabel"><?= e(tr($group['label'])) ?></span>
            <ul class="sitenav__sub">
              <?php foreach ($group['items'] as $item): ?>
                <li>
                  <a class="sitenav__link<?= $item['id'] === $currentId ? ' is-current' : '' ?>"
                     href="<?= e((string) $item['href']) ?>"
                     <?= $item['id'] === $currentId ? 'aria-current="page"' : '' ?>>
                    <?= e(tr($item['label'])) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="sitehead__tools">
      <?php echo \W5D\Registry::view()->render('partials.langswitch', ['doc' => $doc, 'lang' => $lang]); ?>
      <button class="themetoggle" type="button" data-themetoggle aria-label="<?= e(t('ui.theme')) ?>" title="<?= e(t('ui.theme')) ?>">
        <span aria-hidden="true" data-themeicon>◐</span>
      </button>
    </div>
  </div>
</header>
