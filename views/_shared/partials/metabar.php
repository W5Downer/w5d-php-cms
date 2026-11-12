<?php
declare(strict_types=1);

/**
 * Shared partial: METABAR
 * Status, evidence, layer, horizon and the dated verification for a register
 * record. Every field comes from the record's own JSON; an absent field is
 * simply not shown, and an unsupplied value shows the shared open chip.
 *
 * @var array  $doc
 * @var string $lang
 */
$vars = \W5D\Registry::vars();
$status = (string) ($doc['status'] ?? '');
$evidence = (string) ($doc['evidence'] ?? '');
$layer = (string) ($doc['layer'] ?? '');
$record = (array) ($doc['record'] ?? []);
$verified = (string) ($record['verified'] ?? '');
$horizon = (string) ($record['horizon'] ?? '');
$dimension = $record['dimension'] ?? null;
$stage = $status !== '' ? stage_percent($status) : 0;
?>
<div class="metabar">
  <div class="wrap metabar__inner">
    <?php if ($status !== ''): ?>
      <p class="metabar__item">
        <span class="metabar__label"><?= e(t('ui.status')) ?></span>
        <span class="chip <?= status_class($status) ?>"><?= e(type_label('status', $status)) ?></span>
      </p>
    <?php endif; ?>

    <?php if ($evidence !== ''): ?>
      <p class="metabar__item">
        <span class="metabar__label"><?= e(t('ui.evidence')) ?></span>
        <span class="badge badge--evidence"><?= e($evidence) ?></span>
        <span class="metabar__sub"><?= e(type_label('evidence', $evidence)) ?></span>
      </p>
    <?php endif; ?>

    <?php if ($layer !== ''): ?>
      <p class="metabar__item">
        <span class="metabar__label"><?= e(t('ui.layer')) ?></span>
        <span class="metabar__value"><?= e(type_label('layer', $layer)) ?></span>
      </p>
    <?php endif; ?>

    <?php if ($horizon !== ''): ?>
      <p class="metabar__item">
        <span class="metabar__label"><?= e(t('ui.horizon')) ?></span>
        <span class="metabar__value"><?= e(type_label('horizon', $horizon)) ?></span>
      </p>
    <?php endif; ?>

    <?php if (is_string($dimension) || is_array($dimension)): ?>
      <p class="metabar__item">
        <span class="metabar__label"><?= e(t('ui.dimension')) ?></span>
        <span class="metabar__value"><?= e(tr($dimension, $lang)) ?></span>
      </p>
    <?php endif; ?>

    <p class="metabar__item">
      <span class="metabar__label"><?= e(t('ui.last_verified')) ?></span>
      <span class="metabar__value">
        <?php if ($verified === '' || is_open($verified)): ?>
          <span class="openchip"><?= e(t('ui.pending')) ?></span>
        <?php else: ?>
          <time datetime="<?= e($verified) ?>"><?= e(fmt_date($verified)) ?></time>
        <?php endif; ?>
      </span>
    </p>
  </div>

  <?php if ($status !== ''): ?>
    <div class="wrap metabar__ladder" aria-label="<?= e(t('ui.stage')) ?>">
      <div class="ladder" role="img" aria-label="<?= e(t('ui.stage')) ?>: <?= e((string) $stage) ?>%">
        <span class="ladder__fill" style="--pct:<?= e((string) $stage) ?>%"></span>
      </div>
      <p class="ladder__caption">
        <strong><?= e((string) $stage) ?>%</strong> · <?= e(t('ui.stage_note')) ?>
      </p>
    </div>
  <?php endif; ?>
</div>
