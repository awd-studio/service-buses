<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Middleware;

use AwdStudio\Bus\Handlers;
use AwdStudio\Bus\Middleware;

final class MiddlewareChain implements Middleware
{
    /**
     * @var \AwdStudio\Bus\Handlers
     *
     * @psalm-var   Handlers<callable(callable $next, object $message, mixed ...$extraParams): mixed>
     * @phpstan-var Handlers<callable(callable $next, object $message, mixed ...$extraParams): mixed>
     */
    private $middleware;

    /**
     * @param \AwdStudio\Bus\Handlers $handlers
     *
     * @psalm-param   Handlers<callable(callable $next, object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param Handlers<callable(callable $next, object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(Handlers $handlers)
    {
        $this->middleware = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function buildChain(callable $handler, object $message, array $extraParams = []): callable
    {
        $next = /** @return mixed */ static function () use ($handler, $message, $extraParams) {
            return $handler($message, ...$extraParams);
        };

        foreach ($this->middleware->get(\get_class($message)) as $item) {
            $next = /** @return mixed */ static function () use ($item, $next, $message, $extraParams) {
                return $item($next, $message, ...$extraParams);
            };
        }

        return $next;
    }
}
