<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

final class MiddlewareChain implements Middleware
{
    /** @var \AwdStudio\Bus\Handlers */
    private $middleware;

    public function __construct(Handlers $handlers)
    {
        $this->middleware = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function buildChain(object $message, callable $handler): callable
    {
        $next = /** @return mixed */ static function () use ($message, $handler) {
            return $handler($message);
        };

        foreach ($this->middleware->get($message) as $item) {
            $next = /** @return mixed */ static function () use ($item, $message, $next) {
                return $item($message, $next);
            };
        }

        return $next;
    }
}
