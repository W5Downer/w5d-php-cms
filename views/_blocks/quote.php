<?php
declare(strict_types=1);

/**
 * Shared block: QUOTE
 * A founder's line, a declaration extract, or a pull quote.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$text = (string) ($d['text'] ?? '');
$cite = (string) ($d['cite'] ?? '');
$source = (string) ($d['source'] ?? '');
$hrefTarget = (string) ($d['href'] ?? '');
?>
<section class="block block--quote" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <figure class="pullquote">
      <blockquote>
        <p><?= \W5D\Support\Markup::inline($text, $lang) ?></p>
      </blockquote>
      <figcaption>
        <?php if ($cite !== ''): ?><span class="pullquote__cite"><?= e($cite) ?></span><?php endif; ?>
        <?php if ($source !== ''): ?>
          <span class="pullquote__source">
            <?php if ($hrefTarget !== ''): ?>
              <?= \W5D\Support\Markup::link(e($source), $hrefTarget, $lang) ?>
            <?php else: ?>
              <?= e($source) ?>
            <?php endif; ?>
          </span>
        <?php endif; ?>
      </figcaption>
    </figure>
  </div>
</section>
