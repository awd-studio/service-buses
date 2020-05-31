<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * @psalm-template   TCallback of callable
 * @phpstan-template TCallback of callable
 */
interface HandlerLocator
{
    /**
     * Assigns a handler to a particular message.
     *
     * @param string   $messageId
     * @param callable $handler
     *
     * @psalm-param   class-string $messageId
     * @phpstan-param class-string $messageId
     *
     * @psalm-param   TCallback $handler
     * @phpstan-param TCallback $handler
     */
    public function add(string $messageId, callable $handler): void;

    /**
     * Checks if there are handlers for particular message.
     *
     * @param string $messageId
     *
     * @return bool
     *
     * @psalm-param   class-string $messageId
     * @phpstan-param class-string $messageId
     */
    public function has(string $messageId): bool;

    /**
     * Returns a handlers for particular message.
     *
     * @param string $messageId
     *
     * @return \Traversable<callable>|callable[]
     *
     * @psalm-param    class-string $messageId
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   \Traversable<array-key, TCallback>
     * @phpstan-return \Traversable<array-key, TCallback>
     */
    public function get(string $messageId): \Traversable;
}
