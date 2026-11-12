<?php
declare(strict_types=1);

/**
 * Shared block: KPIS
 * A strip of figures. Each item is EITHER a variable path (`var`) or a literal
 * (`value`), so a page can show live registry figures without ever typing them.
 *
 * data.items = [
 *   {"var":"counts.services","label":"Registered services", "note":"…"},
 *   {"var":"counts.experts_target","prefix":"~"},
 *   {"value":"2026-10-10","format":"date"}
 * ]
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$items = (array) ($d['items'] ?? []);
$vars = \W5D\Registry::vars();

// No explicit items: show the whole group named by data.group.
if ($items === [] && !empty($d['group'])) {
    foreach (vars_group((string) $d['group']) as $path => $entry) {
        $items[] = ['var' => $path];
    }
}
?>
<section class="block block--kpis" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <dl class="kpis">
      <?php foreach ($items as $item): ?>
        <?php
          $path = (string) ($item['var'] ?? '');
          $label = (string) ($item['label'] ?? ($path !== '' ? vlabel($path) : ''));
          $value = $path !== '' ? v($path) : (string) ($item['value'] ?? '');
          $note = (string) ($item['note'] ?? ($path !== '' ? (vnote($path) ?? '') : ''));
          $source = $path !== '' ? \W5D\Registry::vars()->source($path) : null;
          $uncertain = !empty($item['uncertain']);
          $sub = (string) ($item['sub'] ?? '');
        ?>
        <div class="kpi<?= $uncertain ? ' kpi--uncertain' : '' ?>">
          <dt class="kpi__label"><?= e($label) ?></dt>
          <dd class="kpi__value">
            <?php if ($uncertain): ?><span aria-hidden="true">~</span><?php endif; ?>
            <?= e($value) ?>
          </dd>
          <?php if ($sub !== ''): ?><p class="kpi__unit"><?= e($sub) ?></p><?php endif; ?>
          <?php if ($note !== ''): ?><p class="kpi__note"><?= e($note) ?></p><?php endif; ?>
          <?php if ($source !== null): ?><p class="kpi__src"><?= e(t('ui.source')) ?>: <code><?= e($source) ?></code></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </dl>

    <?php if (!empty($d['note'])): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline((string) $d['note'], $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
