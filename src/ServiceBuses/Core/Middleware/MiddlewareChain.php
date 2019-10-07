<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Core\Middleware;

interface MiddlewareChain
{

    /**
     * Appends a middleware link.
     *
     * It'll get two parameters as arguments:
     *   - a message (instance of command class);
     *   - the next callable middleware.
     * It must return value a result of execution of the next middleware in the chain.
     * For example:
     * public function __invoke($command, callable $next): void
     * {
     *     // do anything you need.
     *     $result = $next($command);
     *     // do something again.
     *
     *     return $result;
     * }
     *
     * @param callable $middleware
     */
    public function add(callable $middleware): void;

    /**
     * Returns an arranged chain of responsibility to execute.
     *
     * @param object   $message
     * @param callable $handler
     *
     * @return callable
     */
    public function chain($message, callable $handler): callable;

}
