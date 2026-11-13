<?php
/** steps — ordered list. items[{title{}, text{}}] */
?>
<ol class="steps">
<?php foreach (($block['data']['items'] ?? []) as $i => $it): ?>
  <li>
    <span class="step-no" aria-hidden="true"><?= $i + 1 ?></span>
    <div>
      <h3><?= e(loc($it['title'] ?? '', $lang)) ?></h3>
      <?php if (!empty($it['text'])): ?><p><?= e(loc($it['text'], $lang)) ?></p><?php endif; ?>
    </div>
  </li>
<?php endforeach; ?>
</ol>
