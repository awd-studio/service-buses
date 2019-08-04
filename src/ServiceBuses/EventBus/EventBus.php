<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\EventBus;

use AwdStudio\ServiceBuses\EventBus\Exception\EventSubscriberIsInappropriate;
use Psr\Container\ContainerInterface;

final class EventBus implements EventBusInterface
{

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var string[][] */
    protected $subscribers;

    /**
     * EventBus constructor.
     *
     * @param \Psr\Container\ContainerInterface    $container DI container to manage handlers.
     * @param array<string, array<string, string>> $handlers  A list of current handlers for commands defined as keys.
     */
    public function __construct(ContainerInterface $container, array $handlers = [])
    {
        $this->container = $container;
        $this->subscribers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(string $eventSubscriber, string $event): EventBusInterface
    {
        $subscribers = $this->subscribers[$event] ?? [];
        if (!\in_array($eventSubscriber, $subscribers)) {
            $subscribers[] = $eventSubscriber;
        }
        $this->subscribers[$event] = $subscribers;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch($event): void
    {
        $subscriberIds = $this->resolveSubscribers($event);
        foreach ($subscriberIds as $subscriberId) {
            $handler = $this->container->get($subscriberId);
            $handlingMethod = $this->resolveHandlingMethod($handler);
            $handler->{$handlingMethod}($event);
        }
    }

    /**
     * Finds the handler name by the command object.
     *
     * @param object $event The command to resolve
     *
     * @return array A list of subscribers (could be empty).
     */
    private function resolveSubscribers($event): array
    {
        $commandClass = \get_class($event);
        $subscribers = $this->subscribers[$commandClass] ?? [];

        return $subscribers;
    }

    /**
     * Checks if the handler contains a required method to execute handling and returns it's name.
     *
     * @param object $handler A handler to check.
     *
     * @return string The name of a handler method.
     * @throws \AwdStudio\ServiceBuses\EventBus\Exception\EventSubscriberIsInappropriate
     */
    private function resolveHandlingMethod($handler): string
    {
        if (\method_exists($handler, '__invoke')) {
            return '__invoke';
        }

        if (\method_exists($handler, 'handle')) {
            return 'handle';
        }

        if (\method_exists($handler, 'notify')) {
            return 'notify';
        }

        throw new EventSubscriberIsInappropriate(\sprintf(
            'The subscriber "%s" must contain one of required methods: "__invoke", "handle" or "notify""',
            \get_class($handler)
        ));
    }

}
