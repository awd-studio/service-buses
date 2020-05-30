<?php

declare(strict_types=1);

namespace AwdStudio\Query;

use AwdStudio\Bus\Exception\NoHandlerDefined;
use AwdStudio\Bus\MiddlewareBus;

final class QueryBus extends MiddlewareBus implements IQueryBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $query, ...$extraParams)
    {
        foreach ($this->chain($query, ...$extraParams) as $chain) {
            return $chain();
        }

        throw new NoHandlerDefined($query);
    }
}
