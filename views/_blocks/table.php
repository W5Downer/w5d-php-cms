<?php
declare(strict_types=1);

/**
 * Shared block: TABLE
 * Renders data.head / data.rows, or a named generated table (`data.source`).
 * Cells may be strings or structured objects handled by Markup::table().
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$head = (array) ($d['head'] ?? []);
$rows = (array) ($d['rows'] ?? []);
$captionText = (string) ($d['caption'] ?? '');
$compact = (bool) ($d['compact'] ?? false);
$source = (string) ($d['source'] ?? '');

// Generated tables read from the document, never re-typed in JSON.
if ($source === 'register' && $rows === []) {
    $head = [t('ui.name'), t('ui.layer'), t('ui.status'), t('ui.evidence'), t('ui.last_verified'), t('ui.what_exists')];
    foreach ($doc['register'] ?? [] as $item) {
        $rows[] = [
            ['text' => $item['name'], 'href' => $item['href']],
            type_label('layer', $item['layer']),
            ['chip' => $item['status']],
            ['badge' => $item['evidence']],
            fmt_date((string) $item['verified'], true),
            $item['exists'],
        ];
    }
}

if ($source === 'variables' && $rows === []) {
    // Live variable table: hand it a group name and it prints every figure.
    $head = [t('ui.variable'), t('ui.value'), t('ui.note')];
    foreach (vars_group((string) ($d['group'] ?? 'counts')) as $path => $entry) {
        $rows[] = [['text' => $entry['label']], ['badge' => $entry['value']], $entry['note'] ?? ''];
    }
}
?>
<section class="block block--table" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <?= \W5D\Support\Markup::table([
        'head' => $head,
        'rows' => $rows,
        'caption' => $captionText,
        'compact' => $compact,
    ], $lang) ?>

    <?php if (!empty($d['note'])): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline((string) $d['note'], $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
