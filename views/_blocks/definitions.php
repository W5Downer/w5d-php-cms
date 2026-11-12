<?php
declare(strict_types=1);

/**
 * Shared block: DEFINITIONS
 * A description list, optionally grouped. Unsupplied values render as the
 * shared "to be confirmed" chip instead of a guessed value.
 *
 * data.items  = [{"term":"Legal name","value":"…"} | {"term":"…","var":"money.license_rate"} | {"term":"…","open":true}]
 * data.groups = [{"title":"…","items":[…]}]
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$groups = (array) ($d['groups'] ?? []);
$items = (array) ($d['items'] ?? []);
if ($groups === [] && $items !== []) {
    $groups = [['title' => '', 'items' => $items]];
}

$renderItems = static function (array $list) use ($lang): string {
    $out = '<dl class="deflist deflist--panel">';
    foreach ($list as $item) {
        $term = (string) ($item['term'] ?? $item['label'] ?? '');
        $varPath = (string) ($item['var'] ?? '');
        $value = $varPath !== '' ? v($varPath) : (string) ($item['value'] ?? '');
        $isOpen = !empty($item['open']) || ($value === '');
        $chip = $item['chip'] ?? null;
        $badge = !empty($item['badge']);
        $href = (string) ($item['href'] ?? '');

        $out .= '<div class="deflist__row"><dt>' . e($term) . '</dt><dd>';
        if ($chip !== null) {
            $out .= '<span class="chip ' . status_class((string) $chip) . '">' . e(type_label('status', (string) $chip)) . '</span>';
        } elseif ($badge) {
            $out .= '<span class="badge">' . e($value) . '</span>';
        } elseif ($isOpen) {
            $out .= '<span class="openchip">' . e(t('ui.pending')) . '</span>';
        } elseif ($href !== '') {
            $out .= \W5D\Support\Markup::link(e($value), $href, $lang);
        } else {
            $out .= e($value);
        }
        if (!empty($item['note'])) {
            $out .= '<small>' . \W5D\Support\Markup::inline((string) $item['note'], $lang) . '</small>';
        }
        $out .= '</dd></div>';
    }
    return $out . '</dl>';
};
?>
<section class="block block--definitions" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <?php if (count((array) $groups) > 1): ?>
      <div class="defgroups">
        <?php foreach ($groups as $group): ?>
          <section class="defgroup">
            <?php if (!empty($group['title'])): ?>
              <h3 class="defgroup__title"><?= \W5D\Support\Markup::inline((string) $group['title'], $lang) ?></h3>
            <?php endif; ?>
            <?= $renderItems((array) ($group['items'] ?? [])) ?>
          </section>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?= $renderItems((array) ($groups[0]['items'] ?? [])) ?>
    <?php endif; ?>
  </div>
</section>
