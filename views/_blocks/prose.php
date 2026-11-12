<?php
/** prose — paragraphs with inline markup. data: paragraphs[string|{en,fr,ar}] */
use W5D\Markup;
foreach (($block['data']['paragraphs'] ?? []) as $p):
    $txt = loc($p, $lang);
    if ($txt === '') { continue; }
?>
<p><?= Markup::inline($txt) ?></p>
<?php endforeach; ?>
