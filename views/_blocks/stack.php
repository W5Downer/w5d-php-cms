<?php
/** stack — ontology layers as a stack. items[{layer, title{}, question{}, count{}, href, examples[]}] */
use W5D\Router;
?>
<ol class="stack">
<?php foreach (($block['data']['items'] ?? []) as $it):
    $href = isset($it['href']) ? (Router::link($it['href']) ?? '#') : null;
?>
  <li class="stack-layer stack-<?= e($it['layer'] ?? '') ?>">
    <p class="stack-id"><?= e($it['layer_label'] ?? '') ?></p>
    <h3><?php if ($href): ?><a href="<?= e($href) ?>"><?= e(loc($it['title'] ?? '', $lang)) ?></a><?php else: ?><?= e(loc($it['title'] ?? '', $lang)) ?><?php endif; ?></h3>
    <?php if (!empty($it['question'])): ?><p class="stack-q"><?= e(loc($it['question'], $lang)) ?></p><?php endif; ?>
    <?php if (!empty($it['examples'])): ?><p class="stack-ex"><?= e(implode(' · ', $it['examples'])) ?></p><?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
