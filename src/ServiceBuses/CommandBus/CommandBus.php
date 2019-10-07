<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\CommandBus;

use AwdStudio\ServiceBuses\Core\Bus\SingleHandlerBus;

final class CommandBus extends SingleHandlerBus implements CommandBusInterface
{

    /**
     * {@inheritDoc}
     */
    public function handle($command): void
    {
        $this->run($command);
    }

}
