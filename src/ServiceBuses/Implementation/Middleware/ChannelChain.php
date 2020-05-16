<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Implementation\Middleware;

use AwdStudio\ServiceBuses\Core\Middleware\MiddlewareChain;
use AwdStudio\ServiceBuses\Reflection\Reflector;

final class ChannelChain implements MiddlewareChain
{
    /**
     * @var array<string, array<array-key, callable>>
     */
    private $channels;

    public function __construct()
    {
        $this->channels = [Reflector::NO_INVOKABLE_ARGUMENT => []];
    }

    public function add(callable $middleware): void
    {
        $channel = Reflector::create($middleware)->firstParametersTypeName();
        $this->addToChannel($channel, $middleware);
    }

    public function chain($message, callable $handler): callable
    {
        return $this->chainForChannel($message, $handler);
    }

    private function addToChannel(string $channel, callable $middleware): void
    {
        $this->channels[$channel][] = $middleware;
    }

    /**
     * Builds chain.
     *
     * @param object $message
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
