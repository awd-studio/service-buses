<?php

declare(strict_types=1);

namespace AwdStudio\Command;

use AwdStudio\Bus\Bus;

final class CommandBus extends Bus implements ICommandBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $command): void
    {
        $firstHandler = $this->doHandling($command);
        $firstHandler->current();
    }
}
