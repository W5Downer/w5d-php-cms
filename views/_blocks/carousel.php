<?php
declare(strict_types=1);

/**
 * Shared block: CAROUSEL
 * A dependency-free, accessible carousel. No jQuery, no Owl Carousel: the whole
 * thing is ~60 lines of CSS scroll-snap plus a tiny script, so the site keeps
 * its "no third-party requests" promise.
 *
 * Steps are ordinary slides: title, text, optional figure, chip, href.
 * The first slide is rendered visible; the rest are in the same scroll track,
 * so the content is in the HTML even with scripts disabled.
 *
 * data.slides = [{"title":"…","text":"…","figure":"…","chip":"building","href":"project:kernid"}]
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$slides = (array) ($d['slides'] ?? []);
$label = (string) ($d['label'] ?? ($block['title'] !== '' ? $block['title'] : 'Carousel'));
?>
<section class="block block--carousel" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>

    <div class="carousel" role="group" aria-roledescription="carousel" aria-label="<?= e($label) ?>" data-carousel>
      <ul class="carousel__track" data-carousel-track tabindex="0">
        <?php foreach ($slides as $i => $slide): ?>
          <?php $chip = (string) ($slide['chip'] ?? ''); ?>
          <li class="carousel__slide" data-carousel-slide aria-roledescription="slide" aria-label="<?= e(($i + 1) . ' / ' . count((array) $slides)) ?>">
            <div class="carousel__card">
              <?php if (!empty($slide['figure'])): ?>
                <p class="carousel__figure"><?= e((string) $slide['figure']) ?></p>
              <?php endif; ?>
              <h3 class="carousel__title">
                <?php if (!empty($slide['href'])): ?>
                  <?= \W5D\Support\Markup::link(e((string) ($slide['title'] ?? '')), (string) $slide['href'], $lang) ?>
                <?php else: ?>
                  <?= e((string) ($slide['title'] ?? '')) ?>
                <?php endif; ?>
                <?php if ($chip !== ''): ?>
                  <span class="chip <?= status_class($chip) ?>"><?= e(type_label('status', $chip)) ?></span>
                <?php endif; ?>
              </h3>
              <?php if (!empty($slide['text'])): ?>
                <p class="carousel__text"><?= \W5D\Support\Markup::inline((string) $slide['text'], $lang) ?></p>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if (count((array) $slides) > 1): ?>
        <div class="carousel__controls">
          <button class="carousel__btn" type="button" data-carousel-prev aria-label="<?= e(t('ui.previous')) ?>">‹</button>
          <span class="carousel__status" data-carousel-status aria-live="polite">1 / <?= e((string) count((array) $slides)) ?></span>
          <button class="carousel__btn" type="button" data-carousel-next aria-label="<?= e(t('ui.next')) ?>">›</button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
