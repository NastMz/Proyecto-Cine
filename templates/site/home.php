<?php
use Cine\Support\Format;

/** @var list<array<string, mixed>> $movies */
/** @var array<string, mixed>|null $featured */
/** @var list<string> $genres */
/** @var string $genre */
?>
<?php if ($featured !== null): ?>
<section class="hero">
    <div class="container hero__inner">
        <a class="hero__poster" href="/peliculas/<?= e($featured['movie_id']) ?>">
            <img src="/afiches/<?= e($featured['movie_id']) ?>.svg" alt="Afiche de <?= e($featured['title']) ?>" width="400" height="600">
        </a>
        <div class="hero__copy">
            <p class="eyebrow">Estreno destacado</p>
            <h1 class="display hero__title"><?= e($featured['title']) ?></h1>
            <p class="meta">
                <span><?= e($featured['genre']) ?></span>
                <span><?= e(Format::duration((int) $featured['duration_min'])) ?></span>
                <span class="rating"><?= e($featured['rating']) ?></span>
            </p>
            <p class="lead">Próxima función: <strong><?= e(Format::dateTime($featured['next_showtime'])) ?></strong></p>
            <div class="hero__actions">
                <a class="btn btn--brass" href="/peliculas/<?= e($featured['movie_id']) ?>">Ver horarios y comprar</a>
                <a class="btn btn--ghost" href="#cartelera">Toda la cartelera</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="container section" id="cartelera">
    <div class="section__head">
        <div>
            <p class="eyebrow">En cartelera</p>
            <h2 class="heading">Esta semana en Butaca</h2>
        </div>
        <nav class="chips" aria-label="Filtrar por género">
            <a class="chip<?= $genre === '' ? ' chip--active' : '' ?>" href="/#cartelera">Todas</a>
            <?php foreach ($genres as $g): ?>
                <a class="chip<?= $genre === $g ? ' chip--active' : '' ?>" href="/?genero=<?= e(rawurlencode($g)) ?>#cartelera"><?= e($g) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>

    <?php if ($movies === []): ?>
        <p class="empty">No hay funciones programadas para este filtro. Vuelve pronto.</p>
    <?php else: ?>
        <ul class="movie-grid">
            <?php foreach ($movies as $movie): ?>
                <li class="movie-card">
                    <a href="/peliculas/<?= e($movie['movie_id']) ?>" class="movie-card__link">
                        <span class="movie-card__poster">
                            <img src="/afiches/<?= e($movie['movie_id']) ?>.svg" alt="Afiche de <?= e($movie['title']) ?>" loading="lazy" width="400" height="600">
                            <span class="rating rating--float"><?= e($movie['rating']) ?></span>
                        </span>
                        <span class="movie-card__body">
                            <span class="movie-card__title"><?= e($movie['title']) ?></span>
                            <span class="movie-card__meta"><?= e($movie['genre']) ?> · <?= e(Format::duration((int) $movie['duration_min'])) ?></span>
                            <span class="movie-card__next">Próxima: <?= e(Format::shortDay($movie['next_showtime'])) ?>, <?= e(Format::time($movie['next_showtime'])) ?></span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
