<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\QueryBus;

use AwdStudio\ServiceBuses\Core\Bus\SingleHandlerBus;

final class QueryBus extends SingleHandlerBus implements QueryBusInterface
{

    /**
     * {@inheritDoc}
     */
    public function handle($query)
    {
        return $this->run($query);
    }

}
