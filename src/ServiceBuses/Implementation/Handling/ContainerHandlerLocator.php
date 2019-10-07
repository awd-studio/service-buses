<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Implementation\Handling;

use AwdStudio\ServiceBuses\Core\Handing\HandlerLocator;
use AwdStudio\ServiceBuses\Exception\HandlerNotDefined;
use Psr\Container\ContainerInterface;

class ContainerHandlerLocator implements HandlerLocator
{

    /** @var \Psr\Container\ContainerInterface */
    private $container;

    /** @var array<string,array<int,string>> */
    private $handlers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Registers a handler which processes a message.
     *
     * @param string $message Message's class FCQN.
     * @param string $handler Handler's class FCQN.
     *
     * @psalm-param class-string $message Message's class FCQN.
     * @psalm-param class-string $handler Handler's class FCQN.
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     */
    public function add(string $message, string $handler): void
    {
        if (!$this->container->has($handler)) {
            throw new HandlerNotDefined(\sprintf('Undefined handler "%s"', $handler));
        }

        $this->handlers[$message][] = $handler;
    }

    /**
     * {@inheritDoc}
     */
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

    private function resolveForMessage(string $message): iterable
    {
        foreach ($this->handlers[$message] as $handler) {
            yield $this->container->get($handler);
        }
    }

}
