<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Exception\InvalidHandler;
use AwdStudio\Bus\Reader\MessageIdResolver;
use AwdStudio\Bus\Reader\ReflectionMessageIdReader;

/**
 * @implements HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
 */
final class AutoRegisterHandlersRegistry implements HandlerRegistry
{
    /**
     * @var \AwdStudio\Bus\Handler\HandlerRegistry
     *
     * @psalm-var   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     * @phpstan-var HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed>
     */
    private $parent;

    /** @var \AwdStudio\Bus\Reader\MessageIdResolver */
    private $reader;

    /**
     * @param \AwdStudio\Bus\Handler\HandlerRegistry       $parent
     * @param \AwdStudio\Bus\Reader\MessageIdResolver|null $reader
     *
     * @psalm-param   HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $parent
     * @phpstan-param HandlerRegistry<callable(object $message, mixed ...$extraParams): mixed> $parent
     */
    public function __construct(HandlerRegistry $parent, ?MessageIdResolver $reader = null)
    {
        $this->parent = $parent;
        $this->reader = $reader ?? new ReflectionMessageIdReader();
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $messageId, callable $handler): void
    {
        $this->parent->add($messageId, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $messageId): bool
    {
        return $this->parent->has($messageId);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $messageId): \Iterator
    {
        return $this->parent->get($messageId);
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $messageId, string $handlerId, string $handlerMethod = '__invoke'): void
    {
        $this->parent->register($messageId, $handlerId);
    }

    /**
     * Registers a callback as a handler automatically, by the signature of the method in a service.
     *
     * @param callable $handler
     *
     * @psalm-param   callable(object $message, mixed ...$extraParams): mixed $handler
     * @phpstan-param callable(object $message, mixed ...$extraParams): mixed $handler
     */
    public function autoAdd(callable $handler): void
    {
        $messageId = $this->reader->read(new \ReflectionFunction(\Closure::fromCallable($handler)));

        $this->parent->add($messageId, $handler);
    }

    /**
     * Registers a service as a handler automatically, by the signature of the method in a service.
     *
     * @param string $handlerId     an ID of a service that represents a handler in a container
     * @param string $handlerMethod the name of a method that handles a message
     *
     * @psalm-param   class-string $handlerId
     * @phpstan-param class-string $handlerId
     */
    public function autoRegister(string $handlerId, string $handlerMethod = '__invoke'): void
    {
        $classReflection = new \ReflectionClass($handlerId);

        try {
            $messageId = $this->reader->read($classReflection->getMethod($handlerMethod));
        } catch (\ReflectionException $e) {
            throw new InvalidHandler(\sprintf(
                'A handler "%s" supposed to have a method "%s" to register automatically.', $handlerId, $handlerMethod
            ));
        }

        $this->parent->register($messageId, $handlerId, $handlerMethod);
    }
}
