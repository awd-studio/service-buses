<?php

declare(strict_types=1);

namespace AwdStudio\Event;

use AwdStudio\Bus\MiddlewareBus;

final class EventBus extends MiddlewareBus implements IEventBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $event, ...$extraParams): void
    {
        foreach ($this->chain($event, ...$extraParams) as $chain) {
            $chain();
        }
    }
}
