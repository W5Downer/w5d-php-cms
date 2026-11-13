<?php
/** faq — native accordion (details/summary); no JavaScript. items[{q{}, a{}}] */
?>
<div class="faq">
<?php foreach (($block['data']['items'] ?? []) as $it): ?>
  <details>
    <summary><?= e(loc($it['q'] ?? '', $lang)) ?></summary>
    <p><?= e(loc($it['a'] ?? '', $lang)) ?></p>
  </details>
<?php endforeach; ?>
</div>
