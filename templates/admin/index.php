<?php
use Cine\Support\Format;

/** @var \Cine\Admin\Resource $resource */
/** @var array{rows: list<array<string, mixed>>, total: int, page: int, pages: int, sort: string, dir: string} $result */
/** @var string $search */
/** @var string $csrfToken */
$base = '/admin/' . $resource->slug;
$query = static function (array $changes) use ($search, $result): string {
    $params = array_filter([
        'q' => $search,
        'orden' => $result['sort'],
        'dir' => $result['dir'],
        'pagina' => $result['page'] > 1 ? $result['page'] : null,
    ] + [], static fn ($v) => $v !== null && $v !== '');
    $params = array_filter(array_merge($params, $changes), static fn ($v) => $v !== null && $v !== '');

    return $params === [] ? '' : '?' . http_build_query($params);
};
$format = static function (mixed $value, string $kind): string {
    if ($value === null || $value === '') {
        return '—';
    }

    return match ($kind) {
        'money' => Format::money($value),
        'date' => Format::shortDay($value . ' 12:00') . ' ' . substr((string) $value, 0, 4),
        'datetime' => Format::shortDay($value) . ', ' . Format::time($value),
        'bool' => $value ? 'Sí' : 'No',
        'minutes' => Format::duration((int) $value),
        'percent' => rtrim(rtrim((string) $value, '0'), '.') . ' %',
        'seat_kind' => ['standard' => 'Estándar', 'preferential' => 'Preferencial', 'accessible' => 'Accesible'][$value] ?? (string) $value,
        default => (string) $value,
    };
};
?>
<header class="page-head">
    <div>
        <p class="eyebrow"><?= e($resource->group) ?></p>
        <h1 class="heading"><?= e($resource->title) ?></h1>
        <?php if ($resource->description !== ''): ?>
            <p class="muted"><?= e($resource->description) ?></p>
        <?php endif; ?>
    </div>
    <?php if (!$resource->isReadOnly()): ?>
        <a class="btn btn--brass" href="<?= e($base) ?>/nuevo">+ Agregar <?= e($resource->singular) ?></a>
    <?php endif; ?>
</header>

<section class="panel">
    <form class="toolbar" method="get" action="<?= e($base) ?>" role="search">
        <label class="sr-only" for="q">Buscar</label>
        <input class="input" type="search" id="q" name="q" value="<?= e($search) ?>" placeholder="Buscar en <?= e(mb_strtolower($resource->title)) ?>…">
        <button class="btn btn--ghost" type="submit">Buscar</button>
        <span class="toolbar__count"><?= e($result['total']) ?> registro(s)</span>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <?php foreach ($resource->columns as $column => $meta): ?>
                    <?php
                    $active = $result['sort'] === $column;
                    $nextDir = $active && $result['dir'] === 'asc' ? 'desc' : 'asc';
                    ?>
                    <th scope="col"<?= $active ? ' aria-sort="' . ($result['dir'] === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>>
                        <a class="sort<?= $active ? ' sort--' . e($result['dir']) : '' ?>" href="<?= e($base . $query(['orden' => $column, 'dir' => $nextDir, 'pagina' => null])) ?>"><?= e($meta[0]) ?></a>
                    </th>
                <?php endforeach; ?>
                <?php if (!$resource->isReadOnly()): ?><th scope="col" class="table__actions">Acciones</th><?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php if ($result['rows'] === []): ?>
                <tr><td colspan="<?= count($resource->columns) + 1 ?>" class="empty">No hay registros<?= $search !== '' ? ' para «' . e($search) . '»' : '' ?>.</td></tr>
            <?php endif; ?>
            <?php foreach ($result['rows'] as $row): ?>
                <?php $key = $resource->keyOf($row); ?>
                <tr>
                    <?php foreach ($resource->columns as $column => $meta): ?>
                        <td data-label="<?= e($meta[0]) ?>"><?= e($format($row[$column] ?? null, $meta[1] ?? 'text')) ?></td>
                    <?php endforeach; ?>
                    <?php if (!$resource->isReadOnly()): ?>
                        <td class="table__actions">
                            <?php if ($resource->canEdit()): ?>
                                <a class="btn btn--ghost btn--sm" href="<?= e($base . '/' . $key . '/editar') ?>">Editar</a>
                            <?php endif; ?>
                            <form method="post" action="<?= e($base . '/' . $key . '/eliminar') ?>" class="inline-form" data-confirm="¿Eliminar este registro? Esta acción no se puede deshacer.">
                                <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">
                                <button type="submit" class="btn btn--danger btn--sm">Eliminar</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($result['pages'] > 1): ?>
        <nav class="pager" aria-label="Paginación">
            <?php if ($result['page'] > 1): ?>
                <a class="btn btn--ghost btn--sm" href="<?= e($base . $query(['pagina' => $result['page'] - 1])) ?>">← Anterior</a>
            <?php endif; ?>
            <span class="pager__info">Página <?= e($result['page']) ?> de <?= e($result['pages']) ?></span>
            <?php if ($result['page'] < $result['pages']): ?>
                <a class="btn btn--ghost btn--sm" href="<?= e($base . $query(['pagina' => $result['page'] + 1])) ?>">Siguiente →</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
