<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Implementation\Handling;

use AwdStudio\ServiceBuses\Core\Handing\HandlerLocator;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use Psr\Container\ContainerInterface;

class ContainerHandlerLocator implements HandlerLocator
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var array<string,array<int,string>>
     */
    private $handlers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Registers a handler which processes a message.
     *
     * @param string $message message's class FCQN
     * @param string $handler handler's class FCQN
     *
     * @psalm-param class-string $message Message's class FCQN.
     * @psalm-param class-string $handler Handler's class FCQN.
     */
    public function add(string $message, string $handler): void
    {
        $this->handlers[$message][] = $handler;
    }

    public function get(string $message): iterable
    {
        if (!$this->hasHandler($message)) {
            throw new HandlerNotDefined(\sprintf('No handler defined for "%s"', $message));
        }

        return $this->resolveForMessage($message);
    }

    private function hasHandler(string $message): bool
    {
        $hasMessage = !empty($this->handlers[$message]);
        $hasHandlers = true;

        if ($hasMessage) {
            foreach ($this->handlers[$message] as $handler) {
                if (!$this->container->has($handler)) {
                    $hasHandlers = false;

                    break;
                }
            }
        }

        return $hasMessage && $hasHandlers;
    }

    /**
     * Resolves a handler for certain message.
     *
     * @return iterable<callable>
     */
    private function resolveForMessage(string $message): iterable
    {
        foreach ($this->handlers[$message] as $handler) {
            yield $this->container->get($handler);
        }
    }
}
