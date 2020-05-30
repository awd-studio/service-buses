<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

interface PsrContainerHandlerRegistry
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
