<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\EventBus;

interface EventBusInterface
{
    /**
     * Handles an event.
     *
     * @param object $event
     *
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    public function handle($event): void;
}
