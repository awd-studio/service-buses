<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

abstract class MiddlewareBus
{
    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     */
    protected $handlers;

    /** @var \AwdStudio\Bus\Middleware */
    protected $middleware;

    /**
     * @param \AwdStudio\Bus\HandlerLocator $handlers
     * @param \AwdStudio\Bus\Middleware     $middleware
     *
     * @psalm-param   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerLocator $handlers, Middleware $middleware)
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
     * @return iterable<callable>
     *
     * @psalm-return   iterable<array-key, callable(): mixed>
     * @phpstan-return iterable<array-key, callable(): mixed>
     */
    protected function chain(object $message, ...$extraParams): iterable
    {
        foreach ($this->handlers->get(\get_class($message)) as $handler) {
            yield $this->middleware->buildChain($handler, $message, $extraParams);
        }
    }
}
