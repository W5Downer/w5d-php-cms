<?php
/** table — responsive data table. head[{en}], rows[[cell{}...]] */
?>
<div class="table-scroll">
<table class="data">
  <?php if (!empty($block['data']['head'])): ?>
  <thead><tr><?php foreach ($block['data']['head'] as $h): ?><th scope="col"><?= e(loc($h, $lang)) ?></th><?php endforeach; ?></tr></thead>
  <?php endif; ?>
  <tbody>
  <?php foreach (($block['data']['rows'] ?? []) as $row): ?>
    <tr><?php foreach ($row as $cell): ?><td><?= e(loc($cell, $lang)) ?></td><?php endforeach; ?></tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
