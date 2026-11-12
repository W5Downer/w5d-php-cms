<?php
/** quote — a statement with attribution. data: text{}, by{} */
?>
<figure class="quote">
  <blockquote><p><?= e(loc($block['data']['text'] ?? '', $lang)) ?></p></blockquote>
  <?php if (!empty($block['data']['by'])): ?><figcaption><?= e(loc($block['data']['by'], $lang)) ?></figcaption><?php endif; ?>
</figure>
