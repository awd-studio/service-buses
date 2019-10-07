<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\EventBus;

use AwdStudio\ServiceBuses\Core\Bus\MultipleHandlersBus;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;

final class EventBus extends MultipleHandlersBus implements EventBusInterface
{

    /**
     * {@inheritDoc}
     */
    public function handle($event): void
    {

        $this->run($event);

//        try {
//            $this->run($event);
//        } catch (HandlerNotDefined $e) {
//            return;
//        }
    }

}
