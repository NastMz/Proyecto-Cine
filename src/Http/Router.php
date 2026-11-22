<?php

declare(strict_types=1);

namespace Cine\Http;

/**
 * Minimal router: `/peliculas/{id}` style patterns, where every placeholder
 * matches one path segment and `{id}` placeholders only match digits.
 */
final class Router
{
    /** @var list<array{method: string, regex: string, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => self::compile($pattern),
            'handler' => $handler,
        ];
    }

    /**
     * @return array{status: int, handler: ?callable, params: array<string, string>}
     */
    public function match(string $method, string $path): array
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method && !($method === 'HEAD' && $route['method'] === 'GET')) {
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            foreach ($params as $name => $value) {
                // Ids beyond the PostgreSQL integer range cannot exist: 404.
                if (self::isIdParam($name) && !Request::isId($value)) {
                    return ['status' => 404, 'handler' => null, 'params' => []];
                }
            }

            return ['status' => 200, 'handler' => $route['handler'], 'params' => $params];
        }

        return ['status' => $pathMatched ? 405 : 404, 'handler' => null, 'params' => []];
    }

    private static function isIdParam(string $name): bool
    {
        return str_ends_with($name, 'id') || str_ends_with($name, 'Id');
    }

    private static function compile(string $pattern): string
    {
        $regex = preg_replace_callback(
            '/\{([a-z][a-zA-Z0-9]*)\}/',
            static function (array $m): string {
                $segment = self::isIdParam($m[1]) ? '\d+' : '[a-z0-9_~-]+';

                return '(?P<' . $m[1] . '>' . $segment . ')';
            },
            $pattern,
        );

        return '#^' . $regex . '$#';
    }
}
