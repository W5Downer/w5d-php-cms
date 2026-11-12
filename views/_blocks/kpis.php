<?php
/** kpis — labelled figures. items[{label{}, value}]. Values may be {{ tokens }}. */
?>
<dl class="kpis">
<?php foreach (($block['data']['items'] ?? []) as $it): ?>
  <div class="kpi">
    <dt><?= e(loc($it['label'] ?? '', $lang)) ?></dt>
    <dd class="num"><?= e(loc($it['value'] ?? '', $lang)) ?></dd>
  </div>
<?php endforeach; ?>
</dl>
