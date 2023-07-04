<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Registry;

final readonly class ReflectionImplementationParser implements ImplementationParser
{
    public function parse(string $messageId): array
    {
        $result = [];
        $messageReflection = new \ReflectionClass($messageId);

        $reflectionClasses = $messageReflection->getInterfaces();
        foreach ($reflectionClasses as $interface) {
            $result[] = $interface->getName();
        }

        $parent = $messageReflection->getParentClass();
        while (false !== $parent) {
            $result[] = $parent->getName();
            $parent = $parent->getParentClass();
        }

        return $result;
    }
}
