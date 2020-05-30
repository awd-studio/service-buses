<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

interface Middleware
{
    /**
     * Provide a built chain of middleware for particular message.
     *
     * @param object   $message
     * @param callable $handler
     * @param mixed[]  $extraParams
     *
     * @return callable
     *
     * @psalm-param   callable(object $message, mixed ...$extraParams): mixed $handler
     * @phpstan-param callable(object $message, mixed ...$extraParams): mixed $handler
     *
     * @psalm-return   callable(): mixed
     * @phpstan-return callable(): mixed
     */
    public function buildChain(callable $handler, object $message, array $extraParams = []): callable;
}
