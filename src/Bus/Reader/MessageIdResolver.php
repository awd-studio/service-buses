<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Reader;

/**
 * Resolves the FCQN of a message by callback's signature.
 */
interface MessageIdResolver
{
    /**
     * Determines the message ID by callback.
     *
     * @param \ReflectionFunctionAbstract $callback the callback's reflection
     *
     * @return class-string the ID of a message on which a callback should be subscribed on
     *
     * @throws \AwdStudio\Bus\Exception\ParsingException
     */
    public function read(\ReflectionFunctionAbstract $callback): string;
}
