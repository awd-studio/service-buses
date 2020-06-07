<?php

declare(strict_types=1);

namespace AwdStudio\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\MiddlewareBus;

/**
 * Implements the QueryBus with handling via middleware.
 */
final class MiddlewareQueryBus extends MiddlewareBus implements QueryBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $query, ...$extraParams)
    {
        foreach ($this->buildChains($query, ...$extraParams) as $chain) {
            return $chain();
        }

        throw new NoHandlerDefined($query);
    }
}
