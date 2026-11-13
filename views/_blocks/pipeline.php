<?php
/** pipeline — Register → Verify → Certify (L1–L4) → Deliver. items[{title{}, text{}, state}] */
?>
<ol class="pipeline">
<?php foreach (($block['data']['items'] ?? []) as $it): ?>
  <li class="pipe-stage pipe-<?= e($it['state'] ?? 'open') ?>">
    <h3><?= e(loc($it['title'] ?? '', $lang)) ?></h3>
    <p><?= e(loc($it['text'] ?? '', $lang)) ?></p>
    <?php if (!empty($it['state_label'])): ?><p class="pipe-state"><?= e(loc($it['state_label'], $lang)) ?></p><?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
