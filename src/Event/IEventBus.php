<?php

declare(strict_types=1);

namespace AwdStudio\Event;

interface IEventBus
{
    /**
     * Handles an event.
     *
     * @param object $event
     * @param mixed  ...$extraParams
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     */
    public function handle(object $event, ...$extraParams): void;
}
