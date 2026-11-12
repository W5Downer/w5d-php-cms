<?php
declare(strict_types=1);

/**
 * Shared block: INDEX
 * A grouped index (used by ecosystem, declarations and macro lists).
 *
 * data.groups = [{"title":"…","items":[{"title","summary","href","chip","meta"}]}]
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$groups = (array) ($d['groups'] ?? []);
$items = (array) ($d['items'] ?? []);
if ($groups === [] && $items !== []) {
    $groups = [['title' => '', 'items' => $items]];
}
?>
<section class="block block--index" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <?php foreach ($groups as $group): ?>
      <section class="indexgroup">
        <?php if (!empty($group['title'])): ?>
          <h3 class="indexgroup__title"><?= \W5D\Support\Markup::inline((string) $group['title'], $lang) ?></h3>
        <?php endif; ?>
        <ul class="indexlist">
          <?php foreach ((array) ($group['items'] ?? []) as $item): ?>
            <?php $hrefValue = (string) ($item['href'] ?? ''); ?>
            <li class="indexlist__item">
              <div class="indexlist__main">
                <span class="indexlist__title">
                  <?php if ($hrefValue !== ''): ?>
                    <a href="<?= e($hrefValue) ?>"><?= e((string) ($item['title'] ?? '')) ?></a>
                  <?php else: ?>
                    <?= e((string) ($item['title'] ?? '')) ?>
                  <?php endif; ?>
                </span>
                <?php if (!empty($item['summary'])): ?>
                  <span class="indexlist__summary"><?= \W5D\Support\Markup::inline((string) $item['summary'], $lang) ?></span>
                <?php endif; ?>
              </div>
              <?php if (!empty($item['chip'])): ?>
                <span class="chip <?= status_class((string) $item['chip']) ?>"><?= e(type_label('status', (string) $item['chip'])) ?></span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endforeach; ?>
  </div>
</section>
