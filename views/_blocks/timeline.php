<?php
declare(strict_types=1);

/**
 * Shared block: TIMELINE
 * Dated milestones. Two lanes: `roadmap` (year only) and `events` (dated).
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$items = (array) ($d['items'] ?? []);
$open = (string) ($d['open'] ?? '');
?>
<section class="block block--timeline" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <ol class="timeline">
      <?php foreach ($items as $item): ?>
        <?php
          $date = (string) ($item['date'] ?? $item['year'] ?? '');
          $kind = (string) ($item['kind'] ?? 'event');
          $title = (string) ($item['title'] ?? '');
          $text = (string) ($item['text'] ?? '');
          $chip = (string) ($item['chip'] ?? '');
          $isPending = !empty($item['open']) || is_open($date);
        ?>
        <li class="timeline__item timeline__item--<?= e($kind) ?>">
          <div class="timeline__date">
            <?php if ($isPending): ?>
              <span class="openchip"><?= e(t('ui.pending')) ?></span>
            <?php else: ?>
              <time datetime="<?= e($date) ?>"><?= e(fmt_date($date, true)) ?></time>
            <?php endif; ?>
          </div>
          <div class="timeline__body">
            <?php if ($kind !== ''): ?><p class="timeline__kind"><?= e(type_label('horizon', $kind) === $kind ? $kind : $kind) ?></p><?php endif; ?>
            <h3 class="timeline__title">
              <?= \W5D\Support\Markup::inline($title, $lang) ?>
              <?php if ($chip !== ''): ?>
                <span class="chip <?= status_class($chip) ?>"><?= e(type_label('status', $chip)) ?></span>
              <?php endif; ?>
            </h3>
            <?php if ($text !== ''): ?><p class="timeline__text"><?= \W5D\Support\Markup::inline($text, $lang) ?></p><?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php if ($open !== ''): ?>
      <p class="openbar"><span class="openchip"><?= e(t('ui.pending')) ?></span> <?= \W5D\Support\Markup::inline($open, $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
