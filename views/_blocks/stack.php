<?php
declare(strict_types=1);

/**
 * Shared block: STACK
 * The six-layer stacked ontology, drawn as a ladder.
 *
 * This block exists so the main structural claim of the project has ONE
 * renderer that every page reuses: the home page, the ontology index, and each
 * individual layer page all draw the same stack from the same content files.
 * A template may restyle it, but it can never restate it differently.
 *
 * data.items = [
 *   {
 *     "layer": "projects",              // key from vars types.ontology_layer
 *     "number": 5,                       // L5 … L0
 *     "title": "Projects",
 *     "question": "What is being built, for whom, by when?",
 *     "body": "A time-bound instantiation that consumes services.",
 *     "href": "ontology:index",          // optional route target
 *     "count": "{{ counts.projects.value }}",  // optional token-bearing figure
 *     "examples": ["MetaX Academy", "KernID", "SelfAI"]
 *   }
 * ]
 * data.direction = "down" | "up"      — which way the constitutive arrows point
 * data.arrows    = bool               — draw the connector arrows (default true)
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$items = (array) ($d['items'] ?? []);
$arrows = !array_key_exists('arrows', $d) || !empty($d['arrows']);
$direction = (string) ($d['direction'] ?? 'down');
?>
<section class="block block--stack" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <ol class="stack stack--<?= e($direction) ?>" role="list">
      <?php foreach ($items as $i => $item): ?>
        <?php
          $number = $item['number'] ?? '';
          $title = (string) ($item['title'] ?? '');
          $question = (string) ($item['question'] ?? '');
          $body = (string) ($item['body'] ?? '');
          $href = (string) ($item['href'] ?? '');
          $count = (string) ($item['count'] ?? '');
          $examples = (array) ($item['examples'] ?? []);
          $layerKey = (string) ($item['layer'] ?? '');
        ?>
        <li class="stack__layer stack__layer--<?= e($layerKey) ?>" data-layer="<?= e($layerKey) ?>">
          <?php if ($arrows && $i > 0): ?>
            <span class="stack__arrow" aria-hidden="true"></span>
          <?php endif; ?>
          <div class="stack__card">
            <div class="stack__head">
              <span class="stack__number" aria-hidden="true">L<?= e((string) $number) ?></span>
              <h3 class="stack__title">
                <?php if ($href !== ''): ?>
                  <?= \W5D\Support\Markup::link(e($title), $href, $lang) ?>
                <?php else: ?>
                  <?= e($title) ?>
                <?php endif; ?>
              </h3>
              <?php if ($count !== ''): ?>
                <span class="stack__count"><?= e(\W5D\Registry::vars()->withLang($lang)->resolve($count)) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($question !== ''): ?>
              <p class="stack__question"><?= e($question) ?></p>
            <?php endif; ?>
            <?php if ($body !== ''): ?>
              <p class="stack__body"><?= \W5D\Support\Markup::inline($body, $lang) ?></p>
            <?php endif; ?>
            <?php if ($examples !== []): ?>
              <ul class="stack__examples" role="list">
                <?php foreach ($examples as $example): ?>
                  <li><?= e((string) $example) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php if (!empty($d['feedback'])): ?>
      <p class="stack__feedback">
        <span class="stack__feedback-mark" aria-hidden="true">↑</span>
        <?= \W5D\Support\Markup::inline((string) $d['feedback'], $lang) ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($d['note'])): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline((string) $d['note'], $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
