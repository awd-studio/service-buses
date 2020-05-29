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
     *
     * @return callable
     */
    public function buildChain(object $message, callable $handler): callable;
}
