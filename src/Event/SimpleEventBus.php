<?php

declare(strict_types=1);

namespace AwdStudio\Event;

use AwdStudio\Bus\SimpleBus;

/**
 * Implements the EventBus with handling via middleware.
 */
final class SimpleEventBus extends SimpleBus implements EventBus
{
    public function handle(object $event): void
    {
        iterator_to_array($this->handleMessage($event));
    }
}
