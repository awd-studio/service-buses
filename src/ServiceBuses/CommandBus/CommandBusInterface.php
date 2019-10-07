<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\CommandBus;

interface CommandBusInterface
{

    /**
     * Handles a command.
     *
     * @param object $command
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    public function handle($command): void;

}
