<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * A base implementation of a bus.
 *
 * To implement such bus - just extend this one and
 * add a method with the logic to use chains from here.
 *
 * @see \AwdStudio\Bus\HandlerLocator
 */
abstract class SimpleBus
{
    public function __construct(
        protected readonly HandlerLocator $handlers,
    ) {
    }

    /**
     * Handles the message.
     *
     * @return \Iterator<mixed>
     */
    protected function handleMessage(object $message): \Iterator
    {
        foreach ($this->handlers->get($message::class) as $handler) {
            yield $handler($message);
        }
    }
}
