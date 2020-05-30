<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\Handlers;

/**
 * @psalm-external-mutation-free
 */
final class InMemoryHandlers implements Handlers
{
    /**
     * @var array
     *
     * @psalm-var   array<class-string, array<array-key, callable>>
     * @phpstan-var array<class-string, array<array-key, callable>>
     */
    private $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * Assigns a handler to a particular message.
     *
     * @param string   $message
     * @param callable $handler
     *
     * @psalm-param   class-string $message
     * @phpstan-param class-string $message
     */
    public function add(string $message, callable $handler): void
    {
        $this->handlers[$message][] = $handler;
    }

    /**
     * Returns all configured state.
     *
     * @return array
     *
     * @psalm-return   array<class-string, array<array-key, callable>>
     * @phpstan-return array<class-string, array<array-key, callable>>
     */
    public function export(): array
    {
        return $this->handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function has(object $message): bool
    {
        return !empty($this->handlers[\get_class($message)]);
    }

    /**
     * {@inheritdoc}
     */
    public function get(object $message): \Traversable
    {
        if (false === $this->has($message)) {
            throw new NoHandlerDefined($message);
        }

        return $this->resolveHandlerGenerator($message);
    }

    /**
     * Yields a list of handlers.
     *
     * @param object $message
     *
     * @return \Generator<callable>
     *
     * @psalm-return   \Generator<array-key, callable>
     * @phpstan-return \Generator<array-key, callable>
     */
    private function resolveHandlerGenerator(object $message): \Generator
    {
        yield from $this->handlers[\get_class($message)];
    }
}
