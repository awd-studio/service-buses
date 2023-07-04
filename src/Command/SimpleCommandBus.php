<?php

declare(strict_types=1);

namespace AwdStudio\Command;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\SimpleBus;

/**
 * Implements the CommandBus with handling via middleware.
 */
final class SimpleCommandBus extends SimpleBus implements CommandBus
{
    public function handle(object $command): void
    {
        $chains = $this->handleMessage($command);
        if (false === $chains->valid()) {
            throw new NoHandlerDefined($command);
        }
    }
}
