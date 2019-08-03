<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\QueryBus;

use AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerIsNotAppropriate;
use AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerNotDefined;
use Psr\Container\ContainerInterface;

final class QueryBus implements QueryBusInterface
{

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var string[] */
    protected $handlers;

    /**
     * QueryBus constructor.
     *
     * @param \Psr\Container\ContainerInterface $container DI container to manage handlers.
     * @param array<string, string>             $handlers  A list of current handlers for queries defined as keys.
     */
    public function __construct(ContainerInterface $container, array $handlers = [])
    {
        $this->container = $container;
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(string $queryHandler, string $query): QueryBusInterface
    {
        $this->handlers[$query] = $queryHandler;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle($query)
    {
        $id = $this->resolveHandler($query);
        $handler = $this->container->get($id);
        $handlingMethod = $this->resolveHandlingMethod($handler);

        return $handler->{$handlingMethod}($query);
    }

    /**
     * Finds the handler name by the query object.
     *
     * @param object $query The query to resolve
     *
     * @return string
     * @throws \AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerNotDefined
     */
    private function resolveHandler($query): string
    {
        $queryClass = \get_class($query);
        $handlerClass = $this->handlers[$queryClass] ?? null;

        if (null === $handlerClass) {
            $message = \sprintf('There is no handlers for the query "%s"', $queryClass);
            throw new QueryHandlerNotDefined($message);
        }

        return $handlerClass;
    }

    /**
     * Checks if the handler contains a required method to execute handling and returns it's name.
     *
     * @param object $handler A handler to check.
     *
     * @return string The name of the handling method.
     *
     * @throws \AwdStudio\ServiceBuses\QueryBus\Exception\QueryHandlerIsNotAppropriate
     */
    private function resolveHandlingMethod($handler): string
    {
        if (\method_exists($handler, '__invoke')) {
            return '__invoke';
        }

        if (\method_exists($handler, 'handle')) {
            return 'handle';
        }

        if (\method_exists($handler, 'fetch')) {
            return 'fetch';
        }

        throw new QueryHandlerIsNotAppropriate(\sprintf(
            'The handler "%s" must contain one of required methods: "__invoke", "handle" or "fetch"',
            \get_class($handler)
        ));
    }

}
