<?php
/**
 * form — server-rendered POST form with CSRF. data: action, submit{}, fields[{name,label{},type,required,options[{value,label{}}],help{}}]
 * Errors and values come from $block['errors'] and $block['values'] when the controller re-renders.
 */
$errors = $block['errors'] ?? [];
$values = $block['values'] ?? [];
?>
<form class="form" method="post" action="<?= e($block['data']['action'] ?? '') ?>" novalidate>
  <?= csrf_field() ?>
<?php foreach (($block['data']['fields'] ?? []) as $f):
    $name = (string) $f['name'];
    if (!empty($f['hidden'])): ?>
  <div class="hp" aria-hidden="true"><label>Leave this field empty<input type="text" name="<?= e($name) ?>" tabindex="-1" autocomplete="off"></label></div>
<?php continue; endif;
    $id = 'f-' . $block['id'] . '-' . $name;
    $req = !empty($f['required']);
    $val = (string) ($values[$name] ?? '');
    $err = $errors[$name] ?? null;
    $help = isset($f['help']) ? loc($f['help'], $lang) : '';
?>
  <div class="field<?= $err ? ' has-error' : '' ?>">
<?php if (($f['type'] ?? '') === 'checkbox'): ?>
    <label class="opt" for="<?= e($id) ?>"><input id="<?= e($id) ?>" type="checkbox" name="<?= e($name) ?>" value="yes"<?= $val === 'yes' ? ' checked' : '' ?><?= $req ? ' required' : '' ?>> <?= e(loc($f['label'] ?? '', $lang)) ?><?= $req ? ' <span class="req">*</span>' : '' ?></label>
<?php else: ?>
    <label for="<?= e($id) ?>"><?= e(loc($f['label'] ?? '', $lang)) ?><?= $req ? ' <span class="req">*</span>' : '' ?></label>
<?php endif; ?>
<?php if (($f['type'] ?? 'text') === 'checkbox'): ?>
<?php elseif (($f['type'] ?? 'text') === 'textarea'): ?>
    <textarea id="<?= e($id) ?>" name="<?= e($name) ?>" rows="5" maxlength="4000"<?= $req ? ' required' : '' ?>><?= e($val) ?></textarea>
<?php elseif (($f['type'] ?? '') === 'select'): ?>
    <select id="<?= e($id) ?>" name="<?= e($name) ?>"<?= $req ? ' required' : '' ?>>
<?php foreach (($f['options'] ?? []) as $o): ?>
      <option value="<?= e($o['value']) ?>"<?= $val === (string) $o['value'] ? ' selected' : '' ?>><?= e(loc($o['label'] ?? '', $lang)) ?></option>
<?php endforeach; ?>
    </select>
<?php else: ?>
    <?php $inputType = in_array($f['type'] ?? 'text', ['text', 'email', 'date'], true) ? ($f['type'] ?? 'text') : 'text'; ?>
    <input id="<?= e($id) ?>" type="<?= e($inputType) ?>" name="<?= e($name) ?>" value="<?= e($val) ?>" maxlength="<?= (int) ($f['max'] ?? 190) ?>"<?= $req ? ' required' : '' ?>>
<?php endif; ?>
<?php if ($help !== ''): ?><p class="help"><?= e($help) ?></p><?php endif; ?>
<?php if ($err): ?><p class="error" role="alert"><?= e($err) ?></p><?php endif; ?>
  </div>
<?php endforeach; ?>
  <p class="blk-actions"><button class="btn btn-peak" type="submit"><?= e(loc($block['data']['submit'] ?? ['en' => 'Send'], $lang)) ?></button></p>
</form>
