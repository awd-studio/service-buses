<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Reflection;

final class Reflector
{
    public const NO_INVOKABLE_ARGUMENT = '!NO_INVOKABLE_ARGUMENT';

    /**
     * @var \ReflectionFunctionAbstract
     */
    private $callback;

    public function __construct(callable $callback)
    {
        if (\is_object($callback)) {
            $this->callback = new \ReflectionMethod($callback, '__invoke');
        } elseif (\is_array($callback)) {
            $this->callback = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            $this->callback = new \ReflectionFunction(\Closure::fromCallable($callback));
        }
    }

    public static function create(callable $middleware): self
    {
        return new self($middleware);
    }

    public function firstParametersTypeName(): string
    {
        return $this->resolveInvokableArgument();
    }

    private function resolveInvokableArgument(): string
    {
        $argument = null;
        $parameter = $this->getFirsParameter();

        if (null !== $parameter) {
            $argument = $parameter->name();
        }

        return $argument ?? self::NO_INVOKABLE_ARGUMENT;
    }

    private function getFirsParameter(): ?ParameterReflector
    {
        $parameters = $this->callback->getParameters();

        if (isset($parameters[0])) {
            return new ParameterReflector($parameters[0]);
        }

        return null;
    }
}
