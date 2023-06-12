<?php

declare(strict_types=1);

namespace AwdStudio\Event;

use AwdStudio\Bus\MiddlewareBus;

/**
 * Implements the EventBus with handling via middleware.
 */
final class MiddlewareEventBus extends MiddlewareBus implements EventBus
{
    public function handle(object $event, mixed ...$extraParams): void
    {
        foreach ($this->buildChains($event, ...$extraParams) as $chain) {
            $chain();
        }
    }
}
