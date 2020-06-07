<?php

declare(strict_types=1);

namespace AwdStudio\Event;

/**
 * Provides an interface for implementing the Event Bus pattern.
 */
interface EventBus
{
    /**
     * Handles an event via all provided handlers.
     *
     * If there is no handles provided - does not act.
     *
     * An event can be any of PHP plain objects.
     * Event handlers, supposed to be, external callbacks than not act back.
     * Therefore, the event bus returns no value.
     *
     * As the pattern told, there might be multiple handlers for one event.
     *
     * A bus also can get additional parameters,
     * that will be passed to the handlers,
     * if they allow to provide them.
     *
     * @param object $event
     * @param mixed  ...$extraParams
     */
    public function handle(object $event, ...$extraParams): void;
}
