<?php

declare(strict_types=1);

namespace AwdStudio\ServiceBuses\Reflection;

final class ParameterReflector
{
    /**
     * @var \ReflectionParameter
     */
    public $parameter;

    public function __construct(\ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    public function name(): ?string
    {
        if ($this->canBeProcessed()) {
            $paramType = $this->parameter->getType();

            return $paramType instanceof \ReflectionNamedType ? $paramType->getName() : null;
        }

        return null;
    }

    private function canBeProcessed(): bool
    {
        return ParameterIsProcessable::create($this->parameter)->isProcessable();
    }
}
