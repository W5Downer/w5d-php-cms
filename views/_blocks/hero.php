<?php
/** hero — page opener. data: eyebrow{}, lead{}, meta[{label{},value{}}]. Title comes from $block['title']. */
use W5D\Markup;
$d = $block['data'];
$eyebrow = isset($d['eyebrow']) ? loc($d['eyebrow'], $lang) : '';
$lead = isset($d['lead']) ? loc($d['lead'], $lang) : '';
?>
<div class="hero-inner">
  <?php if ($eyebrow !== ''): ?><p class="eyebrow"><?= e($eyebrow) ?></p><?php endif; ?>
  <h1><?= e(loc($block['title'] ?? '', $lang)) ?></h1>
  <?php if ($lead !== ''): ?><p class="lead"><?= Markup::inline($lead) ?></p><?php endif; ?>
  <?php if (!empty($d['meta'])): ?>
    <ul class="hero-meta">
      <?php foreach ($d['meta'] as $m): ?>
        <li><span class="k"><?= e(loc($m['label'] ?? '', $lang)) ?></span>
            <span class="v"><?= e(loc($m['value'] ?? ($m['chip'] ?? ''), $lang)) ?></span></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
