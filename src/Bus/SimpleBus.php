<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

abstract class SimpleBus
{
    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   \AwdStudio\Bus\HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var \AwdStudio\Bus\HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     */
    protected $handlers;

    /**
     * @param \AwdStudio\Bus\HandlerLocator $handlers
     *
     * @psalm-param   \AwdStudio\Bus\HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param \AwdStudio\Bus\HandlerLocator<callable(object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerLocator $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * Resolves all handlers for a message.
     *
     * @param object $message
     * @param mixed  ...$extraParams
     *
     * @return \Iterator<mixed>|mixed[]
     *
     * @psalm-return   \Iterator<array-key, mixed>
     * @phpstan-return \Iterator<array-key, mixed>
     */
    protected function handleAll(object $message, ...$extraParams): \Iterator
    {
        foreach ($this->handlers->get(\get_class($message)) as $handler) {
            yield $handler($message, ...$extraParams);
        }
    }
}
