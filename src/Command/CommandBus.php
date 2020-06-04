<?php

declare(strict_types=1);

namespace AwdStudio\Command;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\MiddlewareBus;

final class CommandBus extends MiddlewareBus implements ICommandBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $command, ...$extraParams): void
    {
        $chains = $this->chains($command, ...$extraParams);
        if (false === $chains->valid()) {
            throw new NoHandlerDefined($command);
        }

        ($chains->current())();
    }
}
