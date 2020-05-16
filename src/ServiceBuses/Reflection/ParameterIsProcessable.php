<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Reflection;

final class ParameterIsProcessable
{
    /**
     * @var \ReflectionParameter
     */
    public $parameter;

    /**
     * @var \ReflectionNamedType|null
     */
    public $type;

    public function __construct(\ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
        $paramType = $this->parameter->getType();
        $this->type = $paramType instanceof \ReflectionNamedType ? $paramType : null;
    }

    public static function create(\ReflectionParameter $parameter): self
    {
        return new self($parameter);
    }

    public function isProcessable(): bool
    {
        return
            null !== $this->type
            && !$this->parameter->isVariadic()
            && !$this->parameter->isPassedByReference()
            && !$this->type->isBuiltin()
            && \class_exists($this->type->getName());
    }
}
