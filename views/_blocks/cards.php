<?php
declare(strict_types=1);

/**
 * Shared block: CARDS
 *
 * Sources, in order of precedence:
 *   data.source = "register" | "declarations" | "services" | "macros" | "projects"
 *   data.items  = explicit list [{title, summary, href, meta[], status, chip}]
 *
 * Using a `source` keeps an index page in sync with the records it lists: add a
 * project JSON file and the ecosystem cards update with no edit to the index.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$source = (string) ($d['source'] ?? '');
$items = (array) ($d['items'] ?? []);
$columns = (int) ($d['columns'] ?? 3);
$numbered = (bool) ($d['numbered'] ?? false);

if ($source !== '' && $items === []) {
    $content = \W5D\Registry::content();

    if ($source === 'declarations' && isset($doc['declarations'])) {
        foreach ($doc['declarations'] as $item) {
            $items[] = ['title' => $item['title'], 'summary' => $item['summary'], 'href' => $item['href'], 'date' => $item['date']];
        }
    } elseif ($source === 'register') {
        foreach ($doc['register'] ?? [] as $item) {
            $items[] = [
                'title' => $item['name'],
                'summary' => $item['exists'],
                'href' => $item['href'],
                'status' => $item['status'],
                'chip' => $item['status'],
                'meta' => [
                    ['label' => t('ui.layer'), 'value' => type_label('layer', $item['layer'])],
                    ['label' => t('ui.evidence'), 'value' => $item['evidence']],
                ],
            ];
        }
    } elseif ($source === 'macros') {
        foreach ($content->macros() as $slug => $macro) {
            $items[] = [
                'title' => Content::tr($macro['title'] ?? '', $lang),
                'summary' => Content::tr($macro['short'] ?? '', $lang),
                'href' => href('macro', (string) $slug, $lang),
                'meta' => [['label' => t('ui.services'), 'value' => (string) v('counts.services_per_macro')]],
            ];
        }
    } elseif ($source === 'projects') {
        foreach ($content->projects() as $slug => $project) {
            $items[] = [
                'title' => Content::tr($project['title'] ?? '', $lang),
                'summary' => Content::tr($project['short'] ?? $project['exists'] ?? '', $lang),
                'href' => href('project', (string) $slug, $lang),
                'chip' => (string) ($project['status'] ?? 'draft'),
            ];
        }
    } elseif ($source === 'groups' && isset($d['groups'])) {
        // Grouped cards: [{group, items:[{title, summary, href, chip}]}]
        foreach ((array) $d['groups'] as $group) {
            foreach ((array) ($group['items'] ?? []) as $item) {
                $item['group'] = $group['title'] ?? '';
                $items[] = $item;
            }
        }
    }
}
?>
<section class="block block--cards" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <ul class="cardgrid cardgrid--<?= $columns ?>">
      <?php foreach ($items as $i => $item): ?>
        <?php
          $title = (string) ($item['title'] ?? '');
          $summary = (string) ($item['summary'] ?? '');
          $linkTarget = (string) ($item['href'] ?? '');
          $chip = (string) ($item['chip'] ?? '');
          $meta = (array) ($item['meta'] ?? []);
          $grand = (string) ($item['grand_title'] ?? '');
        ?>
        <li class="card">
          <div class="card__head">
            <?php if ($numbered): ?>
              <span class="card__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <?php endif; ?>
            <?php if ($chip !== ''): ?>
              <span class="chip <?= status_class($chip) ?>"><?= e(type_label('status', $chip)) ?></span>
            <?php endif; ?>
            <?php if (!empty($item['group'])): ?>
              <span class="card__group"><?= e((string) $item['group']) ?></span>
            <?php endif; ?>
          </div>

          <?php if ($grand !== ''): ?>
            <p class="card__grand"><?= e($grand) ?></p>
          <?php endif; ?>

          <h3 class="card__title">
            <?php if ($linkTarget !== ''): ?>
              <a href="<?= e($linkTarget) ?>"><?= e($title) ?></a>
            <?php else: ?>
              <?= e($title) ?>
            <?php endif; ?>
          </h3>

          <?php if ($summary !== ''): ?>
            <p class="card__text"><?= \W5D\Support\Markup::inline($summary, $lang) ?></p>
          <?php endif; ?>

          <?php if ($meta !== []): ?>
            <dl class="card__meta">
              <?php foreach ($meta as $entry): ?>
                <div>
                  <dt><?= e((string) ($entry['label'] ?? '')) ?></dt>
                  <dd><?= e((string) ($entry['value'] ?? '')) ?></dd>
                </div>
              <?php endforeach; ?>
            </dl>
          <?php endif; ?>

          <?php if (!empty($item['date'])): ?>
            <p class="card__date"><?= e(fmt_date((string) $item['date'], true)) ?></p>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
