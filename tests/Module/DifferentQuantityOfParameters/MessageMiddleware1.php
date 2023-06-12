<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\DifferentQuantityOfParameters;

final class MessageMiddleware1
{
    public function __invoke(Message $message, callable $next, int $extra1 = null): void
    {
        $message->iWasHere($this);

        $next();
    }
}
