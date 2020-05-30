<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * @psalm-template   TCallback of callable
 * @phpstan-template TCallback of callable
 */
interface Handlers
{
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
     * @return iterable<callable>|callable[]
     *
     * @psalm-param    class-string $messageId
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   iterable<array-key, TCallback>
     * @phpstan-return iterable<array-key, TCallback>
     */
    public function get(string $messageId): iterable;
}
