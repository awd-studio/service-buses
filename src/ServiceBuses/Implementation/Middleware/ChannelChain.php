<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use AwdStudio\ServiceBuses\Reflection\Reflector;

final class ChannelChain implements MiddlewareChain
{

    /** @var array */
    private $channels = [];

    public function __construct()
    {
        $this->channels = [Reflector::NO_INVOKABLE_ARGUMENT => []];
    }

    /**
     * {@inheritDoc}
     */
    public function add(callable $middleware): void
    {
        $channel = Reflector::create($middleware)->firstParametersTypeName();
        $this->addToChannel($channel, $middleware);
    }

    private function addToChannel(string $channel, callable $middleware): void
    {
        $this->channels[$channel][] = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function chain($message, callable $handler): callable
    {
        return $this->chainForChannel($message, $handler);
    }

    /**
     * Builds chain.
     *
     * @param object   $message
     * @param callable $handler
     *
     * @return callable
     */
    private function chainForChannel($message, callable $handler): callable
    {
        $middlewareChain = new Chain();

        foreach ($this->resolveChain(\get_class($message)) as $middleware) {
            $middlewareChain->add($middleware);
        }

        return $middlewareChain->chain($message, $handler);
    }

    /**
     * Resolves all items for particular channel.
     *
     * @param string $messageClass
     *
     * @psalm-param class-string $messageClass
     *
     * @return callable[]
     * @psalm-return \Generator<array-key, callable, mixed, void>
     */
    private function resolveChain(string $messageClass): iterable
    {
        if (isset($this->channels[$messageClass])) {
            yield from $this->channels[$messageClass];
        }

        yield from $this->channels[Reflector::NO_INVOKABLE_ARGUMENT];
    }

}
