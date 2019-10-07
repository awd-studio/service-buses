<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Reflection;

final class ParameterIsProcessable
{

    /** @var \ReflectionParameter */
    public $parameter;

    /** @var \ReflectionType|null */
    public $type = null;

    /** @var \ReflectionClass|null */
    public $class = null;

    public function __construct(\ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
        $this->process();
    }

    public static function create(\ReflectionParameter $parameter): self
    {
        return new self($parameter);
    }

    private function process(): void
    {
        if ($this->parameter->hasType()) {
            $this->type = $this->parameter->getType();
            $this->processClass();
        }
    }

    private function processClass(): void
    {
        if (null !== $this->type) {
            try {
                $this->class = new \ReflectionClass($this->resolveTypeClass($this->type));
            } catch (\ReflectionException $e) {
                $this->class = null;
            }
        }
    }

    /**
     * Resolves the name of type's class.
     *
     * @param \ReflectionType $type
     *
     * @return string
     * @psalm-return class-string
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function resolveTypeClass(\ReflectionType $type): string
    {
        return (string) $type;
    }

    public function isProcessable(): bool
    {
        if (null !== $this->type && null !== $this->class) {
            return
                !$this->parameter->isVariadic()
                && !$this->parameter->isPassedByReference()
                && !$this->type->isBuiltin()
//                && !$this->class->isInternal()
                ;
        }

        return false;
    }

}
