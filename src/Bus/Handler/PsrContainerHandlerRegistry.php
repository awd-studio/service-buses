<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\HandlerLocator;
use Psr\Container\ContainerInterface;

/**
 * @implements HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
 */
final class PsrContainerHandlerRegistry implements HandlerRegistry
{
    /** @var \Psr\Container\ContainerInterface */
    private $serviceLocator;

    /**
     * @var \AwdStudio\Bus\HandlerLocator
     *
     * @psalm-var   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
     */
    private $dynamicHandlers;

    /**
     * @var array
     *
     * @psalm-var   array<class-string, array<array-key, string>>
     * @phpstan-var array<class-string, array<array-key, string>>
     */
    private $containerHandlers;

    /**
     * @param \Psr\Container\ContainerInterface  $serviceLocator
     * @param \AwdStudio\Bus\HandlerLocator|null $dynamicHandlers
     *
     * @psalm-param   HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>|null $dynamicHandlers
     * @phpstan-param HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>|null $dynamicHandlers
     */
    public function __construct(ContainerInterface $serviceLocator, ?HandlerLocator $dynamicHandlers = null)
    {
        $this->serviceLocator = $serviceLocator;
        $this->dynamicHandlers = $dynamicHandlers ?? new InMemoryHandlerLocator();
        $this->containerHandlers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $messageId, string $handlerId): void
    {
        if (false === $this->serviceLocator->has($handlerId)) {
            throw new InvalidHandler(
                \sprintf('There is no registered services such a "%s" to handle a "%s" message', $handlerId, $messageId)
            );
        }

        $this->containerHandlers[$messageId][] = $handlerId;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $messageId, callable $handler): void
    {
        $this->dynamicHandlers->add($messageId, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $messageId): bool
    {
        return $this->dynamicHandlers->has($messageId) || !empty($this->containerHandlers[$messageId]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $messageId): \Iterator
    {
        if (true === $this->dynamicHandlers->has($messageId)) {
            foreach ($this->dynamicHandlers->get($messageId) as $handler) {
                yield $handler;
            }
        }

        if (false === empty($this->containerHandlers[$messageId])) {
            foreach (\array_unique($this->containerHandlers[$messageId]) as $handlerId) {
                yield $this->serviceLocator->get($handlerId);
            }
        }
    }
}
