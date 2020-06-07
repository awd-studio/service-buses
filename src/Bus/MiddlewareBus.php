<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * A base for buses that need the middleware layer
 * to wrap handlers.
 *
 * To implement such bus - just extend this one and
 * add a method with the logic to use chains from here.
 *
 * @see \AwdStudio\Bus\HandlerLocator
 */
abstract class MiddlewareBus
{
    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     */
    protected $handlers;

    /** @var \AwdStudio\Bus\MiddlewareChain */
    protected $middleware;

    /**
     * @param \AwdStudio\Bus\HandlerLocator  $handlers
     * @param \AwdStudio\Bus\MiddlewareChain $middleware
     *
     * @psalm-param   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerLocator $handlers, MiddlewareChain $middleware)
    {
        $this->handlers = $handlers;
        $this->middleware = $middleware;
    }

    /**
     * Handles the message.
     *
     * @param object $message
     * @param mixed  ...$extraParams
     *
     * @return \Iterator<callable>
     *
     * @psalm-return   \Iterator<array-key, callable(): mixed>
     * @phpstan-return \Iterator<array-key, callable(): mixed>
     */
    protected function buildChains(object $message, ...$extraParams): \Iterator
    {
        foreach ($this->handlers->get(\get_class($message)) as $handler) {
            yield $this->middleware->chain($message, $handler, $extraParams);
        }
    }
}
