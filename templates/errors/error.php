<?php
/** @var int $status */
/** @var string $title */
/** @var string $message */
?>
<section class="container error-page">
    <p class="error-page__code"><?= e($status) ?></p>
    <h1 class="display"><?= e($title) ?></h1>
    <p class="lead"><?= e($message) ?></p>
    <a class="btn btn--brass" href="/">Volver a la cartelera</a>
</section>
