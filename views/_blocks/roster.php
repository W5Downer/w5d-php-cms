<?php
declare(strict_types=1);

/**
 * Shared block: ROSTER
 * Named people with a role. Nothing is invented: an unsupplied person field
 * renders as the open chip.
 *
 * data.items = [{"name":"Maher","role":"Founder","note":"…","href":"page:maher"}]
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$items = (array) ($d['items'] ?? []);
?>
<section class="block block--roster" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>
    <?php if (!empty($d['lead'])): ?>
      <p class="block__lead"><?= \W5D\Support\Markup::inline((string) $d['lead'], $lang) ?></p>
    <?php endif; ?>

    <ul class="roster">
      <?php foreach ($items as $item): ?>
        <?php
          $name = (string) ($item['name'] ?? '');
          $role = (string) ($item['role'] ?? '');
          $note = (string) ($item['note'] ?? '');
          $hrefTarget = (string) ($item['href'] ?? '');
          $open = !empty($item['open']) || is_open($name);
        ?>
        <li class="roster__item">
          <p class="roster__name">
            <?php if ($open): ?>
              <span class="openchip"><?= e(t('ui.pending')) ?></span>
            <?php elseif ($hrefTarget !== ''): ?>
              <?= \W5D\Support\Markup::link(e($name), $hrefTarget, $lang) ?>
            <?php else: ?>
              <?= e($name) ?>
            <?php endif; ?>
          </p>
          <?php if ($role !== ''): ?><p class="roster__role"><?= \W5D\Support\Markup::inline($role, $lang) ?></p><?php endif; ?>
          <?php if ($note !== ''): ?><p class="roster__note"><?= \W5D\Support\Markup::inline($note, $lang) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
