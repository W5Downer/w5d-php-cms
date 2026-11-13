<?php
/** carousel — horizontal scroll-snap strip (no JavaScript). Same items as cards. */
use W5D\Router;
?>
<ul class="carousel">
<?php foreach (($block['data']['items'] ?? []) as $it):
    $href = isset($it['href']) ? (Router::link($it['href']) ?? $it['href']) : null;
?>
  <li class="carousel-item grad-<?= e($it['gradient'] ?? 'calm') ?>">
    <h3><?php if ($href): ?><a href="<?= e($href) ?>"><?= e(loc($it['title'] ?? '', $lang)) ?></a><?php else: ?><?= e(loc($it['title'] ?? '', $lang)) ?><?php endif; ?></h3>
    <?php if (!empty($it['text'])): ?><p><?= e(loc($it['text'], $lang)) ?></p><?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
