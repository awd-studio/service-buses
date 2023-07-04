<?php

declare(strict_types=1);

namespace AwdStudio\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\SimpleBus;

/**
 * Implements the QueryBus with handling via middleware.
 */
final class SimpleQueryBus extends SimpleBus implements QueryBus
{
    public function handle(object $query): mixed
    {
        /** @var mixed $result */
        foreach ($this->handleMessage($query) as $result) {
            return $result;
        }

        throw new NoHandlerDefined($query);
    }
}
