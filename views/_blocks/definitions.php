<?php
/** definitions — term/definition list. items[{term{}, def{}}] */
?>
<dl class="defs">
<?php foreach (($block['data']['items'] ?? []) as $it): ?>
  <div class="def">
    <dt><?= e(loc($it['term'] ?? '', $lang)) ?></dt>
    <dd><?= e(loc($it['def'] ?? '', $lang)) ?></dd>
  </div>
<?php endforeach; ?>
</dl>
