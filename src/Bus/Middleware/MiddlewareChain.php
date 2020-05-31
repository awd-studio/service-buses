<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Middleware;

use AwdStudio\Bus\HandlerLocator;
use AwdStudio\Bus\Middleware;

final class MiddlewareChain implements Middleware
{
    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   HandlerLocator<callable(callable $next, object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerLocator<callable(callable $next, object $message, mixed ...$extraParams): mixed>
     */
    private $middleware;

    /**
     * @param \AwdStudio\Bus\HandlerLocator $handlers
     *
     * @psalm-param   HandlerLocator<callable(callable $next, object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param HandlerLocator<callable(callable $next, object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerLocator $handlers)
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
