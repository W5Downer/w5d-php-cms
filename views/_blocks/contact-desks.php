<?php
declare(strict_types=1);

/**
 * Shared block: CONTACT-DESKS
 * One row per desk. A desk whose address has not been supplied shows the shared
 * open chip rather than a fabricated address.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$desks = (array) ($d['desks'] ?? []);
$extra = (array) ($d['items'] ?? []);
?>
<section class="block block--desks" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>

    <dl class="desks">
      <?php foreach ($desks as $desk): ?>
        <?php
          $label = (string) ($desk['label'] ?? '');
          $address = $desk['address'] ?? '';
          $open = is_array($address) ? false : is_open($address);
        ?>
        <div class="desks__row">
          <dt><?= e($label) ?></dt>
          <dd>
            <?php if ($open): ?>
              <span class="openchip"><?= e(t('ui.pending')) ?></span>
            <?php elseif (is_array($address)): ?>
              <?= \W5D\Support\Markup::link(e((string) ($address['label'] ?? '')), (string) ($address['href'] ?? ''), $lang) ?>
            <?php else: ?>
              <a class="link" href="mailto:<?= e((string) $address) ?>"><?= e((string) $address) ?></a>
            <?php endif; ?>
          </dd>
        </div>
      <?php endforeach; ?>

      <?php foreach ($extra as $item): ?>
        <div class="desks__row">
          <dt><?= e((string) ($item['label'] ?? '')) ?></dt>
          <dd>
            <?php if (!empty($item['open']) || is_open($item['value'] ?? '')): ?>
              <span class="openchip"><?= e(t('ui.pending')) ?></span>
            <?php else: ?>
              <?= e((string) ($item['value'] ?? '')) ?>
            <?php endif; ?>
          </dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>
