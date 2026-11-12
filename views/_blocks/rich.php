<?php
declare(strict_types=1);

/**
 * Shared block: RICH
 * Escape-hatch HTML for a reviewed, trusted fragment.
 *
 * Content JSON is escaped by default. This block is the ONLY place raw HTML is
 * printed, and it refuses to print anything unless the block is explicitly
 * marked `"trusted": true` in the page file. That keeps the default safe while
 * letting an owner paste a vetted embed (e.g. an inline SVG diagram).
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$html = (string) ($d['html'] ?? '');
$trusted = !empty($d['trusted']);
?>
<section class="block block--rich" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>

    <?php if ($trusted): ?>
      <div class="rich"><?= $html ?></div>
    <?php else: ?>
      <p class="rich rich--untrusted">
        <span class="openchip"><?= e(t('ui.pending')) ?></span>
        <?= e('This fragment is not marked trusted, so it is shown as text.') ?>
      </p>
      <pre class="code"><code><?= e($html) ?></code></pre>
    <?php endif; ?>
  </div>
</section>
