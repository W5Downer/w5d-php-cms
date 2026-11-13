<?php
/** cards — linked or plain cards. items[{title{}, text{}, meta{}, href}] */
use W5D\Router;
?>
<ul class="cards">
<?php foreach (($block['data']['items'] ?? []) as $it):
    $href = isset($it['href']) ? (Router::link($it['href']) ?? $it['href']) : null;
    $title = loc($it['title'] ?? '', $lang);
?>
  <li class="card">
    <h3 class="card-title"><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($title) ?></a><?php else: ?><?= e($title) ?><?php endif; ?></h3>
    <?php if (!empty($it['meta'])): ?><p class="card-meta"><?= e(loc($it['meta'], $lang)) ?></p><?php endif; ?>
    <?php if (!empty($it['text'])): ?><p><?= e(loc($it['text'], $lang)) ?></p><?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
