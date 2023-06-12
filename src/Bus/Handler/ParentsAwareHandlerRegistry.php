<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Registry\ImplementationParser;
use AwdStudio\Bus\Registry\ReflectionImplementationParser;

/**
 * @implements HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
 */
final class ParentsAwareHandlerRegistry implements HandlerRegistry
{
    /**
     * @var \AwdStudio\Bus\Handler\HandlerRegistry
     *
     * @psalm-var   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     *
     * @phpstan-var HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     */
    private $handlers;

    /** @var \AwdStudio\Bus\Registry\ImplementationParser */
    private $parser;

    /**
     * @var array
     *
     * @psalm-var   array<class-string, class-string[]>
     *
     * @phpstan-var array<class-string, class-string[]>
     */
    private $parsedMap;

    /**
     * @param \AwdStudio\Bus\Handler\HandlerRegistry $handlers
     *
     * @psalm-param   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $handlers
     *
     * @phpstan-param HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerRegistry $handlers, ImplementationParser $parser = null)
    {
        $this->handlers = $handlers;
        $this->parser = $parser ?? new ReflectionImplementationParser();
        $this->parsedMap = [];
    }

    public function register(string $messageId, string $handlerId, string $handlerMethod = '__invoke'): void
    {
        $this->handlers->register($messageId, $handlerId, $handlerMethod);
    }

    public function add(string $messageId, callable $handler): void
    {
        $this->handlers->add($messageId, $handler);
    }

    public function has(string $messageId): bool
    {
        $has = $this->handlers->has($messageId);
        if (false === $has) {
            foreach ($this->parse($messageId) as $implementation) {
                if ($this->handlers->has($implementation)) {
                    return true;
                }
            }
        }

        return $has;
    }

    public function get(string $messageId): \Iterator
    {
        foreach (\array_merge([$messageId], $this->parse($messageId)) as $implementation) {
            foreach ($this->handlers->get($implementation) as $handler) {
                yield $handler;
            }
        }
    }

    /**
     * Parses and caches the result.
     *
     * @psalm-param    class-string $messageId
     *
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   class-string[]
     *
     * @phpstan-return class-string[]
     */
    private function parse(string $messageId): array
    {
        if (false === isset($this->parsedMap[$messageId])) {
            $this->parsedMap[$messageId] = $this->parser->parse($messageId);
        }

        return $this->parsedMap[$messageId];
    }
}
