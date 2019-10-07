<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;

final class Chain implements MiddlewareChain
{

    /** @var callable[] */
    private $middleware = [];

    public function __construct(callable ...$middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function add(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function chain($message, callable $handler): callable
    {
        $next =
            /**
             * @return mixed
             */
            function () use ($handler, $message)
            {
                return $handler($message);
            };

        foreach ($this->middleware as $middleware) {
            $next =
                /**
                 * @param object $message
                 *
                 * @return mixed
                 */
                function ($message) use ($middleware, $next)
                {
                    return $middleware($message, $next);
                };
        }

        return $next;
    }

}
