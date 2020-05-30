<?php

declare(strict_types=1);

namespace AwdStudio\CommandBus;

use AwdStudio\Bus\Bus;

final class CommandBus extends Bus implements CommandBusInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $command): void
    {
        foreach ($this->doHandling($command) as $handler) {
            return;
        }
    }
}
