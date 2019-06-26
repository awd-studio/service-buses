<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses;

use AwdStudio\ServiceBuses\CommandBus\CommandBus;
use AwdStudio\ServiceBuses\CommandBus\CommandBusInterface;
use AwdStudio\ServiceBuses\EventBus\EventBus;
use AwdStudio\ServiceBuses\EventBus\EventBusInterface;
use AwdStudio\ServiceBuses\QueryBus\QueryBus;
use AwdStudio\ServiceBuses\QueryBus\QueryBusInterface;

final class ServiceBusesFactory
{

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var \AwdStudio\ServiceBuses\CommandBus\CommandBusInterface */
    protected $_commandBus;

    /** @var \AwdStudio\ServiceBuses\EventBus\EventBusInterface */
    protected $_eventBus;

    /** @var \AwdStudio\ServiceBuses\QueryBus\QueryBusInterface */
    protected $_queryBus;

    /**
     * ServiceBusesFactory constructor.
     *
     * @param \Psr\Container\ContainerInterface $container DI container to manage handlers.
     */
    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates an instance of a command-bus.
     *
     * @param array $handlers A list of handlers with commands as keys.
     *
     * @return \AwdStudio\ServiceBuses\CommandBus\CommandBusInterface
     */
    public function commandBus(array $handlers = []): CommandBusInterface
    {
        if (null === $this->_commandBus) {
            $this->_commandBus = new CommandBus($this->container, $handlers);
        }

        return $this->_commandBus;
    }

    /**
     * Creates an instance of an event-bus.
     *
     * @param array $handlers A list of handlers with commands as keys.
     *
     * @return \AwdStudio\ServiceBuses\EventBus\EventBusInterface
     */
    public function eventBus(array $handlers = []): EventBusInterface
    {
        if (null === $this->_eventBus) {
            $this->_eventBus = new EventBus($this->container, $handlers);
        }

        return $this->_eventBus;
    }

    /**
     * Creates an instance of a query-bus.
     *
     * @param array $subscribers A list of handlers with commands as keys.
     *
     * @return \AwdStudio\ServiceBuses\QueryBus\QueryBusInterface
     */
    public function queryBus(array $subscribers = []): QueryBusInterface
    {
        if (null === $this->_queryBus) {
            $this->_queryBus = new QueryBus($this->container, $subscribers);
        }

        return $this->_queryBus;
    }

}
