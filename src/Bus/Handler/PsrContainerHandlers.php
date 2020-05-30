<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use Psr\Container\ContainerInterface;

/**
 * @implements ExternalHandlers<callable(object $message, mixed ...$extraParams): mixed>
 */
final class PsrContainerHandlers implements ExternalHandlers, PsrContainerHandlerRegistry
{
    /** @var \Psr\Container\ContainerInterface */
    private $serviceLocator;

    /**
     * @var \AwdStudio\Bus\Handler\ExternalHandlers
     *
     * @psalm-var   ExternalHandlers<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var ExternalHandlers<callable(object $message, mixed ...$extraParams): mixed>
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
     * @param \Psr\Container\ContainerInterface            $serviceLocator
     * @param \AwdStudio\Bus\Handler\ExternalHandlers|null $dynamicHandlers
     *
     * @psalm-param   ExternalHandlers<callable(object $message, mixed ...$extraParams): mixed>|null $dynamicHandlers
     * @phpstan-param ExternalHandlers<callable(object $message, mixed ...$extraParams): mixed>|null $dynamicHandlers
     */
    public function __construct(ContainerInterface $serviceLocator, ?ExternalHandlers $dynamicHandlers = null)
    {
        $this->serviceLocator = $serviceLocator;
        $this->dynamicHandlers = $dynamicHandlers ?? new InMemoryHandlers();
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
    public function get(string $messageId): iterable
    {
        if (true === $this->dynamicHandlers->has($messageId)) {
            yield from $this->dynamicHandlers->get($messageId);
        }

        if (false === empty($this->containerHandlers[$messageId])) {
            $array_unique = \array_unique($this->containerHandlers[$messageId]);
            foreach ($array_unique as $handlerId) {
                yield $this->serviceLocator->get($handlerId);
            }
        }
    }
}
