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
        foreach ($this->chains($command, ...$extraParams) as $chain) {
            $chain();

            return;
        }

        throw new NoHandlerDefined($command);
    }
}
