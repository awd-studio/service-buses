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
            $this->validateHandler($handler);
            $handler->notify($event);
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
     * Checks if the handler contains a required method to execute handling.
     *
     * @param object $handler A handler to check.
     *
     * @throws \AwdStudio\ServiceBuses\EventBus\Exception\EventSubscriberIsInappropriate
     */
    private function validateHandler($handler): void
    {
        if (!\method_exists($handler, 'notify')) {
            throw new EventSubscriberIsInappropriate(\sprintf(
                'The subscriber "%s" does not contains a required method "notify"',
                \get_class($handler)
            ));
        }
    }

}
