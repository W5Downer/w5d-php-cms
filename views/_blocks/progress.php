<?php
/** progress — one open progress bar (Zeigarnik): shows what is done and what remains. data: label{}, value, max, remaining{} */
$value = (float) (is_numeric($block['data']['value'] ?? null) ? $block['data']['value'] : 0);
$max = max(1.0, (float) ($block['data']['max'] ?? 1));
$w = (int) (round(min(100.0, $value / $max * 100) / 5) * 5);
?>
<div class="progress">
  <p class="bar-label"><?= e(loc($block['data']['label'] ?? '', $lang)) ?></p>
  <div class="bar-track" role="progressbar" aria-valuemin="0" aria-valuemax="<?= (int) $max ?>" aria-valuenow="<?= (int) $value ?>"><div class="bar-fill w-<?= $w ?>"></div></div>
  <?php if (!empty($block['data']['remaining'])): ?><p class="bar-note"><?= e(loc($block['data']['remaining'], $lang)) ?></p><?php endif; ?>
</div>
