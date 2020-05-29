<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

interface Handlers
{
    /**
     * Checks if there are handlers for particular message.
     *
     * @param object $message
     *
     * @return bool
     */
    public function has(object $message): bool;

    /**
     * Returns a handlers for particular message.
     *
     * @param object $message
     *
     * @return \Traversable<object>|callable[]
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     *
     * @psalm-return   \Traversable<array-key, callable>
     * @phpstan-return \Traversable<array-key, callable>
     */
    public function get(object $message): \Traversable;
}
