<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\EventBus;

use AwdStudio\ServiceBuses\Core\Bus\MultipleHandlersBus;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;

final class EventBus extends MultipleHandlersBus implements EventBusInterface
{
    public function handle($event): void
    {
        try {
            $this->run($event);
        } catch (HandlerNotDefined $e) {
        }
    }
}
