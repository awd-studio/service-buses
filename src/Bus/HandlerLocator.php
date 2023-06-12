<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * A storage for handlers.
 *
 * Provides an interface to arrange handlers via assigning them
 * to certain messages.
 *
 * @psalm-template   TCallback of callable
 *
 * @phpstan-template TCallback of callable
 */
interface HandlerLocator
{
    /**
     * Assigns a handler to a particular message.
     *
     * @param string   $messageId a full qualified class-name of a message
     * @param callable $handler   a callback that can handle a message
     *
     * @psalm-param   class-string $messageId
     *
     * @phpstan-param class-string $messageId
     *
     * @psalm-param   TCallback $handler
     *
     * @phpstan-param TCallback $handler
     */
    public function add(string $messageId, callable $handler): void;

    /**
     * Checks if there are handlers for particular message.
     *
     * @param string $messageId a full qualified class-name of a message
     *
     * @psalm-param   class-string $messageId
     *
     * @phpstan-param class-string $messageId
     */
    public function has(string $messageId): bool;

    /**
     * Returns a handlers for particular message.
     *
     * @param string $messageId a full qualified class-name of a message
     *
     * @return \Iterator<callable>|callable[] the handlers iterator
     *
     * @psalm-param    class-string $messageId
     *
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   \Iterator<array-key, TCallback>
     *
     * @phpstan-return \Iterator<array-key, TCallback>
     */
    public function get(string $messageId): \Iterator;
}
