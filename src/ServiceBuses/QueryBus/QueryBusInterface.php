<?php

declare(strict_types=1); // strict mode

namespace AwdStudio\ServiceBuses\QueryBus;

interface QueryBusInterface
{

    /**
     * Handles a query.
     *
     * @param object $query
     *
     * @return mixed
     *
     * @throws \AwdStudio\ServiceBuses\Exception\HandlerNotDefined
     * @throws \AwdStudio\ServiceBuses\Exception\WrongMessage
     */
    public function handle($query);

}
