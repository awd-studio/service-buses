<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;

final class Chain implements MiddlewareChain
{
    /**
     * @var callable[]
     */
    private $middleware = [];

    public function __construct(callable ...$middleware)
    {
        $this->middleware = $middleware;
    }

    public function add(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * @psalm-suppress MissingClosureParamType
     * @psalm-suppress MissingClosureReturnType
     */
    public function chain($message, callable $handler): callable
    {
        $next = static function () use ($handler, $message) {
            return $handler($message);
        };

        foreach ($this->middleware as $middleware) {
            $next = static function ($message) use ($middleware, $next) {
                return $middleware($message, $next);
            };
        }

        return $next;
    }
}
