<?php
declare(strict_types=1);

/**
 * Shared block: HERO
 * A page's opening statement. Every value comes from the page's own JSON.
 *
 * @var array  $block ['id','title','data']
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$eyebrow = (string) ($d['eyebrow'] ?? $doc['eyebrow'] ?? '');
$grand = (string) ($d['grand'] ?? $block['title'] ?? $doc['title'] ?? '');
$lead = (string) ($d['lead'] ?? $doc['summary'] ?? '');
$words = $d['split'] ?? true;
$meta = (array) ($d['meta'] ?? []);
$actions = (array) ($d['actions'] ?? []);
$notice = (string) ($d['notice'] ?? '');
?>
<header class="hero" id="<?= e($block['id']) ?>">
  <div class="wrap hero__inner">
    <?php if ($eyebrow !== ''): ?>
      <p class="hero__eyebrow"><?= \W5D\Support\Markup::inline($eyebrow, $lang) ?></p>
    <?php endif; ?>

    <h1 class="hero__title<?= $words ? ' hero__title--split' : '' ?>">
      <?php if ($words): ?>
        <?php foreach (preg_split('/\s+/u', trim($grand)) ?: [] as $i => $word): ?>
          <span class="w" style="--i:<?= $i ?>"><?= e($word) ?></span>
        <?php endforeach; ?>
      <?php else: ?>
        <?= e($grand) ?>
      <?php endif; ?>
    </h1>

    <?php if ($lead !== ''): ?>
      <p class="hero__lead"><?= \W5D\Support\Markup::inline($lead, $lang) ?></p>
    <?php endif; ?>

    <?php if ($meta !== []): ?>
      <dl class="hero__meta">
        <?php foreach ($meta as $item): ?>
          <?php
            $label = (string) ($item['label'] ?? '');
            $valueKey = $item['var'] ?? null;
            $value = $valueKey ? v((string) $valueKey) : (string) ($item['value'] ?? '');
            $chip = $item['chip'] ?? null;
            $open = $item['open'] ?? false;
          ?>
          <div class="hero__metaitem">
            <dt><?= e($label) ?></dt>
            <dd>
              <?php if ($chip !== null): ?>
                <span class="chip <?= status_class((string) $chip) ?>"><?= e(type_label('status', (string) $chip)) ?></span>
              <?php elseif ($open || is_open($value)): ?>
                <span class="openchip"><?= e(t('ui.pending')) ?></span>
              <?php else: ?>
                <span><?= e($value) ?></span>
              <?php endif; ?>
              <?php if (!empty($item['sub'])): ?><small><?= e((string) $item['sub']) ?></small><?php endif; ?>
            </dd>
          </div>
        <?php endforeach; ?>
      </dl>
    <?php endif; ?>

    <?php if ($actions !== []): ?>
      <div class="hero__actions">
        <?php foreach ($actions as $action): ?>
          <?= \W5D\Support\Markup::link(e((string) ($action['label'] ?? t('ui.read_more'))), (string) ($action['href'] ?? ''), $lang) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($notice !== ''): ?>
      <p class="hero__notice"><?= \W5D\Support\Markup::inline($notice, $lang) ?></p>
    <?php endif; ?>
  </div>
</header>
