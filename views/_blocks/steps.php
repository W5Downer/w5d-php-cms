<?php
declare(strict_types=1);

/**
 * Shared block: STEPS
 * Ordered stages with an optional closing note per step.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$steps = (array) ($d['items'] ?? []);
$ordered = (bool) ($d['ordered'] ?? true);
?>
<section class="block block--steps" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <<?= $ordered ? 'ol' : 'ul' ?> class="steps">
      <?php foreach ($steps as $i => $step): ?>
        <?php
          $title = (string) ($step['title'] ?? '');
          $text = (string) ($step['text'] ?? ($step['body'] ?? ''));
          $meta = (string) ($step['meta'] ?? '');
        ?>
        <li class="step">
          <span class="step__num" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <div class="step__body">
            <h3 class="step__title"><?= \W5D\Support\Markup::inline($title, $lang) ?></h3>
            <?php if ($text !== ''): ?>
              <p class="step__text"><?= \W5D\Support\Markup::inline($text, $lang) ?></p>
            <?php endif; ?>
            <?php if ($meta !== ''): ?>
              <p class="step__meta"><?= \W5D\Support\Markup::inline($meta, $lang) ?></p>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </<?= $ordered ? 'ol' : 'ul' ?>>
  </div>
</section>
