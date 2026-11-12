<?php
declare(strict_types=1);

/**
 * Shared block: NOTICE
 * Declared limits, warnings and open-item statements. Tone drives the colour:
 * note · limits · warn · open · info · success
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$tone = (string) ($d['tone'] ?? 'note');
$body = $d['body'] ?? ($d['text'] ?? '');
$items = (array) ($d['items'] ?? []);
$icon = $tone === 'limits' ? '⚠' : ($tone === 'open' ? '○' : 'ℹ');
?>
<section class="block block--notice" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <aside class="notice notice--<?= e(preg_replace('/[^a-z-]/', '', strtolower($tone))) ?>">
      <p class="notice__title">
        <span class="notice__icon" aria-hidden="true"><?= e($icon) ?></span>
        <?= e($block['title'] !== '' ? $block['title'] : t('ui.declared_limits')) ?>
      </p>
      <?php if ($body !== ''): ?>
        <div class="notice__body"><?= \W5D\Support\Markup::body($body, $lang) ?></div>
      <?php endif; ?>
      <?php if ($items !== []): ?>
        <ul class="notice__list">
          <?php foreach ($items as $item): ?>
            <?php
              $isOpen = is_array($item) ? !empty($item['open']) : false;
              $text = is_array($item) ? (string) ($item['text'] ?? '') : (string) $item;
            ?>
            <li>
              <?= \W5D\Support\Markup::inline($text, $lang) ?>
              <?php if ($isOpen): ?><span class="openchip"><?= e(t('ui.pending')) ?></span><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </aside>
  </div>
</section>
