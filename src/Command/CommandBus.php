<?php

declare(strict_types=1);

namespace AwdStudio\Command;

/**
 * Provides an interface for implementing the Command Bus pattern from the CQRS architectural principe.
 */
interface CommandBus
{
    /**
     * Handles a command with a handler.
     *
     * If a handler is not provided - throws an exception.
     *
     * A command can be any of PHP plain objects.
     * According to the pattern, it mustn't return anything,
     * and have the only one handler.
     *
     * A bus also can get additional parameters,
     * that will be passed to the handlers,
     * if they allow to provide them.
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     */
    public function handle(object $command, mixed ...$extraParams): void;
}
