<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Registry\ImplementationParser;

/**
 * @implements HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
 */
final class ParentsAwareHandlerRegistry implements HandlerRegistry
{
    /**
     * @var \AwdStudio\Bus\Handler\HandlerRegistry
     *
     * @psalm-var   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     */
    private $handlers;

    /** @var \AwdStudio\Bus\Registry\ImplementationParser */
    private $reflector;

    /**
     * @var array
     *
     * @psalm-var   array<class-string, class-string[]>
     * @phpstan-var array<class-string, class-string[]>
     */
    private $parsedMap;

    /**
     * @param \AwdStudio\Bus\Handler\HandlerRegistry       $handlers
     * @param \AwdStudio\Bus\Registry\ImplementationParser $reflector
     *
     * @psalm-param   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $handlers
     * @phpstan-param HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $handlers
     */
    public function __construct(HandlerRegistry $handlers, ImplementationParser $reflector)
    {
        $this->handlers = $handlers;
        $this->reflector = $reflector;
        $this->parsedMap = [];
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $messageId, string $handlerId): void
    {
        $this->handlers->register($messageId, $handlerId);
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $messageId, callable $handler): void
    {
        $this->handlers->add($messageId, $handler);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function get(string $messageId): \Traversable
    {
        foreach (\array_merge([$messageId], $this->parse($messageId)) as $implementation) {
            yield from $this->handlers->get($implementation);
        }
    }

    /**
     * Parses and caches the result.
     *
     * @param string $messageId
     *
     * @return array
     *
     * @psalm-param    class-string $messageId
     * @phpstan-param  class-string $messageId
     *
     * @psalm-return   class-string[]
     * @phpstan-return class-string[]
     */
    private function parse(string $messageId): array
    {
        if (false === isset($this->parsedMap[$messageId])) {
            $this->parsedMap[$messageId] = $this->reflector->parse($messageId);
        }

        return $this->parsedMap[$messageId];
    }
}
