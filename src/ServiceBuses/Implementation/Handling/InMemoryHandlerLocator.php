<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Implementation\Handling;

use AwdStudio\ServiceBuses\Core\Handing\HandlerLocator;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;

class InMemoryHandlerLocator implements HandlerLocator
{

    /** @var array<string,array<int,callable>> */
    private $handlers = [];

    /** @param array<string,array<int,callable>> $handlers */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $message): iterable
    {
        if (empty($this->handlers[$message])) {
            throw new HandlerNotDefined(\sprintf('No handler defined for "%s"', $message));
        }

        return $this->handlers[$message];
    }

}
