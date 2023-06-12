<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Middleware;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\MiddlewareChain;

/**
 * Implements MiddlewareChain that builds a chain from callback-handlers.
 */
final class CallbackMiddlewareChain implements MiddlewareChain
{
    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   HandlerLocator<callable(object $message, callable $next, mixed ...$extraParams): mixed>
     *
     * @phpstan-var HandlerLocator<callable(object $message, callable $next, mixed ...$extraParams): mixed>
     */
    private $middleware;

    /**
     * @psalm-param   HandlerLocator<callable(object $message, callable $next, mixed ...$extraParams): mixed> $handlers
     *
     * @phpstan-param HandlerLocator<callable(object $message, callable $next, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerLocator $handlers)
    {
        $this->middleware = $handlers;
    }

    public function chain(object $message, callable $handler, array $extraParams = []): callable
    {
        $next = static function () use ($handler, $message, $extraParams) {
            return $handler($message, ...$extraParams);
        };

        foreach ($this->middleware->get(\get_class($message)) as $item) {
            $next = static function () use ($item, $message, $next, $extraParams) {
                return $item($message, $next, ...$extraParams);
            };
        }

        return $next;
    }
}
