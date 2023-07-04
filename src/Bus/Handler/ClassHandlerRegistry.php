<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Handler;

use AwdStudio\Bus\HandlerLocator;

interface ClassHandlerRegistry extends HandlerLocator
{
    /**
     * Registers a handler from a PSR-container as a message handler.
     *
     * @param class-string $messageId     a message on which the handler subscribes on
     * @param class-string $handlerClass  an ID of a service that represents a handler in a container
     * @param string       $handlerMethod the name of a method that handles a message
     *
     * @throws \AwdStudio\Bus\Exception\InvalidHandler
     */
    public function register(string $messageId, string $handlerClass, string $handlerMethod = '__invoke'): void;
}
