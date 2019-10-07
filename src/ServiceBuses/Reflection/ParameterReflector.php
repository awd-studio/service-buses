<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\Reflection;

final class ParameterReflector
{

    /** @var \ReflectionParameter */
    public $parameter;

    /** @var \ReflectionType|null */
    public $type = null;

    public function __construct(\ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
        if ($this->parameter->hasType()) {
            $this->type = $this->parameter->getType();
        }
    }

    public function name(): ?string
    {
        if ($this->canBeProcessed()) {
            return (string) $this->type;
        }

        return null;
    }

    private function canBeProcessed(): bool
    {
        return ParameterIsProcessable::create($this->parameter)->isProcessable();
    }

}
