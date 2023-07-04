<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Exception;

final class NoHandlerDefined extends BusException
{
    public function __construct(object $message)
    {
        parent::__construct(\sprintf('No handlers for a message "%s"', $message::class), 1, null);
    }
}
