<?php

declare(strict_types=1);

namespace AwdStudio\Query;

use AwdStudio\Bus\Bus;

final class QueryBus extends Bus implements IQueryBus
{
    /**
     * {@inheritdoc}
     */
    public function handle(object $query)
    {
        $firstHandler = $this->doHandling($query);

        return $firstHandler->current();
    }
}
