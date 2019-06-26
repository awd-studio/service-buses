<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\CommandBus;

interface CommandBusInterface
{

    /**
     * Subscribes a command-handler to a particular command.
     *
     * @param string $commandHandler The handler to subscribe.
     * @param string $command        The command for which a handler will be executed.
     *
     * @return \AwdStudio\ServiceBuses\CommandBus\CommandBusInterface
     */
    public function subscribe(string $commandHandler, string $command): self;

    /**
     * Handles a certain command, with a registered command handler.
     *
     * @param mixed $command The command to handle.
     *
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerIsInappropriate
     */
    public function handle($command): void;

}
