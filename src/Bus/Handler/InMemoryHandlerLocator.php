<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\HandlerLocator;

/**
 * @psalm-external-mutation-free
 *
 * @implements HandlerLocator<callable(object $message, mixed ...$extraParams): mixed>
 */
final class InMemoryHandlerLocator implements HandlerLocator
{
    /**
     * @var array
     *
     * @psalm-var   array<class-string, list<callable(object $message, mixed ...$extraParams): mixed>>
     * @phpstan-var array<class-string, list<callable(object $message, mixed ...$extraParams): mixed>>
     */
    private $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $messageId, callable $handler): void
    {
        $this->handlers[$messageId][] = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $messageId): bool
    {
        return !empty($this->handlers[$messageId]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $messageId): \Iterator
    {
        foreach ($this->handlers[$messageId] ?? [] as $handler) {
            yield $handler;
        }
    }
}
