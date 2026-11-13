<?php
/** country-switch — portal header selector (GET). items[{iso, name}] */
?>
<form class="country-switch" method="get" action="">
  <label for="country-select"><?= e(t('portal_country')) ?></label>
  <select id="country-select" name="country">
    <option value=""><?= e(t('portal_all_countries')) ?></option>
<?php foreach (($block['data']['items'] ?? []) as $c): ?>
    <option value="<?= e($c['iso']) ?>"<?= !empty($c['selected']) ? ' selected' : '' ?>><?= e($c['name']) ?></option>
<?php endforeach; ?>
  </select>
  <button class="btn btn-ghost" type="submit"><?= e(t('portal_country')) ?></button>
</form>
