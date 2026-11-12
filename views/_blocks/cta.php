<?php
declare(strict_types=1);

/**
 * Shared block: CTA
 * A closing call to action. Used on most pages so every page has a next step.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$text = (string) ($d['text'] ?? '');
$actions = (array) ($d['actions'] ?? []);
?>
<section class="block block--cta" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <div class="cta">
      <p class="cta__text"><?= \W5D\Support\Markup::inline($text, $lang) ?></p>
      <?php if ($actions !== []): ?>
        <p class="cta__actions">
          <?php foreach ($actions as $action): ?>
            <a class="button<?= !empty($action['primary']) ? ' button--primary' : '' ?>" href="<?= e((string) ($action['href'] ?? '#')) ?>">
              <?= e((string) ($action['label'] ?? t('ui.read_more'))) ?>
            </a>
          <?php endforeach; ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>
