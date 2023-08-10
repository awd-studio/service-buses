<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\Registry\ImplementationParser;
use AwdStudio\Bus\Registry\ReflectionImplementationParser;

final class ParentsAwareClassHandlerRegistry implements ClassHandlerRegistry
{
    /**
     * @var array<class-string, class-string[]>
     */
    private array $parsedMap = [];

    public function __construct(
        private readonly ClassHandlerRegistry $handlers,
        private readonly ImplementationParser $parser = new ReflectionImplementationParser()
    ) {
    }

    public function register(string $messageId, string $handlerClass, string $handlerMethod = '__invoke'): void
    {
        $this->handlers->register($messageId, $handlerClass, $handlerMethod);
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
        foreach (array_merge([$messageId], $this->parse($messageId)) as $implementation) {
            foreach ($this->handlers->get($implementation) as $handler) {
                yield $handler;
            }
        }
    }

    /**
     * Parses and caches the result.
     *
     * @param class-string $messageId
     *
     * @return class-string[]
     */
    private function parse(string $messageId): array
    {
        if (false === isset($this->parsedMap[$messageId])) {
            $this->parsedMap[$messageId] = $this->parser->parse($messageId);
        }

        return $this->parsedMap[$messageId];
    }
}
