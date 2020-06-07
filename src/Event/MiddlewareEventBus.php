<?php

declare(strict_types=1);

namespace AwdStudio\Event;

use AwdStudio\Bus\MiddlewareBus;

/**
 * Implements the EventBus with handling via middleware.
 */
final class MiddlewareEventBus extends MiddlewareBus implements EventBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $event, ...$extraParams): void
    {
        foreach ($this->buildChains($event, ...$extraParams) as $chain) {
            $chain();
        }
    }
}
