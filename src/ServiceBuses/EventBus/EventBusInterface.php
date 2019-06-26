<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\EventBus;

interface EventBusInterface
{

    /**
     * Appends a subscriber to a particular event.
     *
     * @param string $eventSubscriber The subscriber.
     * @param string $event           The event to append a subscriber to.
     *
     * @return \AwdStudio\ServiceBuses\EventBus\EventBusInterface
     */
    public function subscribe(string $eventSubscriber, string $event): self;

    /**
     * Calls every subscribers that is subscribed to the event.
     *
     * @param object $event The propagated event.
     *
     * @throws \AwdStudio\ServiceBuses\EventBus\Exception\EventSubscriberIsInappropriate
     */
    public function dispatch($event): void;

}
