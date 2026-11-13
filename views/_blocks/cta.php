<?php
/** cta — supporting text. The button itself is rendered by View::cta (one per block). */
use W5D\Markup;
if (!empty($block['data']['text'])): ?>
<p class="cta-text"><?= Markup::inline(loc($block['data']['text'], $lang)) ?></p>
<?php endif;
