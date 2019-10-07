<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Core\Bus;

use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;

abstract class SingleHandlerBus extends BusProcessor
{

    /**
     * Processes a particular message.
     *
     * @param object $message
     *
     * @return mixed
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    public function run($message)
    {
        return $this->execute($message, $this->firstHandler($message));
    }

    /**
     * Resolves the first handler.
     *
     * @param object $message
     *
     * @return callable
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    protected function firstHandler($message): callable
    {
        foreach ($this->resolveHandlers($message) as $handler) {
            return $handler;
        }

        throw new HandlerNotDefined(\sprintf('No handlers defined for "%s"', \get_class($message)));
    }

}
