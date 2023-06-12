<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\Stub;

use Psr\Container\ContainerInterface;

final class StubServiceLocator implements ContainerInterface
{
    /**
     * @var array
     *
     * @psalm-var   array<string, object<callable>|false>
     *
     * @phpstan-var array<string, object<callable>|false>
     */
    private $handlers;

    public function __construct()
    {
        $this->handlers = [];
    }

    /**
     * @psalm-param   class-string<callable> $handler
     *
     * @phpstan-param class-string<callable> $handler
     */
    public function add(string $handler): void
    {
        $this->handlers[$handler] = false;
    }

    public function has($id)
    {
        return isset($this->handlers[$id]);
    }

    public function get($id)
    {
        if (false === isset($this->handlers[$id])) {
            throw new StubServiceLocatorNotFoundException(\sprintf('No handlers with ID "%s"', $id));
        }

        if (false === $this->handlers[$id]) {
            $this->handlers[$id] = new $id();
        }

        return $this->handlers[$id];
    }
}
