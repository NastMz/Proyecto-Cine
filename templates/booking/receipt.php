<?php
use Cine\Support\Format;

/** @var array<string, mixed> $purchase */
?>
<section class="container section narrow">
    <p class="eyebrow">Compra #<?= e($purchase['purchase_id']) ?></p>
    <h1 class="heading">Tus boletos están listos</h1>
    <p class="lead">Presenta el código en la entrada de la sala. También los encuentras en <a href="/mis-boletos">Mis boletos</a>.</p>
    <?= $this->partial('partials/ticket', ['p' => $purchase]) ?>
    <dl class="receipt">
        <div><dt>Cliente</dt><dd><?= e($purchase['customer']) ?></dd></div>
        <div><dt>Medio de pago</dt><dd><?= e($purchase['payment_method']) ?></dd></div>
        <div><dt>Fecha de compra</dt><dd><?= e(Format::dateTime($purchase['purchased_at'])) ?></dd></div>
        <div><dt>Total pagado</dt><dd><strong><?= e(Format::money($purchase['total'])) ?></strong></dd></div>
    </dl>
</section>
