<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

/**
 * Represents the middleware layer that wraps
 * all handlers with additional callbacks.
 */
interface MiddlewareChain
{
    /**
     * Provide a built chain of middleware for particular message.
     *
     * @param object   $message     the message to handle
     * @param callable $handler     a main handler of a message
     * @param mixed[]  $extraParams additional parameters, that will be passed to all handlers
     *
     * @return callable a callback that need to be executed to run all handlers.
     *
     * <code>
     *     // Here we get a prepared chain as a callback
     *     $chain = Middleware::buildChain($someMessage, $someHandler);
     *
     *     // To run it - just call it as a regular function
     *     $chain();
     * </code>
     *
     * @psalm-param    callable(object $message, mixed ...$extraParams): mixed $handler
     *
     * @phpstan-param  callable(object $message, mixed ...$extraParams): mixed $handler
     *
     * @psalm-return   callable(): mixed
     *
     * @phpstan-return callable(): mixed
     */
    public function chain(object $message, callable $handler, array $extraParams = []): callable;
}
