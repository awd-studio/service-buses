<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * A storage for handlers.
 *
 * Provides an interface to arrange handlers via assigning them
 * to certain messages.
 */
interface HandlerLocator
{
    /**
     * Assigns a handler to a particular message.
     *
     * @param class-string $messageId a full qualified class-name of a message
     * @param callable     $handler   a callback that can handle a message
     */
    public function add(string $messageId, callable $handler): void;

    /**
     * Checks if there are handlers for particular message.
     *
     * @param class-string $messageId a full qualified class-name of a message
     */
    public function has(string $messageId): bool;

    /**
     * Returns a handlers for particular message.
     *
     * @param class-string $messageId a full qualified class-name of a message
     *
     * @return \Iterator<callable> the handlers iterator
     */
    public function get(string $messageId): \Iterator;
}
