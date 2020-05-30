<?php

declare(strict_types=1);

namespace AwdStudio\Bus\Exception;

final class NoHandlerDefined extends \RuntimeException
{
    public function __construct(object $message)
    {
        parent::__construct(\sprintf('No handlers for a message "%s"', \get_class($message)), 1, null);
    }
}
