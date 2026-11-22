<?php
use Cine\Support\Format;

/** @var array<string, mixed> $movie */
/** @var list<string> $directors */
/** @var list<array{full_name: string, character_name: string}> $cast */
/** @var array<string, list<array<string, mixed>>> $showtimesByDay */
?>
<section class="movie-hero" style="--hue: <?= (int) $movie['poster_hue'] ?>">
    <div class="container movie-hero__inner">
        <img class="movie-hero__poster" src="/afiches/<?= e($movie['movie_id']) ?>.svg" alt="Afiche de <?= e($movie['title']) ?>" width="400" height="600">
        <div class="movie-hero__copy">
            <a class="back-link" href="/">← Cartelera</a>
            <h1 class="display"><?= e($movie['title']) ?></h1>
            <?php if (!empty($movie['original_title'])): ?>
                <p class="muted"><em><?= e($movie['original_title']) ?></em></p>
            <?php endif; ?>
            <p class="meta">
                <span><?= e($movie['genre']) ?></span>
                <span><?= e(Format::duration((int) $movie['duration_min'])) ?></span>
                <span class="rating"><?= e($movie['rating']) ?></span>
                <span>Estreno <?= e(Format::longDay($movie['release_date'])) ?></span>
            </p>
            <p class="lead"><?= e($movie['synopsis']) ?></p>
            <dl class="credits">
                <div>
                    <dt>Dirección</dt>
                    <dd><?= e(implode(', ', $directors) ?: 'Por confirmar') ?></dd>
                </div>
                <div>
                    <dt>Reparto</dt>
                    <dd>
                        <?php if ($cast === []): ?>Por confirmar<?php endif; ?>
                        <ul class="cast-list">
                            <?php foreach ($cast as $member): ?>
                                <li><strong><?= e($member['full_name']) ?></strong> <span class="muted">como <?= e($member['character_name']) ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<section class="container section" id="horarios">
    <p class="eyebrow">Horarios</p>
    <h2 class="heading">Elige tu función</h2>
    <?php if ($showtimesByDay === []): ?>
        <p class="empty">No hay funciones programadas para esta película.</p>
    <?php endif; ?>
    <div class="schedule">
        <?php foreach ($showtimesByDay as $day => $showtimes): ?>
            <div class="schedule__day">
                <h3 class="schedule__date"><?= e(ucfirst(Format::longDay($day . ' 12:00'))) ?></h3>
                <ul class="slots">
                    <?php foreach ($showtimes as $st): ?>
                        <?php $soldOut = (int) $st['available'] === 0; ?>
                        <li>
                            <a class="slot<?= $soldOut ? ' slot--soldout' : '' ?>" href="/funciones/<?= e($st['showtime_id']) ?>"<?= $soldOut ? ' aria-disabled="true"' : '' ?>>
                                <span class="slot__time"><?= e(Format::time($st['starts_at'])) ?></span>
                                <span class="slot__info"><?= e($st['hall_name']) ?> · <?= e($st['format']) ?> · <?= e($st['language']) ?></span>
                                <span class="slot__seats"><?= $soldOut ? 'Agotada' : e($st['available']) . ' sillas libres' ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>
