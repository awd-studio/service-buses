<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\HandlerLocator;
use Psr\Container\ContainerInterface;

final class PsrContainerClassHandlerRegistry implements ClassHandlerRegistry
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private array $containerHandlers = [];

    public function __construct(
        private readonly ContainerInterface $serviceLocator,
        private readonly HandlerLocator $dynamicHandlers = new InMemoryHandlerLocator()
    ) {
    }

    public function register(string $messageId, string $handlerClass, string $handlerMethod = '__invoke'): void
    {
        if (false === $this->serviceLocator->has($handlerClass)) {
            throw new InvalidHandler(sprintf('There is no a service such as "%s" to handle a "%s" message', $handlerClass, $messageId));
        }

        $this->containerHandlers[$messageId][$handlerClass] = $handlerMethod;
    }

    public function add(string $messageId, callable $handler): void
    {
        $this->dynamicHandlers->add($messageId, $handler);
    }

    public function has(string $messageId): bool
    {
        return $this->dynamicHandlers->has($messageId) || false === empty($this->containerHandlers[$messageId]);
    }

    public function get(string $messageId): \Iterator
    {
        if (true === $this->dynamicHandlers->has($messageId)) {
            foreach ($this->dynamicHandlers->get($messageId) as $handler) {
                yield $handler;
            }
        }

        if (false === empty($this->containerHandlers[$messageId])) {
            foreach ($this->containerHandlers[$messageId] as $handlerId => $handlerMethod) {
                /** @var object $handler */
                $handler = $this->serviceLocator->get($handlerId);
                \assert(method_exists($handler, $handlerMethod));
                yield $handler->$handlerMethod(...);
            }
        }
    }
}
