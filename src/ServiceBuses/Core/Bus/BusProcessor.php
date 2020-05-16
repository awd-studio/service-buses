<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Core\Bus;

use AwdStudio\ServiceBuses\Core\Handing\HandlerLocator;
use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use AwdStudio\ServiceBuses\Exception\WrongMessage;

abstract class BusProcessor
{
    /**
     * @var \AwdStudio\ServiceBuses\Core\Handing\HandlerLocator
     */
    protected $handlers;

    /**
     * @var \AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain
     */
    protected $middleware;

    public function __construct(HandlerLocator $handlers, MiddlewareChain $middleware)
    {
        $this->handlers = $handlers;
        $this->middleware = $middleware;
    }

    /**
     * Returns a list of handlers for particular message.
     *
     * @param object $message
     *
     * @return iterable<int, callable>
     */
    protected function resolveHandlers($message): iterable
    {
        $this->validateCommand($message);

        return $this->handlers->get(\get_class($message));
    }

    /**
     * Processes a message handler.
     *
     * @param object   $message
     * @param callable $handler
     *
     * @return mixed
     */
    protected function execute($message, callable $handler)
    {
        $chain = $this->middleware->chain($message, $handler);

        return $chain($message);
    }

    /**
     * Checks a command.
     *
     * @param mixed $command
     */
    protected function validateCommand($command): void
    {
        if (!is_object($command)) {
            throw new WrongMessage(\sprintf('Command must be an object, %s given.', \gettype($command)));
        }
    }
}
