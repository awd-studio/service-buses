<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\HandlerLocator;

/**
 * @psalm-template   TCallback of callable
 * @phpstan-template TCallback of callable
 *
 * @extends HandlerLocator<TCallback>
 */
interface HandlerRegistry extends HandlerLocator
{
    /**
     * Registers a handler from a PSR-container as a message handler.
     *
     * @param string $messageId
     * @param string $handlerId
     *
     * @throws \AwdStudio\Bus\Exception\InvalidHandler
     *
     * @psalm-param   class-string $messageId
     * @phpstan-param class-string $messageId
     */
    public function register(string $messageId, string $handlerId): void;
}
