<?php
use Cine\Support\Format;

/** @var list<array<string, mixed>> $purchases */
$upcoming = array_filter($purchases, static fn (array $p): bool => (bool) $p['upcoming']);
// Most recent past purchases only; the full history lives in the admin panel.
$past = array_slice(array_values(array_filter($purchases, static fn (array $p): bool => !$p['upcoming'])), 0, 6);
?>
<section class="container section narrow">
    <p class="eyebrow">Tu cuenta</p>
    <h1 class="heading">Mis boletos</h1>

    <?php if ($purchases === []): ?>
        <p class="empty">Aún no tienes compras. <a href="/">Explora la cartelera</a>.</p>
    <?php endif; ?>

    <?php if ($upcoming !== []): ?>
        <h2 class="subheading">Próximas funciones</h2>
        <div class="ticket-list">
            <?php foreach ($upcoming as $purchase): ?>
                <a class="ticket-link" href="/compras/<?= e($purchase['purchase_id']) ?>"><?= $this->partial('partials/ticket', ['p' => $purchase]) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($past !== []): ?>
        <h2 class="subheading">Historial reciente</h2>
        <div class="ticket-list">
            <?php foreach ($past as $purchase): ?>
                <?= $this->partial('partials/ticket', ['p' => $purchase]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
