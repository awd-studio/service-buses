<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\HandlerLocator;

final class InMemoryHandlerLocator implements HandlerLocator
{
    public function __construct(
        /** @phpstan-var array<class-string, list<callable>> */
        private array $handlers = []
    ) {
    }

    public function add(string $messageId, callable $handler): void
    {
        $this->handlers[$messageId][] = $handler;
    }

    public function has(string $messageId): bool
    {
        return !empty($this->handlers[$messageId]);
    }

    public function get(string $messageId): \Iterator
    {
        foreach ($this->handlers[$messageId] ?? [] as $handler) {
            yield $handler;
        }
    }
}
