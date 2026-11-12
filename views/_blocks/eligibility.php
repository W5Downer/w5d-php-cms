<?php
/** eligibility — three-question check with no JavaScript. GET form to the registration page. */
?>
<form class="elig" method="get" action="/app/register/">
  <input type="hidden" name="service" value="<?= e($block['data']['service'] ?? '') ?>">
  <fieldset>
    <legend><?= e(t('elig_q1')) ?></legend>
    <label><input type="radio" name="degree" value="licence" required> <?= e(t('elig_degree_licence')) ?></label>
    <label><input type="radio" name="degree" value="maitrise"> <?= e(t('elig_degree_maitrise')) ?></label>
    <label><input type="radio" name="degree" value="ingenieur"> <?= e(t('elig_degree_ingenieur')) ?></label>
    <label><input type="radio" name="degree" value="none"> <?= e(t('elig_degree_none')) ?></label>
  </fieldset>
  <fieldset>
    <legend><?= e(t('elig_q2')) ?></legend>
    <label><input type="radio" name="fit" value="yes" required> <?= e(t('yes')) ?></label>
    <label><input type="radio" name="fit" value="no"> <?= e(t('no')) ?></label>
  </fieldset>
  <fieldset>
    <legend><?= e(t('elig_q3')) ?></legend>
    <label><input type="radio" name="available" value="yes" required> <?= e(t('yes')) ?></label>
    <label><input type="radio" name="available" value="no"> <?= e(t('no')) ?></label>
  </fieldset>
  <p class="blk-actions"><button class="btn btn-focus" type="submit"><?= e(t('elig_submit')) ?></button></p>
</form>
