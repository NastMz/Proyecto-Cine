<?php
use Cine\Support\Format;

/** @var array<string, mixed> $kpis */
/** @var list<array<string, mixed>> $daily */
/** @var list<array<string, mixed>> $topMovies */
/** @var list<array<string, mixed>> $nextShowtimes */
$maxRevenue = max(1.0, ...array_map(static fn ($d) => (float) $d['revenue'], $daily));
$maxMovie = max(1.0, ...array_map(static fn ($m) => (float) $m['revenue'], $topMovies ?: [['revenue' => 1]]));
$chartH = 160;
$barW = 44;
$gap = 22;
$chartW = count($daily) * ($barW + $gap);
?>
<header class="page-head">
    <div>
        <p class="eyebrow">Resumen</p>
        <h1 class="heading">Taquilla de los últimos 7 días</h1>
    </div>
</header>

<section class="kpis">
    <article class="kpi">
        <span class="kpi__label">Ingresos (7 días)</span>
        <strong class="kpi__value"><?= e(Format::money($kpis['revenue_7d'])) ?></strong>
    </article>
    <article class="kpi">
        <span class="kpi__label">Boletos vendidos</span>
        <strong class="kpi__value"><?= e(number_format((int) $kpis['tickets_7d'], 0, ',', '.')) ?></strong>
    </article>
    <article class="kpi">
        <span class="kpi__label">Ocupación promedio</span>
        <strong class="kpi__value"><?= e($kpis['occupancy'] ?? '0') ?>%</strong>
    </article>
    <article class="kpi">
        <span class="kpi__label">Funciones programadas</span>
        <strong class="kpi__value"><?= e($kpis['upcoming']) ?></strong>
        <span class="kpi__hint"><?= e($kpis['customers']) ?> clientes registrados</span>
    </article>
</section>

<div class="dash-grid">
    <section class="panel">
        <h2 class="panel__title">Ingresos por día</h2>
        <svg class="chart" viewBox="0 0 <?= $chartW ?> <?= $chartH + 40 ?>" role="img" aria-label="Ingresos diarios de la última semana">
            <?php foreach ($daily as $i => $day): ?>
                <?php
                $h = max(2, (float) $day['revenue'] / $maxRevenue * $chartH);
                $x = $i * ($barW + $gap) + $gap / 2;
                ?>
                <g>
                    <title><?= e(Format::longDay($day['sale_date'] . ' 12:00')) ?>: <?= e(Format::money($day['revenue'])) ?></title>
                    <rect class="chart__bar" x="<?= $x ?>" y="<?= $chartH - $h ?>" width="<?= $barW ?>" height="<?= $h ?>" rx="6"/>
                    <text class="chart__label" x="<?= $x + $barW / 2 ?>" y="<?= $chartH + 22 ?>" text-anchor="middle"><?= e(mb_substr(Format::shortDay($day['sale_date'] . ' 12:00'), 0, 6)) ?></text>
                </g>
            <?php endforeach; ?>
        </svg>
    </section>

    <section class="panel">
        <h2 class="panel__title">Películas más taquilleras</h2>
        <ol class="bars">
            <?php foreach ($topMovies as $movie): ?>
                <li class="bars__item">
                    <span class="bars__name"><?= e($movie['title']) ?></span>
                    <span class="bars__track"><span class="bars__fill" style="width: <?= round((float) $movie['revenue'] / $maxMovie * 100, 1) ?>%"></span></span>
                    <span class="bars__value"><?= e(Format::money($movie['revenue'])) ?> · <?= e($movie['tickets_sold']) ?> boletos</span>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="panel panel--wide">
        <h2 class="panel__title">Próximas funciones</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Película</th><th>Sala</th><th>Inicio</th><th>Ocupación</th></tr></thead>
                <tbody>
                <?php foreach ($nextShowtimes as $st): ?>
                    <tr>
                        <td><?= e($st['movie_title']) ?></td>
                        <td><?= e($st['hall_name']) ?> · <?= e($st['format']) ?></td>
                        <td><?= e(Format::shortDay($st['starts_at'])) ?>, <?= e(Format::time($st['starts_at'])) ?></td>
                        <td>
                            <span class="meter"><span class="meter__fill" style="width: <?= (float) $st['occupancy_pct'] ?>%"></span></span>
                            <span class="small muted"><?= e($st['sold']) ?>/<?= e($st['capacity']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
