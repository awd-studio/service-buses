<?php

declare(strict_types=1);

namespace AwdStudio\Command;

interface ICommandBus
{
    /**
     * Handles a command.
     *
     * @param object $command
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     */
    public function handle(object $command): void;
}
