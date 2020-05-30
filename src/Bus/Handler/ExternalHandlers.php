<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Handlers;

/**
 * @psalm-template   TCallback of callable
 * @phpstan-template TCallback of callable
 *
 * @extends \AwdStudio\Bus\Handlers<TCallback>
 */
interface ExternalHandlers extends Handlers
{
    /**
     * Assigns a handler to a particular message.
     *
     * @param string   $messageId
     * @param callable $handler
     *
     * @psalm-param   class-string $messageId
     * @phpstan-param class-string $messageId
     *
     * @psalm-param   TCallback $handler
     * @phpstan-param TCallback $handler
     */
    public function add(string $messageId, callable $handler): void;
}
