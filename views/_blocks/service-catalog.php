<?php
/** service-catalog — same data as cards, styled as the service list. */
use W5D\Router;
?>
<ul class="cards catalog">
<?php foreach (($block['data']['items'] ?? []) as $it):
    $href = isset($it['href']) ? (Router::link($it['href']) ?? $it['href']) : null;
?>
  <li class="card">
    <?php if (!empty($it['meta'])): ?><p class="card-meta"><?= e(loc($it['meta'], $lang)) ?></p><?php endif; ?>
    <h3 class="card-title"><?php if ($href): ?><a href="<?= e($href) ?>"><?= e(loc($it['title'] ?? '', $lang)) ?></a><?php else: ?><?= e(loc($it['title'] ?? '', $lang)) ?><?php endif; ?></h3>
    <?php if (!empty($it['text'])): ?><p><?= e(loc($it['text'], $lang)) ?></p><?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
