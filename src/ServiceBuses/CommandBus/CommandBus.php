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
     * @param array<string, string>             $handlers  A list of current handlers for commands defined as keys.
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
        $handlerId = $this->resolveHandlerId($command);
        $handler = $this->container->get($handlerId);
        $handlerMethod = $this->resolveHandlingMethod($handler);
        $handler->{$handlerMethod}($command);
    }

    /**
     * Finds the handler name by the command object.
     *
     * @param object $command The command to resolve
     *
     * @return string
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerNotDefined
     */
    private function resolveHandlerId($command): string
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
     * Checks if the handler contains a required method to execute handling and returns it's name.
     *
     * @param object $handler A handler to check.
     *
     * @return string The name of a handling method.
     *
     * @throws \AwdStudio\ServiceBuses\CommandBus\Exception\CommandHandlerIsInappropriate
     */
    private function resolveHandlingMethod($handler): string
    {
        if (\method_exists($handler, '__invoke')) {
            return '__invoke';
        }

        if (\method_exists($handler, 'handle')) {
            return 'handle';
        }

        throw new CommandHandlerIsInappropriate(\sprintf(
            'The handler "%s" must be invokable, or contain a required method "handle"',
            \get_class($handler)
        ));
    }

}
