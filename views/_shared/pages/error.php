<?php
/** Error page body. $status, $message (already safe text or null). */
$title = $status === 404 ? t('err404_title') : t('err_title');
$text = $status === 404 ? t('err404_body') : ($message ?? t('err_body'));
?>
<section class="blk tier-E focus-5" id="err-<?= (int) $status ?>">
  <div class="hero-inner">
    <p class="eyebrow"><?= (int) $status ?></p>
    <h1><?= e($title) ?></h1>
    <p class="lead"><?= e($text) ?></p>
    <p class="blk-actions"><a class="btn btn-peak" href="/"><?= e(t('back_home')) ?></a></p>
  </div>
</section>
