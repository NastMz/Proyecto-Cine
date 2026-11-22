<?php /** @var array{type: string, message: string}|null $flash */ ?>
<?php if (!empty($flash)): ?>
    <div class="container">
        <div class="flash flash--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
