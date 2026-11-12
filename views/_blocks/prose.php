<?php
declare(strict_types=1);

/**
 * Shared block: PROSE
 * A titled section with a body. The body may be a string (split into
 * paragraphs on blank lines) or a list of typed items.
 *
 * Item types: p · h2 · h3 · h4 · ul · ol · dl · quote · note · callout · table · figure · code
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$lead = (string) ($d['lead'] ?? '');
$body = $d['body'] ?? [];
$note = (string) ($d['note'] ?? '');
$layout = (string) ($d['layout'] ?? 'single');
$aside = (array) ($d['aside'] ?? []);
?>
<section class="block block--prose block--<?= e($layout) ?>" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>

    <?php if ($lead !== ''): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline($lead, $lang) ?></p>
    <?php endif; ?>

    <div class="prose<?= $aside !== [] ? ' prose--withaside' : '' ?>">
      <div class="prose__main">
        <?= \W5D\Support\Markup::body($body, $lang) ?>
      </div>

      <?php if ($aside !== []): ?>
        <aside class="prose__aside">
          <?php foreach ($aside as $item): ?>
            <div class="asidecard">
              <?php if (!empty($item['title'])): ?>
                <p class="asidecard__title"><?= \W5D\Support\Markup::inline((string) $item['title'], $lang) ?></p>
              <?php endif; ?>
              <?php if (!empty($item['body'])): ?>
                <div class="asidecard__body"><?= \W5D\Support\Markup::body($item['body'], $lang) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </aside>
      <?php endif; ?>
    </div>

    <?php if ($note !== ''): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline($note, $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
