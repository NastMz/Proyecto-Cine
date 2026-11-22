<?php
use Cine\Support\Format;

/** @var array<string, mixed> $showtime */
/** @var array<string, list<array<string, mixed>>> $rows */
/** @var bool $isOpen */
/** @var float|null $unitPrice */
/** @var list<array{payment_method_id: int, name: string}> $paymentMethods */
/** @var int $maxSeats */
/** @var bool $canBuy */
/** @var array|null $authUser */
/** @var string $csrfToken */
$columns = max(array_map('count', $rows ?: [[]]));
?>
<section class="container section booking">
    <a class="back-link" href="/peliculas/<?= e($showtime['movie_id']) ?>">← <?= e($showtime['movie_title']) ?></a>
    <header class="booking__head">
        <div>
            <p class="eyebrow"><?= e($showtime['hall_name']) ?> · <?= e($showtime['format']) ?> · <?= e($showtime['language']) ?></p>
            <h1 class="heading"><?= e(ucfirst(Format::longDay($showtime['starts_at']))) ?>, <?= e(Format::time($showtime['starts_at'])) ?></h1>
        </div>
        <p class="booking__availability"><strong><?= e($showtime['available']) ?></strong> de <?= e($showtime['capacity']) ?> sillas libres</p>
    </header>

    <form method="post" action="/funciones/<?= e($showtime['showtime_id']) ?>/comprar" class="booking__layout" data-seat-picker data-max="<?= e($maxSeats) ?>" data-price="<?= e($unitPrice ?? 0) ?>">
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
        <div class="theater">
            <div class="screen" aria-hidden="true"><span>Pantalla</span></div>
            <fieldset class="seat-map" style="--cols: <?= (int) $columns ?>" <?= $isOpen ? '' : 'disabled' ?>>
                <legend class="sr-only">Mapa de sillas</legend>
                <?php foreach ($rows as $label => $seats): ?>
                    <div class="seat-row">
                        <span class="seat-row__label" aria-hidden="true"><?= e($label) ?></span>
                        <?php foreach ($seats as $seat): ?>
                            <?php $code = $seat['row_label'] . $seat['seat_number']; ?>
                            <label class="seat seat--<?= e($seat['seat_kind']) ?><?= $seat['sold'] ? ' seat--sold' : '' ?>" title="Silla <?= e($code) ?>">
                                <input type="checkbox" name="seats[]" value="<?= e($seat['seat_id']) ?>" data-label="<?= e($code) ?>"<?= $seat['sold'] ? ' disabled' : '' ?>>
                                <span class="seat__shape" aria-hidden="true"></span>
                                <span class="sr-only">Silla <?= e($code) ?><?= $seat['sold'] ? ' (vendida)' : '' ?></span>
                            </label>
                        <?php endforeach; ?>
                        <span class="seat-row__label" aria-hidden="true"><?= e($label) ?></span>
                    </div>
                <?php endforeach; ?>
            </fieldset>
            <ul class="legend">
                <li><span class="seat__shape"></span> Disponible</li>
                <li><span class="seat__shape seat__shape--selected"></span> Tu selección</li>
                <li><span class="seat__shape seat__shape--sold"></span> Vendida</li>
                <li><span class="seat__shape seat__shape--preferential"></span> Preferencial</li>
                <li><span class="seat__shape seat__shape--accessible"></span> Accesible</li>
            </ul>
        </div>

        <aside class="summary">
            <h2 class="summary__title"><?= e($showtime['movie_title']) ?></h2>
            <dl class="summary__list">
                <div><dt>Valor por silla</dt><dd><?= e(Format::money($unitPrice)) ?></dd></div>
                <div><dt>Sillas</dt><dd data-selected-list>Ninguna</dd></div>
                <div class="summary__total"><dt>Total</dt><dd data-total>$ 0</dd></div>
            </dl>
            <?php if ($authUser !== null && (float) ($authUser['discount_pct'] ?? 0) > 0): ?>
                <p class="note">Incluye tu descuento <?= e($authUser['user_type_name']) ?> (<?= e(rtrim(rtrim((string) $authUser['discount_pct'], '0'), '.')) ?>%).</p>
            <?php endif; ?>

            <?php if (!$isOpen): ?>
                <p class="note note--warn">Esta función ya comenzó y no admite ventas.</p>
            <?php elseif ($authUser === null): ?>
                <p class="note">Ingresa a tu cuenta para comprar. Tu selección no se guarda.</p>
                <a class="btn btn--brass btn--block" href="/ingresar?next=<?= e(rawurlencode('/funciones/' . $showtime['showtime_id'])) ?>">Ingresar para comprar</a>
            <?php elseif (!$canBuy): ?>
                <p class="note note--warn">Tu rol no permite comprar boletos.</p>
            <?php else: ?>
                <label class="field">
                    <span class="field__label">Medio de pago</span>
                    <select name="payment_method_id" class="input" required>
                        <?php foreach ($paymentMethods as $method): ?>
                            <option value="<?= e($method['payment_method_id']) ?>"><?= e($method['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <p class="note small">Pago simulado: no se solicitan datos de tarjeta.</p>
                <button type="submit" class="btn btn--brass btn--block" data-buy>Confirmar compra</button>
                <p class="note small">Máximo <?= e($maxSeats) ?> sillas por compra.</p>
            <?php endif; ?>
        </aside>
    </form>
</section>
