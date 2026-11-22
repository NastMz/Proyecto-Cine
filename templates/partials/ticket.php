<?php
use Cine\Support\Format;

/** @var array<string, mixed> $p purchase row from v_purchase_detail */
$past = isset($p['upcoming']) && !$p['upcoming'];
?>
<article class="ticket<?= $past ? ' ticket--past' : '' ?>">
    <div class="ticket__main">
        <img class="ticket__poster" src="/afiches/<?= e($p['movie_id']) ?>.svg" alt="" width="80" height="120">
        <div>
            <p class="ticket__brand">Butaca · Admite <?= e($p['seat_count']) ?></p>
            <h3 class="ticket__title"><?= e($p['movie_title']) ?></h3>
            <p class="ticket__when"><?= e(ucfirst(Format::longDay($p['starts_at']))) ?> · <?= e(Format::time($p['starts_at'])) ?></p>
            <p class="ticket__where"><?= e($p['hall_name']) ?> · <?= e($p['format']) ?> · <?= e($p['language']) ?></p>
        </div>
    </div>
    <div class="ticket__stub">
        <span class="ticket__label">Sillas</span>
        <span class="ticket__seats"><?= e($p['seat_labels']) ?></span>
        <span class="ticket__label">Código</span>
        <code class="ticket__code"><?= e(strtoupper(substr((string) $p['reference'], 0, 8))) ?></code>
    </div>
</article>
