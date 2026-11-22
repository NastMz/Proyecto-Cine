<?php
use Cine\Admin\Field;

/** @var \Cine\Admin\Resource $resource */
/** @var string|null $key */
/** @var array<string, mixed> $values */
/** @var array<string, string> $errors */
/** @var array<string, array<string|int, string>> $options */
/** @var string $csrfToken */
$base = '/admin/' . $resource->slug;
$action = $key === null ? $base : $base . '/' . $key;
$valueOf = static function (Field $field) use ($values): string {
    $value = $values[$field->name] ?? '';
    if ($field->type === Field::DATETIME && is_string($value) && $value !== '') {
        return substr(str_replace(' ', 'T', $value), 0, 16);
    }

    return is_scalar($value) ? (string) $value : '';
};
?>
<header class="page-head">
    <div>
        <a class="back-link" href="<?= e($base) ?>">← <?= e($resource->title) ?></a>
        <h1 class="heading"><?= $key === null ? 'Agregar ' . e($resource->singular) : 'Editar ' . e($resource->singular) ?></h1>
        <?php if ($resource->description !== ''): ?><p class="muted"><?= e($resource->description) ?></p><?php endif; ?>
    </div>
</header>

<section class="panel panel--form">
    <?php if (isset($errors['_form'])): ?>
        <div class="flash flash--error" role="alert"><?= e($errors['_form']) ?></div>
    <?php elseif ($errors !== []): ?>
        <div class="flash flash--error" role="alert">Revisa los campos marcados.</div>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>" class="form form--grid" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <?php foreach ($resource->fields as $field): ?>
            <?php
            $id = 'f-' . $field->name;
            $error = $errors[$field->name] ?? null;
            $value = $valueOf($field);
            $disabled = $key !== null && !$field->editable;
            $wide = in_array($field->type, [Field::TEXTAREA], true);
            $required = $field->required && !($field->type === Field::PASSWORD && $key !== null);
            ?>
            <div class="field<?= $wide ? ' field--full' : '' ?><?= $field->type === Field::BOOL ? ' field--check' : '' ?>">
                <?php if ($field->type === Field::BOOL): ?>
                    <label class="check">
                        <input type="hidden" name="<?= e($field->name) ?>" value="0">
                        <input type="checkbox" id="<?= e($id) ?>" name="<?= e($field->name) ?>" value="1"<?= in_array($value, ['1', 'true'], true) ? ' checked' : '' ?>>
                        <span><?= e($field->label) ?></span>
                    </label>
                <?php else: ?>
                    <label class="field__label" for="<?= e($id) ?>"><?= e($field->label) ?><?= $required ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></label>
                    <?php if ($field->type === Field::TEXTAREA): ?>
                        <textarea class="input<?= $error ? ' input--invalid' : '' ?>" id="<?= e($id) ?>" name="<?= e($field->name) ?>" rows="5"<?= $required ? ' required' : '' ?>><?= e($value) ?></textarea>
                    <?php elseif ($field->type === Field::SELECT): ?>
                        <?php $choices = $field->choices ?? $options[$field->name] ?? []; ?>
                        <select class="input<?= $error ? ' input--invalid' : '' ?>" id="<?= e($id) ?>" name="<?= e($field->name) ?>"<?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?>>
                            <option value="">Selecciona…</option>
                            <?php foreach ($choices as $optionValue => $optionLabel): ?>
                                <option value="<?= e($optionValue) ?>"<?= (string) $optionValue === $value ? ' selected' : '' ?>><?= e($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <?php
                        $type = match ($field->type) {
                            Field::EMAIL => 'email',
                            Field::INT, Field::DECIMAL => 'number',
                            Field::DATE => 'date',
                            Field::DATETIME => 'datetime-local',
                            Field::PASSWORD => 'password',
                            default => 'text',
                        };
                        ?>
                        <input class="input<?= $error ? ' input--invalid' : '' ?>" type="<?= e($type) ?>" id="<?= e($id) ?>" name="<?= e($field->name) ?>"
                               value="<?= $field->type === Field::PASSWORD ? '' : e($value) ?>"
                               <?= $field->maxLength !== null ? 'maxlength="' . e($field->maxLength) . '"' : '' ?>
                               <?= $field->min !== null ? 'min="' . e($field->min) . '"' : '' ?>
                               <?= $field->max !== null ? 'max="' . e($field->max) . '"' : '' ?>
                               <?= $field->type === Field::DECIMAL ? 'step="any"' : '' ?>
                               <?= $field->type === Field::PASSWORD ? 'autocomplete="new-password"' : '' ?>
                               <?= $required ? 'required' : '' ?>>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($error !== null): ?>
                    <span class="field__error"><?= e($error) ?></span>
                <?php elseif ($field->help !== null): ?>
                    <span class="field__help"><?= e($field->help) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="form__actions field--full">
            <a class="btn btn--ghost" href="<?= e($base) ?>">Cancelar</a>
            <button type="submit" class="btn btn--brass">Guardar</button>
        </div>
    </form>
</section>
