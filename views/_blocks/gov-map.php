<?php
/**
 * gov-map — schematic tile map of the governorates (inline SVG, no external tiles).
 * Positions come from data/countries/{iso}.json (col, row). Each tile links to /{iso}/{slug}/.
 */
use W5D\Country;
$iso = Country::defaultIso();
$cell = 76; $gap = 6; $pad = 4;
$regions = $block['data']['regions'] ?? [];
$cols = 0; $rows = 0;
foreach ($regions as $r) { $cols = max($cols, (int) ($r['col'] ?? 0) + 1); $rows = max($rows, (int) ($r['row'] ?? 0) + 1); }
$w = $cols * ($cell + $gap) + $pad * 2; $h = $rows * ($cell + $gap) + $pad * 2;
?>
<figure class="gov-map">
  <svg viewBox="0 0 <?= $w ?> <?= $h ?>" role="img" aria-labelledby="govmap-title-<?= e($block['id']) ?>" focusable="false">
    <title id="govmap-title-<?= e($block['id']) ?>"><?= e(t('gov_tile_note')) ?></title>
<?php foreach ($regions as $r):
    $x = $pad + (int) $r['col'] * ($cell + $gap);
    $y = $pad + (int) $r['row'] * ($cell + $gap);
    $label = loc($r['name'] ?? '', $lang);
?>
    <a href="/<?= e($iso) ?>/<?= e($r['slug']) ?>/">
      <rect class="tile" x="<?= $x ?>" y="<?= $y ?>" width="<?= $cell ?>" height="<?= $cell ?>" rx="6"></rect>
      <text class="tile-label" x="<?= $x + $cell / 2 ?>" y="<?= $y + $cell / 2 + 4 ?>" text-anchor="middle"><?= e($label) ?></text>
    </a>
<?php endforeach; ?>
  </svg>
  <?php if (!empty($block['data']['note'])): ?><figcaption><?= e(loc($block['data']['note'], $lang)) ?></figcaption><?php endif; ?>
</figure>
