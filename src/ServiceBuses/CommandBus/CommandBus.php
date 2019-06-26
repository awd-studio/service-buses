<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\CommandBus;

use AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerIsInappropriate;
use AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerNotDefined;
use Psr\Container\ContainerInterface;

final class CommandBus implements CommandBusInterface
{

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var string[] */
    protected $handlers;

    /**
     * CommandBus constructor.
     *
     * @param \Psr\Container\ContainerInterface $container DI container to manage handlers.
     * @param array                             $handlers  A list of current handlers for commands defined as keys.
     */
    public function __construct(ContainerInterface $container, array $handlers = [])
    {
        $this->container = $container;
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(string $commandHandler, string $command): CommandBusInterface
    {
        $this->handlers[$command] = $commandHandler;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle($command): void
    {
        $id = $this->resolveHandlerNameByCommand($command);
        $handler = $this->container->get($id);
        $this->validateHandler($handler);
        $handler->handle($command);
    }

    /**
     * Finds the handler name by the command object.
     *
     * @param object $command The command to resolve
     *
     * @return string
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerNotDefined
     */
    private function resolveHandlerNameByCommand($command): string
    {
        $commandClass = \get_class($command);
        $handlerClass = $this->handlers[$commandClass] ?? null;

        if (null === $handlerClass) {
            $message = \sprintf('There is no handlers for the command "%s"', $commandClass);
            throw new CommandHandlerNotDefined($message);
        }

        return $handlerClass;
    }

    /**
     * Checks if the handler contains a required method to execute handling.
     *
     * @param object $handler A handler to check.
     *
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerIsInappropriate
     */
    private function validateHandler($handler): void
    {
        if (!\method_exists($handler, 'handle')) {
            throw new CommandHandlerIsInappropriate(\sprintf(
                'The handler "%s" does not contains a required method "handle"',
                \get_class($handler)
            ));
        }
    }

}
