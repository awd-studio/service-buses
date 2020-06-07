<?php

declare(strict_types=1);

namespace AwdStudio\Tests\Module\DifferentQuantityOfParameters;

final class MessageHandler
{
    public function __invoke(Message $message): void
    {
        $message->iWasHere($this);
    }
}
