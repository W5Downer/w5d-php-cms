<?php
declare(strict_types=1);

/**
 * Shared block: BARS
 * A horizontal comparison of figures, all read from the variables registry or
 * explicit values. Used for headcount, capacity and funding-versus-payroll.
 *
 * data.items = [{"label":"…","var":"counts.experts_target"} | {"label":"…","value":3200,"max":130000}]
 * data.max   = shared scale (otherwise the largest item wins)
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$items = (array) ($d['items'] ?? []);
$scaleMax = (float) ($d['max'] ?? 0);

// Resolve each item to a numeric value plus its display string.
$rows = [];
foreach ($items as $item) {
    $path = (string) ($item['var'] ?? '');
    if ($path !== '') {
        $numeric = (float) (\W5D\Registry::vars()->value($path) ?? 0);
        $display = v($path);
        $label = (string) ($item['label'] ?? vlabel($path));
    } else {
        $numeric = (float) ($item['value'] ?? 0);
        $display = (string) ($item['display'] ?? number_format($numeric));
        $label = (string) ($item['label'] ?? '');
    }
    $rows[] = [
        'label' => $label,
        'display' => $display,
        'value' => $numeric,
        'tone' => (string) ($item['tone'] ?? 'neutral'),
        'note' => (string) ($item['note'] ?? ($path !== '' ? (vnote($path) ?? '') : '')),
    ];
}

if ($scaleMax <= 0) {
    foreach ($rows as $row) {
        $scaleMax = max($scaleMax, $row['value']);
    }
}
$scaleMax = $scaleMax > 0 ? $scaleMax : 1;
?>
<section class="block block--bars" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <div class="bars" role="list">
      <?php foreach ($rows as $row): ?>
        <?php $pct = max(0.8, min(100, $row['value'] / $scaleMax * 100)); ?>
        <div class="bar bar--<?= e($row['tone']) ?>" role="listitem">
          <div class="bar__head">
            <span class="bar__label"><?= e($row['label']) ?></span>
            <span class="bar__value"><?= e($row['display']) ?></span>
          </div>
          <div class="bar__track"><span class="bar__fill" style="--pct:<?= e(number_format($pct, 1, '.', '')) ?>%"></span></div>
          <?php if ($row['note'] !== ''): ?><p class="bar__note"><?= e($row['note']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($d['note'])): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline((string) $d['note'], $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
