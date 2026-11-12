<?php
declare(strict_types=1);

/**
 * Shared block: REGISTER
 * The status register, generated from data/content/projects/*.json.
 * Never typed in a page file: adding a project JSON row adds a register row.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$rows = (array) ($doc['register'] ?? []);
$showLegend = (bool) ($d['legend'] ?? true);
$vars = \W5D\Registry::vars();
?>
<section class="block block--register" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <?php if ($rows !== []): ?>
      <div class="tablewrap">
        <table class="table table--register">
          <caption><?= e(t('ui.status_words')) ?>: <?= e((string) v('counts.status_words')) ?> · <?= e(t('ui.evidence_scale')) ?>: <?= e((string) v('counts.evidence_levels')) ?></caption>
          <thead>
            <tr>
              <th scope="col"><?= e(t('ui.name')) ?></th>
              <th scope="col"><?= e(t('ui.layer')) ?></th>
              <th scope="col"><?= e(t('ui.status')) ?></th>
              <th scope="col"><?= e(t('ui.evidence')) ?></th>
              <th scope="col"><?= e(t('ui.last_verified')) ?></th>
              <th scope="col"><?= e(t('ui.what_exists')) ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <th scope="row">
                  <a href="<?= e((string) $row['href']) ?>"><?= e((string) $row['name']) ?></a>
                  <?php if (!empty($row['subtitle'])): ?><small><?= e((string) $row['subtitle']) ?></small><?php endif; ?>
                </th>
                <td><?= e(type_label('layer', (string) $row['layer'])) ?></td>
                <td><span class="chip <?= status_class((string) $row['status']) ?>"><?= e(type_label('status', (string) $row['status'])) ?></span></td>
                <td><span class="badge badge--evidence"><?= e((string) $row['evidence']) ?></span></td>
                <td><?= e(fmt_date((string) $row['verified'], true)) ?></td>
                <td><?= \W5D\Support\Markup::inline((string) $row['exists'], $lang) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if ($showLegend): ?>
      <div class="legend">
        <section class="legend__col">
          <h3 class="legend__title"><?= e(t('ui.status_words')) ?></h3>
          <dl class="legend__list">
            <?php foreach ($vars->options('status') as $key => $label): ?>
              <div>
                <dt><span class="chip <?= status_class((string) $key) ?>"><?= e(type_label('status', (string) $key)) ?></span></dt>
                <dd><?= e((string) ($vars->raw('types.status.description.' . $key) ?? '')) ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </section>
        <section class="legend__col">
          <h3 class="legend__title"><?= e(t('ui.evidence_scale')) ?></h3>
          <dl class="legend__list">
            <?php foreach ($vars->options('evidence') as $key => $label): ?>
              <div>
                <dt><span class="badge badge--evidence"><?= e((string) $key) ?></span> <?= e(type_label('evidence', (string) $key)) ?></dt>
                <dd><?= e((string) ($vars->raw('types.evidence.description.' . $key) ?? '')) ?></dd>
              </div>
            <?php endforeach; ?>
          </dl>
        </section>
      </div>
    <?php endif; ?>

    <?php if (!empty($d['note'])): ?>
      <p class="block__note"><?= \W5D\Support\Markup::inline((string) $d['note'], $lang) ?></p>
    <?php endif; ?>
  </div>
</section>
