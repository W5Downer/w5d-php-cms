<?php
/** bars — share bars. items[{label{}, value, max, unit{}}]. Width is a class (CSP forbids inline style). */
$bucket = static function (float $v, float $max): int {
    if ($max <= 0) { return 0; }
    $pct = max(0.0, min(100.0, $v / $max * 100));
    return (int) (round($pct / 5) * 5);
};
?>
<div class="bars">
<?php foreach (($block['data']['items'] ?? []) as $it):
    $value = (float) (is_numeric($it['value'] ?? null) ? $it['value'] : 0);
    $max = (float) ($it['max'] ?? 1);
    $w = $bucket($value, $max);
?>
  <div class="bar">
    <p class="bar-label"><?= e(loc($it['label'] ?? '', $lang)) ?> <span class="bar-unit"><?= e(loc($it['unit'] ?? '', $lang)) ?></span></p>
    <div class="bar-track" role="img" aria-label="<?= e($w) ?>%"><div class="bar-fill w-<?= $w ?>"></div></div>
  </div>
<?php endforeach; ?>
<?php if (!empty($block['data']['note'])): ?><p class="bar-note"><?= e(loc($block['data']['note'], $lang)) ?></p><?php endif; ?>
</div>
