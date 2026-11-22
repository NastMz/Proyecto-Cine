<?php
/** @var string $next */
/** @var string $email */
/** @var string|null $error */
/** @var string $csrfToken */
?>
<section class="auth">
    <div class="auth__card">
        <p class="eyebrow">Bienvenido de nuevo</p>
        <h1 class="heading">Ingresar</h1>
        <?php if ($error !== null): ?>
            <div class="flash flash--error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/ingresar" class="form">
            <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <label class="field">
                <span class="field__label">Correo electrónico</span>
                <input class="input" type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
            </label>
            <label class="field">
                <span class="field__label">Contraseña</span>
                <input class="input" type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit" class="btn btn--brass btn--block">Ingresar</button>
        </form>
        <p class="auth__alt">¿No tienes cuenta? <a href="/registro">Regístrate</a></p>
    </div>
</section>
