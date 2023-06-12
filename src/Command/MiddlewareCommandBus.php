<?php

declare(strict_types=1);

namespace AwdStudio\Command;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\MiddlewareBus;

/**
 * Implements the CommandBus with handling via middleware.
 */
final class MiddlewareCommandBus extends MiddlewareBus implements CommandBus
{
    public function handle(object $command, mixed ...$extraParams): void
    {
        $chains = $this->buildChains($command, ...$extraParams);
        if (false === $chains->valid()) {
            throw new NoHandlerDefined($command);
        }

        ($chains->current())();
    }
}
