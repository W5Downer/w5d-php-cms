<?php
declare(strict_types=1);

/**
 * Shared partial: BAND
 * The home page's figure band. Every figure is read from the variables registry
 * at build time, so the band can never drift from the register, the macro pages
 * or the payroll page.
 *
 * @var array  $doc
 * @var string $lang
 */
$figures = [
    ['path' => 'counts.dimensions', 'note' => 'space ×3, information, possibility'],
    ['path' => 'counts.macros', 'note' => 'macro works'],
    ['path' => 'counts.services', 'note' => 'registered services'],
    ['path' => 'counts.developers_current', 'note' => 'delivering today'],
];
?>
<section class="band" aria-label="<?= e(t('ui.figure')) ?>">
  <div class="wrap band__inner">
    <?php foreach ($figures as $figure): ?>
      <div class="band__item">
        <p class="band__value"><?= e(v((string) $figure['path'])) ?></p>
        <p class="band__label"><?= e(vlabel((string) $figure['path'])) ?></p>
        <p class="band__note"><?= e((string) $figure['note']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
