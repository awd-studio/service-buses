<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\HandlerLocator;

/**
 * @psalm-template   TCallback of callable
 *
 * @phpstan-template TCallback of callable
 *
 * @extends HandlerLocator<TCallback>
 */
interface HandlerRegistry extends HandlerLocator
{
    /**
     * Registers a handler from a PSR-container as a message handler.
     *
     * @param string $messageId     a message on which the handler subscribes on
     * @param string $handlerId     an ID of a service that represents a handler in a container
     * @param string $handlerMethod the name of a method that handles a message
     *
     * @throws \AwdStudio\Bus\Exception\InvalidHandler
     *
     * @psalm-param   class-string $messageId
     *
     * @phpstan-param class-string $messageId
     */
    public function register(string $messageId, string $handlerId, string $handlerMethod = '__invoke'): void;
}
