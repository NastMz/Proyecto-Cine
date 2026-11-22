<?php

declare(strict_types=1);

namespace Cine\View;

use RuntimeException;

/**
 * Renders plain-PHP templates from the `templates/` directory.
 * Templates must escape every dynamic value with `e()`.
 */
final class View
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(private readonly string $directory)
    {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /**
     * Renders `$template` and wraps it in `$layout` (which receives `$content`).
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = [], ?string $layout = 'layouts/site'): string
    {
        $content = $this->partial($template, $data);
        if ($layout === null) {
            return $content;
        }

        return $this->partial($layout, $data + ['content' => $content]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function partial(string $template, array $data = []): string
    {
        if (preg_match('#^[a-z0-9_/-]+$#', $template) !== 1) {
            throw new RuntimeException("Invalid template name: {$template}");
        }
        $file = $this->directory . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Template not found: {$template}");
        }

        $vars = $data + $this->shared;
        // Non-static closure: templates can call `$this->partial(...)`.
        $render = function (string $__file, array $__vars): string {
            extract($__vars, EXTR_SKIP);
            ob_start();
            try {
                require $__file;
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }

            return (string) ob_get_clean();
        };

        return $render($file, $vars);
    }
}
