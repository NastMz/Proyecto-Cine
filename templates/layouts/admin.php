<?php
/** @var string $content */
/** @var string $pageTitle */
/** @var array<string, list<\Cine\Admin\Resource>> $nav */
/** @var array $authUser */
/** @var \Cine\Auth\Auth $auth */
/** @var string $csrfToken */
/** @var string $currentPath */
$current = $currentPath;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#120b0d">
    <title><?= e($pageTitle) ?> · Panel Butaca</title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Limelight&family=Work+Sans:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body class="admin">
<a class="skip-link" href="#main">Saltar al contenido</a>
<aside class="admin-sidebar" id="admin-nav">
    <a class="brand" href="/admin">
        <img src="/assets/img/logo.svg" alt="" width="32" height="32">
        <span class="brand__name">Butaca</span>
        <span class="brand__tag">Panel</span>
    </a>
    <nav aria-label="Módulos">
        <a href="/admin" class="admin-nav__link<?= $current === '/admin' ? ' is-active' : '' ?>">Resumen</a>
        <?php foreach ($nav as $group => $resources): ?>
            <?php $visible = array_filter($resources, static fn ($r) => $auth->can($r->permission)); ?>
            <?php if ($visible === []) { continue; } ?>
            <p class="admin-nav__group"><?= e($group) ?></p>
            <?php foreach ($visible as $resource): ?>
                <?php $href = '/admin/' . $resource->slug; ?>
                <a href="<?= e($href) ?>" class="admin-nav__link<?= str_starts_with($current . '/', $href . '/') ? ' is-active' : '' ?>"><?= e($resource->title) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>
<div class="admin-shell">
    <header class="admin-topbar">
        <button class="nav-toggle nav-toggle--admin" type="button" aria-expanded="false" aria-controls="admin-nav" data-nav-toggle>
            <span class="sr-only">Menú</span><span class="nav-toggle__bar"></span>
        </button>
        <a href="/" class="admin-topbar__link">Ver cartelera ↗</a>
        <span class="admin-topbar__user"><?= e($authUser['full_name']) ?> · <span class="muted"><?= e($authUser['role_name']) ?></span></span>
        <form method="post" action="/salir" class="inline-form">
            <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
            <button type="submit" class="btn btn--ghost btn--sm">Salir</button>
        </form>
    </header>
    <main id="main" class="admin-main">
        <?= $this->partial('partials/flash', ['flash' => $flash ?? null]) ?>
        <?= $content ?>
    </main>
</div>
</body>
</html>
