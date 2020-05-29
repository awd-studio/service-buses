<?php

declare(strict_types=1);

namespace AwdStudio\Bus;

abstract class Bus
{
    /** @var \AwdStudio\Bus\Handlers */
    protected $handlers;

    /** @var \AwdStudio\Bus\Middleware */
    private $middleware;

    public function __construct(Handlers $handlers, Middleware $middleware)
    {
        $this->handlers = $handlers;
        $this->middleware = $middleware;
    }

    /**
     * Handles the message.
     *
     * @param object $message
     *
     * @return \Generator<mixed>
     *
     * @throws \AwdStudio\Bus\Exception\NoHandlerDefined
     *
     * @psalm-return   \Generator<array-key, mixed>
     * @phpstan-return \Generator<array-key, mixed>
     */
    protected function doHandling(object $message): \Generator
    {
        foreach ($this->handlers->get($message) as $handler) {
            $chain = $this->middleware->buildChain($message, $handler);

            yield $chain();
        }
    }
}
