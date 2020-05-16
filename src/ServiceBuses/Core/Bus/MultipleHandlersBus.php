<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Core\Bus;

abstract class MultipleHandlersBus extends BusProcessor
{
    /**
     * Processes a particular message.
     *
     * @param object $message
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    public function run($message): void
    {
        foreach ($this->resolveHandlers($message) as $handler) {
            $this->execute($message, $handler);
        }
    }
}
