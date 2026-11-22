<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var array|null $authUser */
/** @var \Cine\Auth\Auth $auth */
/** @var string $csrfToken */
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Butaca: cartelera, horarios y compra de boletos en línea.">
    <meta name="theme-color" content="#120b0d">
    <title><?= e($pageTitle ?? 'Cartelera') ?> · Butaca</title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Limelight&family=Work+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body class="site">
<a class="skip-link" href="#main">Saltar al contenido</a>
<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="/" aria-label="Butaca, ir a la cartelera">
            <img src="/assets/img/logo.svg" alt="" width="36" height="36">
            <span class="brand__name">Butaca</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
            <span class="sr-only">Menú</span><span class="nav-toggle__bar"></span>
        </button>
        <nav class="site-nav" id="site-nav" aria-label="Principal">
            <a href="/" class="site-nav__link">Cartelera</a>
            <?php if ($authUser !== null): ?>
                <a href="/mis-boletos" class="site-nav__link">Mis boletos</a>
                <?php if ($auth->can('admin.access')): ?>
                    <a href="/admin" class="site-nav__link">Panel</a>
                <?php endif; ?>
                <span class="site-nav__user">Hola, <?= e($authUser['first_name']) ?></span>
                <form method="post" action="/salir" class="inline-form">
                    <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
                    <button type="submit" class="btn btn--ghost btn--sm">Salir</button>
                </form>
            <?php else: ?>
                <a href="/ingresar" class="site-nav__link">Ingresar</a>
                <a href="/registro" class="btn btn--brass btn--sm">Crear cuenta</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="marquee-lights" aria-hidden="true"></div>
</header>

<main id="main" class="site-main">
    <?= $this->partial('partials/flash', ['flash' => $flash ?? null]) ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        <div>
            <span class="brand__name brand__name--small">Butaca</span>
            <p class="muted">Proyecto académico de Bases de Datos. Películas, personas y datos de demostración ficticios.</p>
        </div>
        <p class="muted small">Afiches generados por la aplicación · MIT 2022–2026</p>
    </div>
</footer>
</body>
</html>
