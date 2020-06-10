<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Reader;

use AwdStudio\Bus\Exception\ParsingException;

/**
 * Resolves a message ID via reflection.
 */
final class ReflectionMessageIdReader implements MessageIdResolver
{
    /**
     * {@inheritdoc}
     */
    public function read(\ReflectionFunctionAbstract $callback): string
    {
        $firstParameter = $callback->getParameters()[0] ?? null;
        if (null === $firstParameter) {
            throw new ParsingException('A callback must have a hinted parameter to read its type');
        }

        $messageType = $firstParameter->getType();
        if (!$messageType instanceof \ReflectionNamedType) {
            throw new ParsingException('Type of message must be named');
        }

        $messageId = $messageType->getName();
        if (false === \class_exists($messageId)) {
            throw new ParsingException('A message ID must represent an existing class');
        }

        return $messageId;
    }
}
