<?php
declare(strict_types=1);

/**
 * Shared block: FORM
 * A contact form that is HONESTLY disabled until an endpoint is configured.
 * A static build has nowhere to post, so the form renders disabled with a
 * clear explanation instead of pretending to work.
 *
 * Configure data.endpoint (or W5D_FORM_ENDPOINT) to enable it.
 *
 * @var array  $block
 * @var array  $doc
 * @var string $lang
 */
$d = $block['data'] ?? [];
$endpoint = (string) ($d['endpoint'] ?? (getenv('W5D_FORM_ENDPOINT') ?: ''));
$reasons = (array) ($d['reasons'] ?? array_keys(type_options('contact_reason')));
$disabled = $endpoint === '';
$address = (string) ($d['address'] ?? '');
?>
<section class="block block--form" id="<?= e($block['id']) ?>">
  <div class="wrap">
    <?php if ($block['title'] !== ''): ?>
      <h2 class="block__title"><?= \W5D\Support\Markup::inline($block['title'], $lang) ?></h2>
    <?php endif; ?>

    <form class="form<?= $disabled ? ' form--disabled' : '' ?>"<?= $disabled ? ' data-disabled="true"' : ' action="' . e($endpoint) . '" method="post"' ?>>
      <?php if ($disabled): ?>
        <p class="form__disabled">
          <strong><?= e(t('form.disabled_title')) ?></strong>
          <?= e(t('form.disabled_note')) ?>
        </p>
      <?php endif; ?>

      <fieldset<?= $disabled ? ' disabled' : '' ?>>
        <label class="field">
          <span class="field__label"><?= e(t('form.name')) ?></span>
          <input class="input" type="text" name="name" required autocomplete="name">
        </label>

        <label class="field">
          <span class="field__label"><?= e(t('form.email')) ?></span>
          <input class="input" type="email" name="email" required autocomplete="email">
        </label>

        <label class="field">
          <span class="field__label"><?= e(t('form.organisation')) ?></span>
          <input class="input" type="text" name="organisation" autocomplete="organization">
        </label>

        <label class="field">
          <span class="field__label"><?= e(t('form.reason')) ?></span>
          <select class="input" name="reason">
            <?php foreach ($reasons as $key): ?>
              <option value="<?= e((string) $key) ?>"><?= e(type_label('contact_reason', (string) $key)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="field field--wide">
          <span class="field__label"><?= e(t('form.message')) ?></span>
          <textarea class="input" name="message" rows="6" required></textarea>
        </label>
      </fieldset>

      <p class="form__submit">
        <button class="button button--primary" type="submit"<?= $disabled ? ' disabled' : '' ?>>
          <?= e(t('form.send')) ?>
        </button>
      </p>
    </form>

    <?php if ($address !== '' && is_open($address)): ?>
      <p class="block__note"><span class="openchip"><?= e(t('ui.pending')) ?></span> <?= e(t('form.disabled_note')) ?></p>
    <?php endif; ?>
  </div>
</section>
