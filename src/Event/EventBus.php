<?php

declare(strict_types=1);

namespace AwdStudio\Event;

use AwdStudio\Bus\Bus;

final class EventBus extends Bus implements IEventBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $event): void
    {
        if (true === $this->handlers->has($event)) {
            $handlers = $this->doHandling($event);
            while ($handlers->valid()) {
                $handlers->next();
            }
        }
    }
}
