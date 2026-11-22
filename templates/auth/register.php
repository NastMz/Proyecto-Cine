<?php
/** @var array<string, mixed> $old */
/** @var array<string, string> $errors */
/** @var string $csrfToken */
$fields = [
    ['first_name', 'Nombres', 'text', 'given-name', ''],
    ['last_name', 'Apellidos', 'text', 'family-name', ''],
    ['document', 'Documento de identidad', 'text', 'off', 'inputmode="numeric" pattern="[0-9]{5,20}"'],
    ['phone', 'Teléfono (opcional)', 'tel', 'tel', ''],
    ['email', 'Correo electrónico', 'email', 'email', ''],
];
?>
<section class="auth">
    <div class="auth__card auth__card--wide">
        <p class="eyebrow">Únete a Butaca</p>
        <h1 class="heading">Crear cuenta</h1>
        <?php if (isset($errors['_form'])): ?>
            <div class="flash flash--error" role="alert"><?= e($errors['_form']) ?></div>
        <?php endif; ?>
        <form method="post" action="/registro" class="form form--grid" novalidate>
            <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
            <?php foreach ($fields as [$name, $label, $type, $autocomplete, $extra]): ?>
                <label class="field<?= $name === 'email' ? ' field--full' : '' ?>">
                    <span class="field__label"><?= e($label) ?></span>
                    <input class="input<?= isset($errors[$name]) ? ' input--invalid' : '' ?>" type="<?= e($type) ?>" name="<?= e($name) ?>"
                           value="<?= e($old[$name] ?? '') ?>" autocomplete="<?= e($autocomplete) ?>" <?= $extra ?> <?= $name === 'phone' ? '' : 'required' ?>>
                    <?php if (isset($errors[$name])): ?><span class="field__error"><?= e($errors[$name]) ?></span><?php endif; ?>
                </label>
            <?php endforeach; ?>
            <label class="field">
                <span class="field__label">Contraseña</span>
                <input class="input<?= isset($errors['password_hash']) ? ' input--invalid' : '' ?>" type="password" name="password_hash" autocomplete="new-password" minlength="8" required>
                <?php if (isset($errors['password_hash'])): ?><span class="field__error"><?= e($errors['password_hash']) ?></span><?php endif; ?>
            </label>
            <label class="field">
                <span class="field__label">Confirmar contraseña</span>
                <input class="input<?= isset($errors['password_confirmation']) ? ' input--invalid' : '' ?>" type="password" name="password_confirmation" autocomplete="new-password" required>
                <?php if (isset($errors['password_confirmation'])): ?><span class="field__error"><?= e($errors['password_confirmation']) ?></span><?php endif; ?>
            </label>
            <p class="note small field--full">Los descuentos de estudiante y adulto mayor se activan en taquilla al verificar tu documento.</p>
            <button type="submit" class="btn btn--brass btn--block field--full">Crear cuenta</button>
        </form>
        <p class="auth__alt">¿Ya tienes cuenta? <a href="/ingresar">Ingresa</a></p>
    </div>
</section>
